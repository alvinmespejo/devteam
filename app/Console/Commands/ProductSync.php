<?php

namespace App\Console\Commands;

use App\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductSync extends Command
{
    public function __construct(protected ProductService $service)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product from external API source';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('Running product sync');
        try {
            $response = Http::get('https://fakestoreapi.com/products');
            if (!$response->ok()) {
                throw new \Exception('Failed to fetch products from external API');
            }

            $productResponse = $response->json();
            if (!count($productResponse)) {
                $this->info('Done syncing product.');
            }

            DB::beginTransaction();
            $this->service->syncProduct($productResponse);
            $this->info('Done syncing product.');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error($th->getMessage());
        }
    }
}
