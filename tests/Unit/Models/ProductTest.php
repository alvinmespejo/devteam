<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function testProductShouldHaveTagsRelation(): void
    {
        $product = Product::factory()->create();
        $tag = Tag::factory()->create(['product_id' => $product->id]);
        $this->assertInstanceOf(Tag::class, $product->tags->first());
    }
}
