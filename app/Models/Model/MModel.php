<?php

namespace App\Models\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MModel extends Model
{
    use HasFactory;
    protected $table = "models";
    protected $primarykey = "modelId";
    protected $fillable = ["partNo","partDescription","brandId","supplierId","quantity"];

}
