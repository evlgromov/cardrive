<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CarRentHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    const CREATED_AT = 'started_at';
    const UPDATED_AT = 'ended_at';

    protected $fillable = [
        'user_id', 'car_id', 'started_at', 'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime: Y-m-d H:i:s',
        'ended_at' => 'datetime: Y-m-d H:i:s',
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
