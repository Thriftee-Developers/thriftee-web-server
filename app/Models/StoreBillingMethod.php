<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreBillingMethod extends Model
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;

    public $timestamps = false;
    protected $table = "storebillingmethods";
}
