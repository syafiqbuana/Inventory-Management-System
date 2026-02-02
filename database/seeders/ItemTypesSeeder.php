<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'buah',
            'dus',
            'pack',
            'lembar',
            'box',
            'set',
            'rim',
            'roll',
            'liter',
            'botol',
            'keping',
            'pasang'
        ];

        foreach ($types as $type) {
            \App\Models\ItemType::create([
                'name' => $type
            ]);
        }
    }

}
