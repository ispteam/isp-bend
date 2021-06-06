<?php

namespace App\Models\Model;

use App\Models\Brand\Brand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MModel extends Model
{
    use HasFactory;
    protected $table = "models";
    protected $primarykey = "modelId";
    protected $fillable = ["partNo","partDescription","brandId","supplierId","quantity"];

    public function brands(){
        return $this->hasOne(
            Brand::class,
            "brandId",
            "modelId"
        );
    }
}
