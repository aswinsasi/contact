<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    protected $table = 'user_contacts';
    protected $primaryKey = 'id';

    protected $fillable = ['name', 'nick_name','relation','address','mobile','user_id'];

    public function user()
    {
        return $this->belongsTo("App\User");
    }
}
