<?php

namespace Tests\Unit\Services\Products;

use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testItSyncsProduct(): void
    {
        $data = [
            ['id' => 1, 'title' => 'title', 'price' => 123.2, 'description' => 'description', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 3.9, 'count' => 120]],
            ['id' => 2, 'title' => 'title', 'price' => 123.2, 'description' => 'description', 'category' => 'womens clothing', 'image' => 'image', 'rating' => ['rate' => 5, 'count' => 121]],
            ['id' => 3, 'title' => 'title', 'price' => 123.2, 'description' => 'description', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 2, 'count' => 122]],
        ];

        $service = new ProductService();
        $service->syncProduct($data);
        $this->assertDatabaseCount('products', 3);
    }

    public function testItUpdatesExistingProduct(): void
    {
        $insertData = [
            ['id' => 1, 'title' => 'title 1', 'price' => 123.2, 'description' => 'description', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 3.9, 'count' => 120]],
            ['id' => 2, 'title' => 'title 2', 'price' => 123.2, 'description' => 'description', 'category' => 'womens clothing', 'image' => 'image', 'rating' => ['rate' => 5, 'count' => 121]],
            ['id' => 3, 'title' => 'title 3', 'price' => 123.2, 'description' => 'description', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 2, 'count' => 122]],
        ];

        // Perform insert data
        $service = new ProductService();
        $service->syncProduct($insertData);
        foreach ($insertData as $value) {
            $this->assertDatabaseHas('products', [
                'external_id' => $value['id'],
                'name' => $value['title'],
                'category' => $value['category'],
                'description' => $value['description'],
                'image' => $value['image'],
                'price' => $value['price'],
                'rating' => $value['rating']['rate'],
                'count' => $value['rating']['count'],
            ]);
        }

        $updateData = [
            ['id' => 1, 'title' => 'titles', 'price' => 2, 'description' => 'description 1', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 3.9, 'count' => 120]],
            ['id' => 2, 'title' => 'titles', 'price' => 3, 'description' => 'description 2', 'category' => 'womens clothing', 'image' => 'image', 'rating' => ['rate' => 5, 'count' => 121]],
            ['id' => 3, 'title' => 'titles', 'price' => 4, 'description' => 'description 3', 'category' => 'mens clothing', 'image' => 'image', 'rating' => ['rate' => 2, 'count' => 122]],
        ];

        // Update existing data
        $service->syncProduct($updateData);
        foreach ($updateData as $value) {
            $this->assertDatabaseHas('products', [
                'external_id' => $value['id'],
                'name' => $value['title'],
                'category' => $value['category'],
                'description' => $value['description'],
                'image' => $value['image'],
                'price' => $value['price'],
                'rating' => $value['rating']['rate'],
                'count' => $value['rating']['count'],
            ]);
        }
    }

    public function testSyncProductThrowsInvalidArgumentExceptionOnemptyInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid product data provided.');

        $service = new ProductService();
        $service->syncProduct([]);
    }
}
