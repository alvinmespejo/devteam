<?php

namespace App\Jobs;

use App\Dto\Api\v1\ProductDTO;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductDetailSync implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $products)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $toInsertProducts = [];
        try {
            DB::beginTransaction();

            foreach ($this->products as $product) {
                if ($product instanceof ProductDTO) {
                    // Insert case
                    if (is_null($product->id)) {
                        $toInsertProducts[] = [
                            'external_id' => $product->externalId,
                            'name' => $product->title,
                            'price' => $product->price,
                            'description' => $product->description,
                            'category' => $product->category,
                            'image' => $product->image,
                            'rating' => $product->ratingRate,
                            'count' => $product->ratingCount,
                            'created_at' => $product->createdAt,
                            'updated_at' => $product->updatedAt,
                        ];

                        continue;
                    }

                    // Update case
                    Product::query()
                        ->where('id', $product->id)
                        ->update([
                            'external_id' => $product->externalId,
                            'name' => $product->title,
                            'price' => $product->price,
                            'description' => $product->description,
                            'category' => $product->category,
                            'image' => $product->image,
                            'rating' => $product->ratingRate,
                            'count' => $product->ratingCount,
                        ]);
                }
            }

            if (count($toInsertProducts)) {
                Product::query()->insert($toInsertProducts);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PRODUCT DETAIL SYNCING ERROR', [$th]);
        }

    }
}
