<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biddings extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'uuid';
    public $incrementing = false;

    protected $fillable = [
        "minimum",
        "increment",
        "claim",
        "start_time",
        "end_time",
        "created_at",
        "status"
    ];
}
