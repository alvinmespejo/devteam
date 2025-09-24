<?php

namespace App\Services;

use App\Jobs\ProductDetailSync;
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
                $arrProdDTO = [];
                foreach ($products as $value) {
                    $prodRespValue = $productWithIdKeys[$value->external_id] ?? null;
                    if (!$prodRespValue) {
                        continue;
                    }

                    $prodDTO = new \App\Dto\Api\v1\ProductDTO(
                        (int) $value->id,
                        $prodRespValue['id'],
                        $prodRespValue['title'],
                        $prodRespValue['price'],
                        $prodRespValue['description'],
                        $prodRespValue['category'],
                        $prodRespValue['image'],
                        $prodRespValue['rating']['rate'],
                        $prodRespValue['rating']['count'],
                    );

                    $arrProdDTO[] = $prodDTO;

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

                ProductDetailSync::dispatch($arrProdDTO)->onConnection('database');
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
                $prodDTO = new \App\Dto\Api\v1\ProductDTO(
                    null,
                    $product['id'],
                    $product['title'],
                    $product['price'],
                    $product['description'],
                    $product['category'],
                    $product['image'],
                    $product['rating']['rate'],
                    $product['rating']['count'],
                    createdAt: $currentTime,
                    updatedAt: $currentTime,
                );

                $toInsertProducts[] = $prodDTO;
            }

            $arrChunk = array_chunk($toInsertProducts, self::CHUNK_SIZE);
            foreach ($arrChunk as $chunk) {
                ProductDetailSync::dispatch($chunk)->onConnection('database');
            }
        }
    }
}
