<?php

namespace App\Exports;

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

class ItemTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ItemTemplateSheet(),
            new CategoryReferenceSheet(),
            new ItemTypeReferenceSheet(),
        ];
    }
}

/**
 * ============================
 * Sheet 1 — Template Utama
 * ============================
 */
class ItemTemplateSheet implements
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
            [
                'kategori' => 'Alat Listrik',
                'nama_barang' => 'Laptop ASUS',
                'stok_awal' => 10,
                'harga' => 5000000,
                'satuan' => 'Unit',
            ],
            [
                'kategori' => 'Alat Tulis Kantor',
                'nama_barang' => 'Pulpen',
                'stok_awal' => 100,
                'harga' => 2500,
                'satuan' => 'Pcs',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'kategori',
            'nama_barang',
            'stok_awal',
            'harga',
            'satuan',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 35,
            'C' => 15,
            'D' => 18,
            'E' => 15,
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
                $sheet->setAutoFilter('A1:E1');
            },
        ];
    }

    public function title(): string
    {
        return 'Template Data Barang';
    }
}

/**
 * ============================
 * Sheet 2 — Referensi Kategori
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
        return Category::select('name')->get()->map(fn ($category) => [
            'kategori' => $category->name,
        ]);
    }

    public function headings(): array
    {
        return ['Daftar Kategori Yang Tersedia'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
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
        return 'Referensi Kategori';
    }
}

/**
 * ============================
 * Sheet 3 — Referensi Satuan
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
        return ItemType::select('name')->get()->map(fn ($type) => [
            'satuan' => $type->name,
        ]);
    }

    public function headings(): array
    {
        return ['Daftar Satuan Yang Tersedia'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
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
