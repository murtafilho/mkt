<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IncrementProductViews implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $productId
    ) {}

    /**
     * Execute the job.
     * Increment product views count.
     */
    public function handle(): void
    {
        Product::where('id', $this->productId)->increment('views');
    }
}
