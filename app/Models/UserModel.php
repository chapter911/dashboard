<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'mst_user';
    protected $primaryKey = 'username';
    protected $returnType = 'array';
    protected $allowedFields = [
        'username',
        'nama',
        'email',
        'profile_photo_path',
        'password',
        'unit_id',
        'group_id',
        'jabatan_id',
        'is_active',
        'web_access',
        'android_access',
        'created_by',
        'created_date',
    ];
    protected $useAutoIncrement = false;

    public function findByUsername(string $username): ?array
    {
        $user = $this->where('username', $username)->first();

        return $user ?: null;
    }
}
