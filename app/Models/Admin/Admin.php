<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    protected $table= "admins";
    protected $primarykey = "adminId";
    protected $fillable = ["name", "nameInArabic", "password", "phone", "enterId", "email"];
}
