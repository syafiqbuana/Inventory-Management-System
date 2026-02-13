<?php

namespace App\Imports;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Facades\Auth;
use App\Models\Item;
use App\Models\Period;
use App\Models\Category;
use App\Models\ItemType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseImport implements WithMultipleSheets
{
    protected $mainSheet;

    public function sheets(): array
    {
        $this->mainSheet = new PurchaseImportSheet();
        
        return [
            0 => $this->mainSheet,
        ];
    }

    public function getSummary()
    {
        return $this->mainSheet ? $this->mainSheet->getSummary() : [
            'total_rows' => 0,
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => 0,
            'new_items_created' => 0,
        ];
    }

    public function getFailures()
    {
        return $this->mainSheet ? $this->mainSheet->getFailures() : [];
    }

    public function getErrors()
    {
        return $this->mainSheet ? $this->mainSheet->getErrors() : [];
    }

    public function getProcessedRows()
    {
        return $this->mainSheet ? $this->mainSheet->getProcessedRows() : [];
    }

    public function getSkippedRows()
    {
        return $this->mainSheet ? $this->mainSheet->getSkippedRows() : [];
    }
}

class PurchaseImportSheet implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows
{
    protected $failures = [];
    protected $errors = [];
    protected $processedRows = [];
    protected $skippedRows = [];
    protected $currentRow = 0;
    protected $purchaseGroups = [];
    protected $activePeriod;
    protected $newItemsCreated = 0;

    public function __construct()
    {
        // Get active period
        $this->activePeriod = Period::where('is_closed', false)->first();
        
        if (!$this->activePeriod) {
            throw new \Exception("Tidak ada periode aktif. Silakan aktifkan periode terlebih dahulu.");
        }
    }

    public function model(array $row)
    {
        $this->currentRow++;

        // Trim all values
        $row = array_map(function($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        // Skip empty rows
        if ($this->isRowEmpty($row)) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'reason' => 'Baris kosong',
            ];
            return null;
        }

        // Skip if essential fields are empty
        if (empty($row['nama_barang']) || empty($row['qty']) || empty($row['harga_satuan'])) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'reason' => 'Field essential kosong (nama_barang, qty, atau harga_satuan)',
            ];
            return null;
        }

        // Parse tanggal pembelian
        $purchaseDate = $this->parseDateField($row['tanggal_pembelian'] ?? now()->format('Y-m-d'));
        
        // Determine jenis_pembelian (default to 'existing')
        $jenisPembelian = strtolower(trim($row['jenis_pembelian'] ?? 'existing'));
        
        // Validate jenis_pembelian
        if (!in_array($jenisPembelian, ['existing', 'baru'])) {
            $jenisPembelian = 'existing';
        }

        // Find or create item based on jenis_pembelian
        $item = null;
        
        if ($jenisPembelian === 'baru') {
            // SKENARIO 1: Barang Baru - Harus register dulu
            $item = $this->handleNewItem($row);
            
            if (!$item) {
                return null; // Error sudah dicatat di handleNewItem
            }
        } else {
            // SKENARIO 2: Barang Existing - Cari di database
            $item = Item::where('name', $row['nama_barang'])->first();
            
            if (!$item) {
                throw new \Exception("Barang '{$row['nama_barang']}' tidak ditemukan. Gunakan jenis_pembelian='baru' jika ingin membuat barang baru.");
            }

            // Validasi: Barang existing harus punya harga > 0
            if ($item->price == 0) {
                throw new \Exception("Barang '{$row['nama_barang']}' belum memiliki harga. Gunakan jenis_pembelian='baru' untuk set harga pertama.");
            }

            // Cek apakah harga berbeda dengan harga existing
            $newPrice = (float) $row['harga_satuan'];
            if (abs($item->price - $newPrice) > 0.01) {
                throw new \Exception("Harga barang '{$row['nama_barang']}' berbeda (Rp " . number_format($item->price, 0) . " vs Rp " . number_format($newPrice, 0) . "). Gunakan jenis_pembelian='baru' atau sesuaikan harga dengan harga existing.");
            }
        }

        // Create unique key for grouping purchases
        $groupKey = $purchaseDate . '_' . ($row['catatan'] ?? 'no-note') . '_' . $jenisPembelian;

        // Group purchase items by purchase_date, note, and jenis_pembelian
        if (!isset($this->purchaseGroups[$groupKey])) {
            $this->purchaseGroups[$groupKey] = [
                'purchase_date' => $purchaseDate,
                'note' => $row['catatan'] ?? 'Pembelian Barang',
                'jenis_pembelian' => $jenisPembelian,
                'items' => [],
            ];
        }

        // Add item to group
        $this->purchaseGroups[$groupKey]['items'][] = [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'qty' => (int) $row['qty'],
            'unit_price' => (float) $row['harga_satuan'],
            'supplier' => $row['supplier'] ?? 'Tidak disebutkan',
            'subtotal' => (int) $row['qty'] * (float) $row['harga_satuan'],
            'is_new_item' => ($jenisPembelian === 'baru'),
        ];

        $this->processedRows[] = [
            'row' => $this->currentRow,
            'group' => $groupKey,
            'item' => $item->name,
            'jenis' => $jenisPembelian,
        ];

        // Return null because we'll create Purchase models after all rows are processed
        return null;
    }

    /**
     * Handle pembuatan barang baru
     */
    protected function handleNewItem(array $row)
    {
        // Validasi field yang diperlukan untuk barang baru
        if (empty($row['kategori'])) {
            $this->errors[] = "Baris {$this->currentRow}: Kategori wajib diisi untuk barang baru '{$row['nama_barang']}'";
            return null;
        }

        // Cek apakah barang sudah ada
        $existingItem = Item::where('name', $row['nama_barang'])->first();
        
        if ($existingItem) {
            // Jika barang sudah ada dengan harga > 0, error
            if ($existingItem->price > 0) {
                $this->errors[] = "Baris {$this->currentRow}: Barang '{$row['nama_barang']}' sudah ada. Gunakan jenis_pembelian='existing'";
                return null;
            }
            
            // Jika barang ada tapi harga = 0, update harganya
            return $existingItem;
        }

        // Find category
        $category = Category::where('name', $row['kategori'])->first();
        
        if (!$category) {
            $this->errors[] = "Baris {$this->currentRow}: Kategori '{$row['kategori']}' tidak ditemukan";
            return null;
        }

        // Find item type (optional)
        $itemType = null;
        if (!empty($row['satuan'])) {
            $itemType = ItemType::where('name', $row['satuan'])->first();
            
            if (!$itemType) {
                $this->errors[] = "Baris {$this->currentRow}: Satuan '{$row['satuan']}' tidak ditemukan";
                return null;
            }
        }

        // Create new item
        try {
            $newItem = Item::create([
                'name' => $row['nama_barang'],
                'category_id' => $category->id,
                'item_type_id' => $itemType?->id,
                'initial_period_id' => $this->activePeriod->id,
                'price' => 0, // Akan diupdate saat purchase dibuat
                'initial_stock' => 0,
            ]);

            $this->newItemsCreated++;

            return $newItem;
        } catch (\Exception $e) {
            $this->errors[] = "Baris {$this->currentRow}: Gagal membuat barang baru - " . $e->getMessage();
            return null;
        }
    }

    protected function parseDateField($value)
    {
        if (empty($value)) {
            return now()->format('Y-m-d');
        }

        try {
            // Handle Excel date serial number
            if (is_numeric($value)) {
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
                return $date->format('Y-m-d');
            }
            
            // Handle string date
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (!empty($value)) {
                return false;
            }
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_barang' => 'required|string',
            'qty' => 'required|numeric|min:1',
            'harga_satuan' => 'required|numeric|min:0',
            'supplier' => 'nullable|string',
            'tanggal_pembelian' => 'nullable',
            'catatan' => 'nullable|string',
            'jenis_pembelian' => 'nullable|in:existing,baru',
            'kategori' => 'nullable|string',
            'satuan' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_barang.required' => 'Nama barang wajib diisi',
            'qty.required' => 'Jumlah wajib diisi',
            'qty.numeric' => 'Jumlah harus berupa angka',
            'qty.min' => 'Jumlah minimal 1',
            'harga_satuan.required' => 'Harga satuan wajib diisi',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka',
            'jenis_pembelian.in' => 'Jenis pembelian harus: existing atau baru',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }

    public function onError(\Throwable $error)
    {
        $this->errors[] = $error->getMessage();
    }

    /**
     * Create Purchase records after all rows are processed
     */
    public function __destruct()
    {
        if (empty($this->purchaseGroups)) {
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($this->purchaseGroups as $groupKey => $group) {
                // Calculate total amount
                $totalAmount = array_sum(array_column($group['items'], 'subtotal'));

                // Create purchase
                $purchase = Purchase::create([
                    'purchase_date' => $group['purchase_date'],
                    'note' => $group['note'],
                    'total_amount' => $totalAmount,
                    'period_id' => $this->activePeriod->id,
                    'created_by' => auth()->id(),
                ]);

                // Create purchase items and update item price if needed
                foreach ($group['items'] as $item) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'item_id' => $item['item_id'],
                        'qty' => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'supplier' => $item['supplier'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Update price for new items
                    if ($item['is_new_item']) {
                        $itemModel = Item::find($item['item_id']);
                        if ($itemModel && $itemModel->price == 0) {
                            $itemModel->update([
                                'price' => $item['unit_price'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = "Gagal menyimpan data pembelian: " . $e->getMessage();
        }
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getProcessedRows()
    {
        return $this->processedRows;
    }

    public function getSkippedRows()
    {
        return $this->skippedRows;
    }

    public function getSummary()
    {
        return [
            'total_rows' => $this->currentRow,
            'processed' => count($this->processedRows),
            'skipped' => count($this->skippedRows),
            'failed' => count($this->failures),
            'errors' => count($this->errors),
            'purchase_count' => count($this->purchaseGroups),
            'new_items_created' => $this->newItemsCreated,
        ];
    }
}