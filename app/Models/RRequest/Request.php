<?php

namespace App\Models\RRequest;

use App\Models\Model\MModel;
use App\Models\Supplier\Supplier;
use ClientsModelsBridge;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Request extends Model
{
    use HasFactory;
    protected $table = "requests";
    protected $primarykey = "id";
    protected $fillable = ["description", "address", "requestStatus" ,"field", "quantity" ,"amounts", "finalAmount", "clientId", "modelId", "supplierId"];

    public function models(){
        return $this->hasManyThrough(
            MModel::class,
            Request::class,
            "requestId",
            "modelId",
            "requestId",
            "modelId"
        );
    }

}
