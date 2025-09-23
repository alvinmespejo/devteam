<?php

namespace App\Services;

use App\Models\Product;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ProductService
{
    private const int CHUNK_SIZE = 5;

    /**
     * Sync product to our database.
     *
     * @param array $productResponse
     * @throws InvalidArgumentException
     * @return void
     */
    public function syncProduct(array $productResponse = []): void
    {
        if (!count($productResponse)) {
            throw new InvalidArgumentException('Invalid product data provided.', Response::HTTP_BAD_REQUEST);
        }

        /**
         * Rekey product array for easy access.
         */
        $productWithIdKeys = collect($productResponse)->keyBy('id');

        /**
         * Get all product ID's for data fetching, this will help minimize
         * http call to our DB.
         */
        // $prodIds = array_map(function ($product) { return $product['id']; }, $productResponse);
        $prodIds = array_column($productResponse, 'id');
        $prodExistingIds = [];

        /**
         * Chunk through existing products and update them if necessary.
         * This will help minimize memory and CPU usage.
         *
         * Note: Increase chunk size if we have more memory and CPU.
         */
        Product::query()
            ->with(['tags'])
            ->whereIn('external_id', $prodIds)
            ->chunk(self::CHUNK_SIZE, function ($products) use ($productWithIdKeys, &$prodExistingIds) {
                foreach ($products as $value) {
                    $prodRespValue = $productWithIdKeys[$value->external_id];
                    if (!$prodRespValue) {
                        continue;
                    }

                    $value->name = $prodRespValue['title'];
                    $value->price = $prodRespValue['price'];
                    $value->category = $prodRespValue['category'];
                    $value->description = $prodRespValue['description'];
                    $value->image = $prodRespValue['image'];
                    $value->rating = $prodRespValue['rating']['rate'];
                    $value->count = $prodRespValue['rating']['count'];
                    $value->save();

                    $prodExistingIds[] = $value->external_id;

                    /**
                     * If you want to sync tags, you can do it here.
                     * This is just a placeholder, adjust according to your needs.
                     */

                    // $tags = $value->tags;
                    // foreach ($tags as $tag) {
                    //     $tag->name;
                    // }
                }
            });

        /**
         * Perform batch insert for product data that
         * are not present in our database
         */
        $toInsertProdIds = array_diff($prodIds, $prodExistingIds);
        if ($toInsertProdIds) {
            $toInsertProducts = [];
            foreach ($toInsertProdIds as $toInsertProdId) {
                $product = $productWithIdKeys[$toInsertProdId];
                if (!$product = $productWithIdKeys[$toInsertProdId]) {
                    continue;
                }

                $currentTime = now();
                $toInsertProducts[] = [
                    'external_id' => $product['id'],
                    'name' => $product['title'],
                    'price' => $product['price'],
                    'category' => $product['category'],
                    'description' => $product['description'],
                    'image' => $product['image'],
                    'rating' => $product['rating']['rate'],
                    'count' => $product['rating']['count'],
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ];
            }

            $arrChunk = array_chunk($toInsertProducts, self::CHUNK_SIZE);
            foreach ($arrChunk as $chunk) {
                Product::insert($chunk);
            }
        }
    }
}
