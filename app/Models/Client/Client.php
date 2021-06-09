<?php

namespace App\Models\Client;

use App\Models\RRequest\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $table="clients";
    protected $primarykey= "clientId";
    protected $fillable = ["name", "password", "email", "nameInArabic", "phone", "address"];
    protected $hidden= ["password", "laravel_through_key"];

    public function requests(){
        return $this->hasMany(
            Request::class,
            "clientId",
            "clientId"
        );
    }
}
