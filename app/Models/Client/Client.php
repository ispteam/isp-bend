<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $table="clients";
    protected $primarykey= "clientId";
    protected $fillable = ["name", "password", "email", "nameInArabic", "phone", "address"];
}
