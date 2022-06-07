<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'mark',
        'model',
        'year',
    ];

    public function carRent()
    {
        return $this->hasOne(CarRent::class);
    }

    public function carRentHistory()
    {
        return $this->hasMany(CarRentHistory::class);
    }

    public function getUser()
    {
        return $this->carRent()->user();
    }
}
