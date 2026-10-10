<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationLookupController extends Controller
{
    private const LIMIT = 20;

    public function entities(Request $request): JsonResponse
    {
        $config = config('entity_management.entities.'.$request->string('type')->toString());
        $term = $request->string('q')->trim()->toString();

        if (! $config || mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $column = $config['name_column'];

        $results = $config['model']::query()
            ->where($column, 'like', '%'.$term.'%')
            ->orderBy($column)
            ->limit(self::LIMIT)
            ->get(['id', $column])
            ->map(fn ($entity) => ['id' => $entity->id, 'text' => $entity->{$column}.' (#'.$entity->id.')'])
            ->values();

        return response()->json(['results' => $results]);
    }

    public function users(Request $request): JsonResponse
    {
        $term = $request->string('q')->trim()->toString();

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = User::query()
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%');
            })
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'email'])
            ->map(fn ($user) => ['id' => $user->id, 'text' => $user->name.' <'.$user->email.'>'])
            ->values();

        return response()->json(['results' => $results]);
    }
}
