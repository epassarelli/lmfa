<?php

namespace App\Support;

use Illuminate\Http\Request;

class BackendListing
{
    public static function resolveSort(
        Request $request,
        array $allowedSorts,
        string $defaultSort,
        string $defaultDirection = 'desc'
    ): array {
        $sort = $request->string('sort')->toString();
        $direction = strtolower($request->string('direction')->toString());

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = $defaultSort;
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        return [$sort, $direction];
    }
}
