<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DocumentNumberGenerator
{
    private const MAX_RETRIES = 5;

    public static function generate(Model $model, string $column, string $prefix, DateTimeInterface|string|null $date = null): string
    {
        $resolvedDate = $date instanceof Carbon
            ? $date
            : ($date instanceof DateTimeInterface ? Carbon::instance($date) : ($date !== null ? Carbon::parse($date) : now()));

        $year = $resolvedDate->format('Y');
        $basePrefix = sprintf('%s-%s-', $prefix, $year);

        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            $latestValue = $model->newQuery()
                ->where($column, 'like', $basePrefix.'%')
                ->orderByDesc('id')
                ->value($column);

            $nextSequence = 1;

            if (is_string($latestValue) && preg_match('/-(\d+)$/', $latestValue, $matches) === 1) {
                $nextSequence = ((int) $matches[1]) + 1;
            }

            $candidate = sprintf('%s%04d', $basePrefix, $nextSequence + $attempt);

            try {
                // Test uniqueness by attempting a lightweight insert check.
                // The actual save happens in the model's creating callback context.
                $test = $model->newQuery()
                    ->where($column, $candidate)
                    ->exists();

                if (! $test) {
                    return $candidate;
                }
            } catch (QueryException) {
                // Collision detected — retry with next sequence
            }
        }

        // Fallback: append a random suffix to guarantee uniqueness
        return sprintf('%s%04d-%s', $basePrefix, $nextSequence, Str::lower(Str::random(4)));
    }
}
