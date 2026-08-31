<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Locality;
use App\Models\Mes;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorialCatalogController extends Controller
{
    public function provinces(): JsonResponse
    {
        $items = Provincia::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (Provincia $province) => [
                'id' => $province->id,
                'name' => $province->nombre,
                'slug' => $province->slug,
            ]);

        return response()->json(['data' => $items]);
    }

    public function localities(Request $request): JsonResponse
    {
        $provinceId = $request->integer('province_id') ?: null;

        $query = Locality::query()
            ->when($provinceId, fn ($builder) => $builder->where('province_id', $provinceId))
            ->orderBy('name');

        return response()->json([
            'data' => $query->get(['id', 'province_id', 'name', 'slug']),
        ]);
    }

    public function months(): JsonResponse
    {
        return response()->json([
            'data' => Mes::query()
                ->orderBy('id')
                ->get(['id', 'nombre'])
                ->map(fn (Mes $month) => [
                    'id' => $month->id,
                    'name' => $month->nombre,
                ]),
        ]);
    }
}
