<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedProduct extends Model
{
    use HasFactory;

    protected $table ="featuredproducts";

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        "product",
        "description"
    ];
}
