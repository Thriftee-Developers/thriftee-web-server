<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        "bid",
        "description",
        "billing_method",
        "reference",
        "status"
    ];
}
