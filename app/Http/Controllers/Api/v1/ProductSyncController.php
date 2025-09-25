<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Jobs\ProductSync;
use App\Models\Product;
use App\Response\ApiErrorResponse;
use App\Response\ApiSuccessResponse;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductSyncController extends Controller
{
    public function __construct(protected ProductService $service)
    {
    }

    public function index(Request $request)
    {
        return new ApiSuccessResponse(
            new ProductResource(Product::paginate(8))
        );

    }

    /**
     * Sync products from external API.
     *
     * Note: This endpoint is intended to handle small numbers of product data, this will cause memory timout issues if the data is large.
     * It is advisable to set up a cron job and use Laravel's task scheduling to call a command/job at regular intervals.
     *
     * See
     *  - ProductSync - command class is intended to be called by the scheduler for long running tasks.
     *  - Scheduler - boostrap/app.php for setting up the scheduler which will be call by the cron job.
     *
     * @param Request $request
     * @return ApiSuccessResponse|ApiErrorResponse
     */
    public function sync(Request $request): ApiSuccessResponse | ApiErrorResponse
    {
        try {
            // $response = Http::get('https://fakestoreapi.com/products');
            // if (!$response->ok()) {
            //     throw new \Exception('Failed to fetch products from external API');
            // }

            // $productResponse = $response->json();
            // if (!count($productResponse)) {
            //     return new ApiSuccessResponse(['status' => 'Done']);
            // }

            // DB::beginTransaction();
            // $this->service->syncProduct($productResponse);
            // DB::commit();
            ProductSync::dispatch()->onConnection('database');
            return new ApiSuccessResponse(['status' => 'Done']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PRODUCT SYNCING ERROR', [$th]);
            return new ApiErrorResponse($th, 'An error occured while processing request. Please try again!');
        }
    }
}
