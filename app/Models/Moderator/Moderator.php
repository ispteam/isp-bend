<?php

namespace App\Models\Moderator;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moderator extends Model
{
    use HasFactory;
    
    protected $table = "moderators";
    protected $primarykey = "moderatorId";
    protected $fillable = ["name","nameInArabic","email", "password", "phone"];
    protected $hidden = ["password"];

}
