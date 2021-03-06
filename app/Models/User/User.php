<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;
    protected $table = "users_info";
    protected $primarykey = "uid";
    protected $fillable = ["name", "password", "email", "phone", "userType", "token", "rememberToken"];  
    protected $hidden = ["password", "token", "rememberToken"];
    public $timestamps = false;
}
