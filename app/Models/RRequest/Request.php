<?php

namespace App\Models\RRequest;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;
    protected $table = "requests";
    protected $primarykey = "id";
    protected $fillable = ["description", "address", "requestStatus" ,"field", "quantity" ,"amounts", "finalAmount", "clientId", "modelId", "supplierId"];

}
