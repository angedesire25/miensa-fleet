<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class LandlordUser extends Authenticatable
{
    protected $connection = 'landlord';
    protected $table      = 'landlord_users';

    protected $fillable = ['name', 'email', 'password', 'is_super'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['password' => 'hashed', 'is_super' => 'boolean'];
}
