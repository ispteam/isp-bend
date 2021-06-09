<?php

namespace App\Models\RRequest;

use App\Models\Brand\Brand;
use App\Models\Client\Client;
use App\Models\Model\MModel;
use App\Models\Supplier\Supplier;
use ClientsModelsBridge;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Request extends Model
{
    use HasFactory;
    protected $table = "requests";
    protected $primarykey = "requestId";
    protected $fillable = ["description", "address", "model" ,"requestStatus" ,"field", "quantity" ,"amounts", "finalAmount", "clientId", "brandId" ,"supplierId", "companyCertificate"];
    protected $hidden = ["clientId","supplierId"];

    public function clients(){
        return $this->hasManyThrough(
            Client::class,
            Request::class,
            "requestId",
            "clientId",
            "requestId",
            "clientId"
        );
    }

    public function brands(){
        return $this->hasOneThrough(
            Brand::class,
            Request::class,
            "requestId",
            "brandId",
            "requestId",
            "brandId"
        );
    }

}
