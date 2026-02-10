<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\ItemType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
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

class ItemTemplateSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
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
                'kategori' => 'Alat Tulis Kantor (0024)',
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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Template Data Barang';
    }
}

class CategoryReferenceSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return Category::select('name')->get()->map(function ($category) {
            return ['kategori' => $category->name];
        });
    }

    public function headings(): array
    {
        return ['Daftar Kategori Yang Tersedia'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Referensi Kategori';
    }
}

class ItemTypeReferenceSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return ItemType::select('name')->get()->map(function ($type) {
            return ['satuan' => $type->name];
        });
    }

    public function headings(): array
    {
        return ['Daftar Satuan Yang Tersedia'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFC000'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Referensi Satuan';
    }
}