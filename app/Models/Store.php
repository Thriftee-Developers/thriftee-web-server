<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;

    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
        'status'
    ];

    protected $fillable = [
        'store_name',
        'password',
        'status',
        'phone_code',
        'contact_no',
        'email',
        'country',
        'state',
        'municipality',
        'street',
        'image_uri'
    ];
}
