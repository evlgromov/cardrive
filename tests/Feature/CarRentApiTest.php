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

    public function setUp():void {
        parent::setUp();

        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
    }


    public function testGetAllActiveRents()
    {
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

    public function testStartRent()
    {
        $user = User::find(1);
        $car = Car::find(5);

        $payload = [
            'user_id' => $user->id,
            'car_id'  => $car->id
        ];

        
        $this->json('post', '/api/v1/rents/new', $payload)
            ->assertStatus(201)
            ->assertJsonStructure(
                [
                    'data' => [
                        'message'
                    ]
                ]
            );

        $this->assertDatabaseHas('car_rents', $payload);
    }

    public function testStartRentWithEmptyFields()
    {
        $payload = [
            'user_id' => null,
            'car_id'  => null
        ];

        
        $this->json('post', '/api/v1/rents/new', $payload)
            ->assertStatus(400)
            ->assertExactJson(
                [
                    'errors' => [
                        'user_id' => ['???????? ???????????????????????? ???????????????????????? ?????? ????????????????????'],
                        'car_id' => ['???????? ???????????????????? ???????????????????????? ?????? ????????????????????']
                    ]
                ]
            );
    }

    public function testStartRentWithAlreadyRentedAuto()
    {
        $this->json('post', '/api/v1/rents/new', [
            'user_id' => 1,
            'car_id'  => 3
        ])->assertStatus(201);
        
        $this->json('post', '/api/v1/rents/new', [
            'user_id' => 2,
            'car_id'  => 3
        ])
            ->assertStatus(400)
            ->assertExactJson([
                'errors' => [
                    'car_id' => ['?????????????????????? ?????????????????? ???????????? ????????????????????????']
                ]
            ]);
    }

    public function testStartRentWithExistUser()
    {   
        $this->json('post', '/api/v1/rents/new', [
            'user_id' => 1,
            'car_id'  => 3
        ])->assertStatus(201);
        
        $this->json('post', '/api/v1/rents/new', [
            'user_id' => 1,
            'car_id'  => 4
        ])
            ->assertStatus(400)
            ->assertExactJson([
                'errors' => [
                    'user_id' => ['???????????????????????? ?????????????????? ???????????? ??????????????????????']
                ]
            ]);
    }

    public function testStartRentNotFoundUser()
    {
        $this->json('post', '/api/v1/rents/new', [
            'user_id' => 11,
            'car_id'  => 4
        ])
            ->assertStatus(400)
            ->assertExactJson([
                'errors' => [
                    'user_id' => ['???????????????????????? ???? ????????????']
                ]
            ]);
    }

    public function testGetRentById()
    {
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

    public function testGetRentByIdNotFound()
    {
        $this->json('get', '/api/v1/rents/99')
            ->assertStatus(404)
            ->assertExactJson(
                [
                    'data' =>
                        [
                            'errors' => [
                                'id' => '???????????? ???? ??????????????'
                            ]
                        ]
                ]
            );
    }

    public function testEditRent()
    {
        $user = User::find(1);
        $car = Car::find(5);

        $payload = [
            'user_id' => $user->id,
            'car_id'  => $car->id
        ];

        
        $this->json('post', '/api/v1/rents/new', $payload)
            ->assertStatus(201);

        $this->json('patch', '/api/v1/rents/1', [
            'user_id' => 6,
            'car_id'  => 1
        ])
            ->assertStatus(200)
            ->assertExactJson(
                [
                    'data' =>
                        [
                            'message' => '???????????? ?????????????? ??????????????????'
                        ]
                ]
            );
        
            $this->assertDatabaseHas('car_rents', [
                'user_id' => 6,
                'car_id'  => 1
            ]);

    }

    public function testEditRentButCarIsRented()
    {
        $user = User::find(1);
        $car = Car::find(5);

        $payload = [
            'user_id' => $user->id,
            'car_id'  => $car->id
        ];

        $this->json('post', '/api/v1/rents/new', $payload)
            ->assertStatus(201);

        $user2 = User::find(2);
        $car2 = Car::find(4);

        $payload2 = [
            'user_id' => $user2->id,
            'car_id'  => $car2->id
        ];

        $this->json('post', '/api/v1/rents/new', $payload2)
            ->assertStatus(201);

        $this->json('patch', '/api/v1/rents/1', [
            'user_id' => 1,
            'car_id'  => 4
        ])
            ->assertStatus(400)
            ->assertExactJson(
                [
                    'errors' =>
                        [
                            'car_id' => ['?????????????????????? ?????????????????? ???????????? ????????????????????????']
                        ]
                ]
            );

    }

    public function testStopRent()
    {
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
                            'message' => '???????????? ?????????????? ??????????????'
                        ]
                ]
            );

            $this->assertDeleted('car_rents', [
                'id' => $rent->id
            ]);

            $this->assertDatabaseHas('car_rent_histories', [
                'user_id' => $rent->user_id,
            ]);

    }
}
