<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAuditModel extends Model
{
    protected $table = 'trn_login';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'username',
        'event_type',
        'is_logged_in',
        'ip_address',
        'ip_network',
        'user_agent',
        'notes',
        'created_date',
    ];
}
