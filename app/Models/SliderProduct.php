<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SliderProduct extends Model
{
    use HasFactory;
    protected $table ="sliderproducts";

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        "product",
        "description"
    ];
}
