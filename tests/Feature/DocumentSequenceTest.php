<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\DocumentSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DocumentSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sequence_advances_inside_committed_transactions(): void
    {
        $service = app(DocumentSequenceService::class);

        $first = DB::transaction(fn () => $service->next('test-sales', 'TEST-', Transaction::class, 'receipt_no'));
        $second = DB::transaction(fn () => $service->next('test-sales', 'TEST-', Transaction::class, 'receipt_no'));

        $this->assertSame('TEST-0001', $first);
        $this->assertSame('TEST-0002', $second);
        $this->assertDatabaseHas('document_sequences', [
            'sequence_key' => 'test-sales',
            'current_value' => 2,
        ]);
    }
}
