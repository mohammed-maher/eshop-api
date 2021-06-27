<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_store_valid_product()
    {
         $response = $this->postJson('api/products', [
            'name' => 'test product',
            'weights' => [
                ['weight' => 200,
                    'price' => 500,
                    'stock' => 3]
            ],
            'tastes' => [
                'sweet'
            ]
        ]);
        $response->assertStatus(201);
        $this->delete('api/products/'.$response->getData()->data->id);
    }

    public function test_store_invalid_product(){
        $response = $this->postJson('api/products',[]);
        $response->assertStatus(422);

    }

    public function test_get_products(){
        $response = $this->get('api/products');
        $response->assertStatus(200);
    }
}
