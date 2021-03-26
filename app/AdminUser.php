<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB, Config;
use Illuminate\Database\Eloquent\SoftDeletes; 


class AdminUser extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'admin_users';
    protected $primaryKey = 'id';

    protected $fillable = ['name', 'email', 'password','mobile','status'];

   

}