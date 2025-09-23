<?php

namespace Tests\Feature\Products;

use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function testProductSyncEndpoint(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'status',
                ],
            ]);
    }

    public function testItSyncsProduct(): void
    {
        $responseData = [
            ['id' => 1, 'title' => 'title', 'price' => 123.2, 'description' => 'description', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 3.9, 'count' => 120]],
            ['id' => 2, 'title' => 'title', 'price' => 123.2, 'description' => 'description', 'category' => 'womens clothing', 'image' => 'image', 'rating' => ['rate' => 5, 'count' => 121]],
            ['id' => 3, 'title' => 'title', 'price' => 123.2, 'description' => 'description', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 2, 'count' => 122]],

        ];

        Http::fake([
            'https://fakestoreapi.com/products' => Http::response($responseData, Response::HTTP_OK)
        ]);

        $mock = Mockery::mock(ProductService::class);
        $mock->shouldReceive('syncProduct')
            ->once()
            ->with($responseData)
            ->andReturnUsing(function ($products) {
                $this->assertCount(3, $products);
            });
        $this->app->instance(ProductService::class, $mock);

        $response = $this->get('/products');
        $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'data' => ['status' => 'Done']
                ]);
    }

    public function testItHandlesExternalApiFailure(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([], Response::HTTP_INTERNAL_SERVER_ERROR)
        ]);

        $mock = Mockery::mock(ProductService::class);
        $this->app->instance(ProductService::class, $mock);
        Log::shouldReceive('error')->once();

        $response = $this->get('/products');
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
                ->assertJsonStructure([
                    'error'
                ]);
    }

    public function testItReturnsSucessWhenExternAPIReturnsEmpty(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response([], Response::HTTP_OK)
        ]);

        $mock = Mockery::mock(ProductService::class);
        $this->app->instance(ProductService::class, $mock);

        $response = $this->get('/products');
        $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'data' => ['status' => 'Done']
                ]);
    }
}
