<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait SafeSchemaModelTrait
{
    /**
     * Cache kolom per nama tabel.
     */
    protected static array $cachedModelColumns = [];

    /**
     * Ambil daftar kolom fisik dari database secara dinamis.
     */
    public static function getPhysicalTableColumns(): array
    {
        $tableName = (new static)->getTable();

        if (!isset(static::$cachedModelColumns[$tableName]) || static::$cachedModelColumns[$tableName] === null) {
            try {
                if (Schema::hasTable($tableName)) {
                    static::$cachedModelColumns[$tableName] = Schema::getColumnListing($tableName);
                } else {
                    static::$cachedModelColumns[$tableName] = [];
                }
            } catch (\Throwable $e) {
                static::$cachedModelColumns[$tableName] = [];
            }
        }

        return static::$cachedModelColumns[$tableName];
    }

    /**
     * Bersihkan cache kolom tabel.
     */
    public static function clearPhysicalColumnsCache(): void
    {
        $tableName = (new static)->getTable();
        unset(static::$cachedModelColumns[$tableName]);
    }

    /**
     * Boot trait untuk secara otomatis menyaring atribut non-kolom sebelum query insert/update.
     */
    public static function bootSafeSchemaModelTrait(): void
    {
        static::saving(function ($model) {
            $cols = static::getPhysicalTableColumns();
            if (!empty($cols)) {
                $colsMap = array_flip($cols);
                foreach ($model->attributes as $key => $val) {
                    if (!isset($colsMap[$key])) {
                        unset($model->attributes[$key]);
                    }
                }
            }
        });
    }

    /**
     * Filter array data mentah agar hanya berisi field yang benar-benar ada di tabel fisik.
     */
    public static function filterAttributesForDatabase(array $attributes): array
    {
        $cols = static::getPhysicalTableColumns();
        if (empty($cols)) {
            return $attributes;
        }

        $colsMap = array_flip($cols);
        return array_filter(
            $attributes,
            fn($key) => isset($colsMap[$key]),
            ARRAY_FILTER_USE_KEY
        );
    }
}
