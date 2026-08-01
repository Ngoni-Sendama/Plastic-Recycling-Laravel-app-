<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DocumentNumberGenerator
{
    public static function generate(Model $model, string $column, string $prefix, ?Carbon $date = null): string
    {
        $year = ($date ?? now())->format('Y');
        $basePrefix = sprintf('%s-%s-', $prefix, $year);

        $latestValue = $model->newQuery()
            ->where($column, 'like', $basePrefix.'%')
            ->orderByDesc('id')
            ->value($column);

        $nextSequence = 1;

        if (is_string($latestValue) && preg_match('/-(\d+)$/', $latestValue, $matches) === 1) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $basePrefix, $nextSequence);
    }
}
