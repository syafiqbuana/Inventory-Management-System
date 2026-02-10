<?php

namespace App\Imports;

use App\Models\Item;
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

class ItemImport implements WithMultipleSheets
{
    protected $mainSheet;

    public function sheets(): array
    {
        $this->mainSheet = new ItemImportSheet();
        
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

class ItemImportSheet implements 
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
        if (empty($row['kategori']) || empty($row['nama_barang'])) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'reason' => 'Field essential kosong',
            ];
            return null;
        }

        // Find category
        $category = Category::where('name', $row['kategori'])->first();
        
        if (!$category) {
            throw new \Exception("Kategori '{$row['kategori']}' tidak ditemukan");
        }

        // Find item type (optional)
        $itemType = null;
        if (!empty($row['satuan'])) {
            $itemType = ItemType::where('name', $row['satuan'])->first();
            
            if (!$itemType) {
                throw new \Exception("Satuan '{$row['satuan']}' tidak ditemukan");
            }
        }

        // Prepare data
        $itemData = [
            'category_id' => $category->id,
            'name' => $row['nama_barang'],
            'initial_stock' => (int) ($row['stok_awal'] ?? 0),
            'price' => (float) ($row['harga'] ?? 0),
            'item_type_id' => $itemType?->id,
        ];

        $this->processedRows[] = [
            'row' => $this->currentRow,
            'data' => $itemData,
        ];

        return new Item($itemData);
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
            'kategori' => 'required|string',
            'nama_barang' => 'required|string|max:255',
            'stok_awal' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kategori.required' => 'Kategori wajib diisi',
            'nama_barang.required' => 'Nama barang wajib diisi',
            'stok_awal.required' => 'Stok awal wajib diisi',
            'stok_awal.numeric' => 'Stok awal harus berupa angka',
            'harga.required' => 'Harga wajib diisi',
            'harga.numeric' => 'Harga harus berupa angka',
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
        ];
    }
}