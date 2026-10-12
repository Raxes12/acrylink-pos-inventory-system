<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ReferenceDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReferenceDataCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_lists_are_cached_and_invalidated(): void
    {
        $this->seed();
        $service = app(ReferenceDataService::class);
        $service->forgetCategories();

        $first = Product::categories();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $second = Product::categories();

        $this->assertSame($first, $second);
        $this->assertCount(0, DB::getQueryLog());

        ProductCategory::create(['name' => 'Cache Invalidation Test']);
        $this->assertContains('Cache Invalidation Test', Product::categories());
    }
}
