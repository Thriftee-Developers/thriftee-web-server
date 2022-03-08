<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biddings extends Model
{
    use HasFactory;
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        "minimum",
        "increment",
        "claim",
        "start_time",
        "end_time",
        "status"
    ];
}
