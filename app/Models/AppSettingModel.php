<?php

namespace App\Models;

use CodeIgniter\Model;

class AppSettingModel extends Model
{
    protected $table = 'app_settings';
    protected $primaryKey = 'setting_key';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'updated_by',
        'updated_at',
    ];

    public function getValue(string $key, ?string $default = null): ?string
    {
        $row = $this->find($key);

        if (! is_array($row)) {
            return $default;
        }

        $value = $row['setting_value'] ?? null;

        return is_string($value) ? $value : $default;
    }

    public function setValue(string $key, ?string $value, ?string $updatedBy = null): void
    {
        $this->save([
            'setting_key' => $key,
            'setting_value' => $value,
            'updated_by' => $updatedBy,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
