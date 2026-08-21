<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate inventories master table
        Inventory::truncate();

        // Reset m0_inventory through m36_inventory to 0 in purchasing_outstandings
        $resetData = ['m0_inventory' => 0];
        for ($i = 1; $i <= 36; $i++) {
            $resetData["m{$i}_inventory"] = 0;
        }

        DB::table('purchasing_outstandings')->update($resetData);
    }
}
