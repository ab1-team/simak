<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoUserMapping extends Model
{
    protected $table = 'sso_user_mappings';

    protected $fillable = [
        'holding_email',
        'holding_lid',
        'local_user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'local_user_id');
    }
}