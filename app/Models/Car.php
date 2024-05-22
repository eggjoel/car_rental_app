<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

        /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'car_model_id',
        'reg_no',
        'price'
    ];

    public function model() 
    {
        return $this->belongsTo(CarModel::class,'car_model_id');
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
