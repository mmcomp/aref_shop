<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ProductSync;
use App\Models\Product;
use Exception;


class SynchronizeProductsWithCrmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = Http::post(env('CRM_ADD_PRODUCT_URL'), [
                "products" => [
                    0 => [
                        "woo_id" => $this->product->woo_id,
                        "name" => $this->product->name,
                        "collections_id" => $this->product->collections_id
                    ],
                ],
            ]);
            if ($response->getStatusCode() == 200) {
                ProductSync::create([
                    "products_id" => $this->product->id,
                    "status" => "successful",
                    "error_message" => null,
                ]);
            }
        } catch (Exception $e) {
            Log::info("CRM ran into a problem in synchronize products! " . json_encode($e->getMessage()));
            ProductSync::create([
                "products_id" => $this->product->id,
                "status" => "failed",
                "error_message" => json_encode($e->getMessage()),
            ]);
        }
    }
}
