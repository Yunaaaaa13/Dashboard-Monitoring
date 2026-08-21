<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_pos')) {
            // Versi lama tidak memiliki constraint PO + Item. Satukan data lama
            // terlebih dahulu agar constraint baru tidak gagal dan target PO tidak tertimpa.
            $groups = [];
            foreach (DB::table('master_pos')->orderBy('id')->get() as $row) {
                $po = $this->normalise($row->po);
                $item = $this->normalise($row->item_code);
                if ($po === '' || $item === '') continue;
                $key = $po . "\0" . $item;
                if (!isset($groups[$key])) {
                    $groups[$key] = ['id' => $row->id, 'po' => $po, 'item' => $item, 'qty' => (int) $row->qty, 'delete' => []];
                } else {
                    $groups[$key]['qty'] += (int) $row->qty;
                    $groups[$key]['delete'][] = $row->id;
                }
            }
            foreach ($groups as $group) {
                DB::table('master_pos')->where('id', $group['id'])->update([
                    'po' => $group['po'], 'item_code' => $group['item'], 'qty' => $group['qty'],
                ]);
                if ($group['delete']) DB::table('master_pos')->whereIn('id', $group['delete'])->delete();
            }

            Schema::table('master_pos', function (Blueprint $table) {
                $table->unique(['po', 'item_code'], 'master_pos_po_item_unique');
            });
        }

        if (Schema::hasTable('purchasing_logs')) {
            Schema::table('purchasing_logs', function (Blueprint $table) {
                // Menjaga query SUM penerimaan per PO + Item tetap cepat saat volume besar.
                $table->index(['po_reference', 'item_code'], 'purchasing_logs_po_item_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchasing_logs')) {
            Schema::table('purchasing_logs', function (Blueprint $table) {
                $table->dropIndex('purchasing_logs_po_item_index');
            });
        }
        if (Schema::hasTable('master_pos')) {
            Schema::table('master_pos', function (Blueprint $table) {
                $table->dropUnique('master_pos_po_item_unique');
            });
        }
    }

    private function normalise($value): string
    {
        $value = str_replace("\xC2\xA0", ' ', (string) $value);
        return strtoupper(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }
};
