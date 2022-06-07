<?php

namespace App\Http\Controllers\API\v1;

use App\Models\CarRent;
use Illuminate\Http\Request;
use App\Models\CarRentHistory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\CarRentRequest;
use App\Http\Resources\CarRentResource;
use Carbon\Carbon;

class CarRentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllActiveRents()
    {
        return CarRentResource::collection(CarRent::all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rentsHistoryList()
    {
        return response()->json([
            'data' => CarRentHistory::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function startRent(CarRentRequest $request)
    {
        CarRent::create($request->validated());
        
        return response()->json(
            ['data' => [
                'message' => 'Запись успешно создана'
            ]], 201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getRentById($id)
    {
        $rent = CarRent::find($id);

        if (!$rent) return response()->json(
            ['data' => [
                'errors' => [
                    'id' => 'Запись не найдена'
                ]
            ]], 404
        );

        return new CarRentResource($rent);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getHistoryById($id)
    {
        $rent = CarRentHistory::find($id);

        if (!$rent) return response()->json(
            ['data' => [
                'errors' => [
                    'id' => 'Запись не найдена'
                ]
            ]], 404
        );

        return response()->json([
            'data' => $rent
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editRent(Request $request, $id)
    {
        $rent = CarRent::find($id);

        if (!$rent) return response()->json(
            ['data' => [
                'errors' => [
                    'id' => 'Запись не найдена'
                ]
            ]], 404
        );

        if (is_null($request->user_id) && is_null($request->car_id)) 
            return response()->json(
                ['data' => [
                    'errors' => [
                        'id' => 'Заполните поле, которое вы хотели бы изменить в брони'
                    ]
                ]], 400
            );

        $request->validate([
            'user_id' => [
                'numeric',
                Rule::unique('car_rents')->ignore($rent->user_id, 'user_id')
            ],
            'car_id' => [
                'numeric',
                Rule::unique('car_rents')->ignore($rent->car_id, 'car_id')
            ],
        ],
        [
            'user_id.unique' => 'Пользователь управляет другим автомобилем',
            'user_id.numeric' => 'Поле Пользователь должно быть числом',

            'car_id.unique' => 'Автомобилем управляет другой пользователь',
            'car_id.numeric' => 'Поле Автомобиль должно быть числом',
        ]);


        if (isset($request->user_id)) $rent->user_id = $request->user_id;
        if (isset($request->car_id)) $rent->car_id = $request->car_id;

        $rent->save();

        return response()->json(
            ['data' => [
                'message' => 'Запись успешно обновлена'
            ]], 200
        );
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function stopRent($id)
    {
        $rent = CarRent::find($id);
        if (!$rent) return response()->json(
            ['data' => [
                'errors' => [
                    'id' => 'Запись не найдена'
                ]
            ]], 404
        );

        try {
            DB::transaction(function () use ($rent): void {
                CarRentHistory::create([
                    'started_at' => $rent->created_at,
                    'ended_at' => Carbon::now(),
                    'user_id' => $rent->user_id,
                    'car_id' => $rent->car_id
                ]);
    
                $rent->delete();
            });
        } catch (\Exception $e) {
            return response()->json(
                ['data' => [
                    'errors' => [
                        'message' => $e->getMessage()
                    ]
                ]], 400
            );
        }

        return response()->json(['data' => [
            'message' => 'Запись успешно удалена'
        ]]);
    }
}
