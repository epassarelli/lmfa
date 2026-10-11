<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VenueService
{
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255', 'address' => 'nullable|string|max:255',
            'city' => 'required|string|max:255', 'province_id' => 'required|exists:provincias,id',
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            'description' => 'nullable|string|max:5000', 'phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255', 'status' => 'nullable|in:active,inactive,pending',
        ];
    }

    public static function inputRules(): array
    {
        $rules = ['venue_id' => 'nullable|integer|exists:venues,id', 'new_venue' => 'nullable|array'];
        foreach (self::rules() as $key => $rule) {
            $rules['new_venue.'.$key] = 'nullable|'.str_replace('required|', 'required_with:new_venue.name|', $rule);
        }
        $rules['new_venue.name'] = 'nullable|required_with:new_venue.city,new_venue.address,new_venue.province_id|string|max:255';
        $rules['new_venue.latitude'] = 'nullable|numeric|between:-90,90|required_with:new_venue.longitude';
        $rules['new_venue.longitude'] = 'nullable|numeric|between:-180,180|required_with:new_venue.latitude';

        return $rules;
    }

    public function save(array $data, ?Venue $venue = null): Venue
    {
        $data = Validator::make($data, self::rules())->validate();
        foreach (['name', 'city', 'address'] as $field) {
            $data[$field] = isset($data[$field]) ? trim(preg_replace('/\s+/u', ' ', $data[$field])) : null;
        }
        $duplicate = Venue::query()->when($venue, fn ($q) => $q->where('id', '!=', $venue->id))
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($data['name'])])
            ->whereRaw('LOWER(TRIM(city)) = ?', [mb_strtolower($data['city'])])
            ->where('province_id', $data['province_id'])
            ->whereRaw("LOWER(TRIM(COALESCE(address, ''))) = ?", [mb_strtolower($data['address'] ?? '')])->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'Ya existe un lugar con ese nombre y ubicación. Buscalo en el catálogo antes de crear otro.']);
        }

        return DB::transaction(function () use ($data, $venue) {
            $venue ??= new Venue(['slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(8))]);
            $venue->fill($data)->save();
            // Copias de consulta: corregir el catálogo no cambia el venue_id de ninguna jornada.
            Event::where('venue_id', $venue->id)->update($this->location($venue));
            \Illuminate\Support\Facades\Cache::forget(\App\Services\Events\EventDetailService::UPCOMING_PROVINCES_CACHE_KEY);

            return $venue;
        });
    }

    public function resolve(array &$data, ?int $currentVenueId = null): void
    {
        if (filled($data['new_venue']['name'] ?? null)) {
            $input = $data['new_venue'];
            $input['status'] = auth()->user()?->hasRole('administrador') ? 'active' : 'pending';
            try {
                $data['venue_id'] = $this->save($input)->id;
            } catch (ValidationException $e) {
                throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($messages, $key) => ['new_venue.'.$key => $messages])->all());
            }
        }
        unset($data['new_venue']);
        $id = array_key_exists('venue_id', $data) ? $data['venue_id'] : $currentVenueId;
        if ($id) {
            $venue = Venue::lockForUpdate()->findOrFail($id);
            if (! auth()->user()?->hasRole('administrador') && $venue->status !== 'active' && $venue->id !== $currentVenueId && ! isset($input)) {
                throw ValidationException::withMessages(['venue_id' => 'Seleccioná un lugar activo del catálogo.']);
            }
            $data = array_merge($data, $this->location($venue));
            if ($venue->status === 'pending' && (($data['editorial_status'] ?? null) === 'published' || ($data['estado'] ?? null) == 1)) {
                throw ValidationException::withMessages(['venue_id' => 'Revisá y activá el lugar propuesto antes de publicar.']);
            }
        }
    }

    public function location(Venue $venue): array
    {
        return $venue->only(['city', 'address', 'province_id', 'latitude', 'longitude']);
    }

    public function delete(Venue $venue): bool
    {
        return DB::transaction(function () use ($venue) {
            $venue = Venue::lockForUpdate()->findOrFail($venue->id);
            if ($venue->events()->exists() || EventProgram::where('venue_id', $venue->id)->exists()) {
                return false;
            }

            return (bool) $venue->delete();
        });
    }
}
