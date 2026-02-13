<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\Category;
use App\Models\ItemType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PurchaseTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PurchaseTemplateSheet(),
            new ItemReferenceSheet(),
            new CategoryReferenceSheet(),
            new ItemTypeReferenceSheet(),
        ];
    }
}

/**
 * ============================
 * Sheet 1 — Template Pembelian
 * ============================
 */
class PurchaseTemplateSheet implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths,
    WithEvents
{
    public function collection()
    {
        return collect([
            // Contoh 1: Purchase barang existing (harga sama)
            [
                'tanggal_pembelian' => now()->format('Y-m-d'),
                'jenis_pembelian' => 'existing',
                'nama_barang' => 'Laptop ASUS',
                'qty' => 5,
                'harga_satuan' => 5000000,
                'supplier' => 'PT. Elektronik Jaya',
                'catatan' => 'Pembelian rutin laptop',
                'kategori' => '',
                'satuan' => '',
            ],
            [
                'tanggal_pembelian' => now()->format('Y-m-d'),
                'jenis_pembelian' => 'existing',
                'nama_barang' => 'Pulpen',
                'qty' => 100,
                'harga_satuan' => 2500,
                'supplier' => 'Toko Alat Tulis Makmur',
                'catatan' => 'Pembelian rutin laptop',
                'kategori' => '',
                'satuan' => '',
            ],
            // Contoh 2: Purchase barang baru (harus register)
            [
                'tanggal_pembelian' => now()->subDay()->format('Y-m-d'),
                'jenis_pembelian' => 'baru',
                'nama_barang' => 'Mouse Wireless Logitech MX Master 3',
                'qty' => 10,
                'harga_satuan' => 1250000,
                'supplier' => 'Distributor Logitech',
                'catatan' => 'Pembelian mouse baru',
                'kategori' => 'Alat Listrik',
                'satuan' => 'buah',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'tanggal_pembelian',
            'jenis_pembelian',
            'nama_barang',
            'qty',
            'harga_satuan',
            'supplier',
            'catatan',
            'kategori',
            'satuan',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // tanggal_pembelian
            'B' => 16,  // jenis_pembelian
            'C' => 40,  // nama_barang
            'D' => 10,  // qty
            'E' => 15,  // harga_satuan
            'F' => 30,  // supplier
            'G' => 35,  // catatan
            'H' => 25,  // kategori
            'I' => 15,  // satuan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Freeze header
                $sheet->freezePane('A2');

                // Auto filter
                $sheet->setAutoFilter('A1:I1');

                // Add instructions in a separate area
                $sheet->setCellValue('K1', '📋 INSTRUKSI PENGGUNAAN:');
                $sheet->setCellValue('K2', '');
                $sheet->setCellValue('K3', '🔹 KOLOM WAJIB untuk SEMUA:');
                $sheet->setCellValue('K4', '   • tanggal_pembelian: Format YYYY-MM-DD (contoh: 2026-02-12)');
                $sheet->setCellValue('K5', '   • jenis_pembelian: isi "existing" atau "baru"');
                $sheet->setCellValue('K6', '   • nama_barang: Nama barang yang akan dibeli');
                $sheet->setCellValue('K7', '   • qty: Jumlah barang (angka positif)');
                $sheet->setCellValue('K8', '   • harga_satuan: Harga per unit (angka tanpa titik/koma)');
                $sheet->setCellValue('K9', '   • supplier: Nama pemasok');
                $sheet->setCellValue('K10', '   • catatan: Untuk grouping transaksi');
                $sheet->setCellValue('K11', '');
                $sheet->setCellValue('K12', '🔹 JENIS PEMBELIAN:');
                $sheet->setCellValue('K13', '');
                $sheet->setCellValue('K14', '1️⃣ jenis_pembelian = "existing"');
                $sheet->setCellValue('K15', '   → Untuk barang yang SUDAH ADA di database');
                $sheet->setCellValue('K16', '   → Harga HARUS SAMA dengan harga existing');
                $sheet->setCellValue('K17', '   → Kolom kategori & satuan TIDAK PERLU diisi');
                $sheet->setCellValue('K18', '   → Lihat sheet "Referensi Barang" untuk cek barang & harga');
                $sheet->setCellValue('K19', '');
                $sheet->setCellValue('K20', '2️⃣ jenis_pembelian = "baru"');
                $sheet->setCellValue('K21', '   → Untuk barang BARU atau barang dengan HARGA BERBEDA');
                $sheet->setCellValue('K22', '   → WAJIB isi kolom kategori & satuan');
                $sheet->setCellValue('K23', '   → Barang akan didaftarkan otomatis jika belum ada');
                $sheet->setCellValue('K24', '   → Lihat sheet "Referensi Kategori" & "Referensi Satuan"');
                $sheet->setCellValue('K25', '');
                $sheet->setCellValue('K26', '⚠️ PENTING:');
                $sheet->setCellValue('K27', '• Baris dengan tanggal & catatan SAMA = 1 transaksi purchase');
                $sheet->setCellValue('K28', '• Format harga: 5000000 (bukan 5.000.000 atau 5,000,000)');
                $sheet->setCellValue('K29', '• Hapus baris contoh ini sebelum import!');

                // Style instructions
                $sheet->getStyle('K1:K29')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D6B656'],
                        ],
                    ],
                    'font' => [
                        'size' => 10,
                    ],
                ]);

                $sheet->getStyle('K1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'C65911'],
                    ],
                ]);

                $sheet->getStyle('K3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '2E5090'],
                    ],
                ]);

                $sheet->getStyle('K12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '2E5090'],
                    ],
                ]);

                $sheet->getStyle('K26')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'C00000'],
                    ],
                ]);

                // Set column width for instructions
                $sheet->getColumnDimension('K')->setWidth(80);

                // Color coding for jenis_pembelian column (B)
                $sheet->getStyle('B2')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9EAD3'], // Light green for existing
                    ],
                ]);

                $sheet->getStyle('B3')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9EAD3'], // Light green for existing
                    ],
                ]);

                $sheet->getStyle('B4')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE5CD'], // Light orange for baru
                    ],
                ]);
            },
        ];
    }

    public function title(): string
    {
        return 'Template Pembelian';
    }
}

/**
 * ============================
 * Sheet 2 — Referensi Barang
 * ============================
 */
class ItemReferenceSheet implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    public function collection()
    {
        return Item::with(['category', 'itemType'])
            ->where('price', '>', 0) // Hanya barang yang sudah punya harga
            ->select('name', 'category_id', 'item_type_id', 'price')
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'nama_barang' => $item->name,
                'kategori' => $item->category?->name ?? '-',
                'satuan' => $item->itemType?->name ?? '-',
                'harga_existing' => $item->price,
                'jenis_pembelian' => 'existing',
            ]);
    }

    public function headings(): array
    {
        return [
            'Nama Barang (Existing)',
            'Kategori',
            'Satuan',
            'Harga (Gunakan Harga Ini)',
            'Gunakan jenis_pembelian',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 30,
            'C' => 15,
            'D' => 20,
            'E' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Referensi Barang';
    }
}

/**
 * ============================
 * Sheet 3 — Referensi Kategori
 * ============================
 */
class CategoryReferenceSheet implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    public function collection()
    {
        return Category::select('name')
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'kategori' => $category->name,
                'keterangan' => 'Untuk barang BARU saja',
            ]);
    }

    public function headings(): array
    {
        return ['Daftar Kategori Yang Tersedia', 'Keterangan'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F4B084'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Referensi Kategori';
    }
}

/**
 * ============================
 * Sheet 4 — Referensi Satuan
 * ============================
 */
class ItemTypeReferenceSheet implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    public function collection()
    {
        return ItemType::select('name')
            ->orderBy('name')
            ->get()
            ->map(fn ($type) => [
                'satuan' => $type->name,
                'keterangan' => 'Untuk barang BARU saja',
            ]);
    }

    public function headings(): array
    {
        return ['Daftar Satuan Yang Tersedia', 'Keterangan'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFC000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Referensi Satuan';
    }
}