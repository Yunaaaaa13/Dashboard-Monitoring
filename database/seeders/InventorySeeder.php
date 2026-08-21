<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Keep inventory table clean by default (0 records) until actual physical stock is inputted.
     */
    public function run(): void
    {
        Inventory::truncate();

        $resetData = ['m0_inventory' => 0];
        for ($i = 1; $i <= 36; $i++) {
            $resetData["m{$i}_inventory"] = 0;
        }

        DB::table('purchasing_outstandings')->update($resetData);
    }
}
