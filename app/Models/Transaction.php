<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $primaryKey = 'uuid';
    public $incrementing = false;

    protected $fillable = [
        "bid",
        "description",
        "billing_method",
        "contact_no",
        "email",
        "country",
        "state",
        "city",
        "street",
        "reference",
        "status",
        "validate_at"
    ];
}
