<?php

namespace App\Jobs;

use App\Services\ProductService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductSync implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = new ProductService();
        try {
            $response = Http::get('https://fakestoreapi.com/products');
            if (!$response->ok()) {
                throw new \Exception('Failed to fetch products from external API');
            }

            $productResponse = $response->json();
            if (!count($productResponse)) {
                exit();
            }

            DB::beginTransaction();
            $service->syncProduct($productResponse);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PRODUCT SYNCING ERROR', [$th]);
            exit();
        }
    }
}
