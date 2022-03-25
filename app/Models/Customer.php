<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    // public $timestamps = false;


    protected $hidden = [
        "password",
        "status",
        'updated_at',
        'created_at',
    ];

    protected $fillable = [
        "lname",
        "fname",
        "email",
        "phone_code",
        "contact_no",
        "country",
        "state",
        "city",
        "street",
        "profile_uri",
        "password",
        "status",
    ];
}
