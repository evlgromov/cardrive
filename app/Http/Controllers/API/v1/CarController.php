<?php

namespace App\Http\Controllers\API\v1;

use App\Models\Car;
use App\Http\Resources\CarResource;
use App\Http\Controllers\Controller;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllCarsList()
    {
        return CarResource::collection(Car::all());
    }
}
