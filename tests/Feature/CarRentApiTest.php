<?php

namespace Tests\Feature;

use App\Models\Car;
use Tests\TestCase;
use App\Models\User;
use App\Models\CarRent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CarRentApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        Artisan::call('db:seed');
        $response = $this->get('/api/v1/rents');
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'data' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name'
                        ],
                        'car' => [
                            'id',
                            'mark',
                            'model',
                            'year'
                        ],
                        'rented_at'
                    ]
                ]
            ]
        );
    }

    public function testStore()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        $user = User::find(1);
        $car = Car::find(5);

        $payload = [
            'user_id' => $user->id,
            'car_id'  => $car->id
        ];

        
        $this->json('post', '/api/v1/rents/', $payload)
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        'message'
                    ]
                ]
            );

        $this->assertDatabaseHas('car_rents', $payload);
    }

    public function testStoreWithEmptyFields()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        $payload = [
            'user_id' => null,
            'car_id'  => null
        ];

        
        $this->json('post', '/api/v1/rents/', $payload)
            ->assertStatus(422)
            ->assertExactJson(
                [
                    'errors' => [
                        'user_id' => ['Поле Пользователь обязательное для заполнения'],
                        'car_id' => ['Поле Автомобиль обязательное для заполнения']
                    ]
                ]
            );
    }

    public function testStoreWithAlreadyRentedAuto()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        
        $this->json('post', '/api/v1/rents/', [
            'user_id' => 1,
            'car_id'  => 3
        ])->assertStatus(200);
        
        $this->json('post', '/api/v1/rents/', [
            'user_id' => 2,
            'car_id'  => 3
        ])
            ->assertStatus(422)
            ->assertExactJson([
                'errors' => [
                    'car_id' => ['Автомобилем управляет другой пользователь']
                ]
            ]);
    }

    public function testStoreWithExistUser()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        
        $this->json('post', '/api/v1/rents/', [
            'user_id' => 1,
            'car_id'  => 3
        ])->assertStatus(200);
        
        $this->json('post', '/api/v1/rents/', [
            'user_id' => 1,
            'car_id'  => 4
        ])
            ->assertStatus(422)
            ->assertExactJson([
                'errors' => [
                    'user_id' => ['Пользователь управляет другим автомобилем']
                ]
            ]);
    }

    public function testShow()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        $user = User::find(1);
        $car = Car::find(5);

        $rent = CarRent::create([
            'user_id' => $user->id,
            'car_id' => $car->id
        ]);
        
        $this->json('get', '/api/v1/rents/' . $rent->id)
            ->assertStatus(200)
            ->assertExactJson(
                [
                    'data' => [
                        'id' => $rent->id,
                        'user' => [
                            'id' => $rent->user['id'],
                            'name' => $rent->user['name']
                        ],
                        'car' => [
                            'id' => $rent->car['id'],
                            'mark' => $rent->car['mark'],
                            'model' => $rent->car['model'],
                            'year' => $rent->car['year']
                        ],
                        'rented_at' => $rent->created_at->format('Y-m-d H:i:s')
                    ]
                ]
            );
    }

    public function testShowNotFound()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        
        $this->json('get', '/api/v1/rents/99')
            ->assertStatus(200)
            ->assertExactJson(
                [
                    'data' =>
                        [
                            'errors' => [
                                'id' => 'Запись не найдена'
                            ]
                        ]
                ]
            );
    }

    public function testStoreAndUpdate()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        $user = User::find(1);
        $car = Car::find(5);

        $payload = [
            'user_id' => $user->id,
            'car_id'  => $car->id
        ];

        
        $this->json('post', '/api/v1/rents/', $payload)
            ->assertStatus(200);

        $this->json('put', '/api/v1/rents/1', [
            'user_id' => 6,
            'car_id'  => 1
        ])
            ->assertStatus(200)
            ->assertExactJson(
                [
                    'data' =>
                        [
                            'message' => 'Запись успешно обновлена'
                        ]
                ]
            );
        
            $this->assertDatabaseHas('car_rents', [
                'user_id' => 6,
                'car_id'  => 1
            ]);

    }

    public function testStoreAndUpdateButCarIsRented()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        $user = User::find(1);
        $car = Car::find(5);

        $payload = [
            'user_id' => $user->id,
            'car_id'  => $car->id
        ];

        $this->json('post', '/api/v1/rents/', $payload)
            ->assertStatus(200);

        $user2 = User::find(2);
        $car2 = Car::find(4);

        $payload2 = [
            'user_id' => $user2->id,
            'car_id'  => $car2->id
        ];

        $this->json('post', '/api/v1/rents/', $payload2)
            ->assertStatus(200);

        $this->json('put', '/api/v1/rents/1', [
            'user_id' => 1,
            'car_id'  => 4
        ])
            ->assertStatus(422)
            ->assertExactJson(
                [
                    'errors' =>
                        [
                            'car_id' => ['Автомобилем управляет другой пользователь']
                        ]
                ]
            );

    }

    public function testDelete()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        $user = User::find(1);
        $car = Car::find(5);

        $rent = CarRent::create([
            'user_id' => $user->id,
            'car_id' => $car->id
        ]);

        $this->json('delete', '/api/v1/rents/' . $rent->id)
            ->assertStatus(200)
            ->assertExactJson(
                [
                    'data' =>
                        [
                            'message' => 'Запись успешно удалена'
                        ]
                ]
            );

            $this->assertDatabaseHas('car_rents', [
               
            ]);

    }
}
