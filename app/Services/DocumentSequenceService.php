<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;

class DocumentSequenceService
{
    /**
     * Preview the next number without reserving it. The final number must still
     * be obtained through next() inside the business transaction.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function preview(
        string $key,
        string $prefix,
        string $modelClass,
        string $column,
        int $padding = 4
    ): string {
        $current = (int) DB::table('document_sequences')
            ->where('sequence_key', $key)
            ->value('current_value');
        $next = max($current, $this->existingMaximum($modelClass, $column, $prefix)) + 1;

        return $prefix.str_pad((string) $next, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * Reserve the next number while the caller's business transaction is open.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function next(
        string $key,
        string $prefix,
        string $modelClass,
        string $column,
        int $padding = 4
    ): string {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Document numbers must be reserved inside a database transaction.');
        }

        DB::table('document_sequences')->insertOrIgnore([
            'sequence_key' => $key,
            'current_value' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('document_sequences')
            ->where('sequence_key', $key)
            ->lockForUpdate()
            ->first();

        $current = max((int) $sequence->current_value, $this->existingMaximum($modelClass, $column, $prefix));
        $next = $current + 1;

        DB::table('document_sequences')
            ->where('sequence_key', $key)
            ->update([
                'current_value' => $next,
                'updated_at' => now(),
            ]);

        return $prefix.str_pad((string) $next, $padding, '0', STR_PAD_LEFT);
    }

    /** @param class-string<Model> $modelClass */
    private function existingMaximum(string $modelClass, string $column, string $prefix): int
    {
        return $modelClass::query()
            ->where($column, 'like', $prefix.'%')
            ->pluck($column)
            ->reduce(function (int $maximum, mixed $reference): int {
                if (! is_string($reference) || ! preg_match('/(\d+)$/', $reference, $matches)) {
                    return $maximum;
                }

                return max($maximum, (int) $matches[1]);
            }, 0);
    }
}
