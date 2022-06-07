<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarRent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'car_id'
    ];

    protected $casts = [
        'created_at' => 'datetime: Y-m-d H:i:s'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
