<?php

namespace App\Models\Shipper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipper extends Model
{
    use HasFactory;
    protected $table = "shippers";
    protected $primarykey = "companyId";
    protected $fillable = ["companyName","companyNameArabic"];

}
