<?php

namespace App\Services;

use App\Models\EventProgram;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventProgramService
{
    public const COMMON_FIELDS = ['body', 'excerpt', 'venue_id', 'ticket_url', 'price_text', 'is_free'];

    public function fromEvent(\App\Models\Event $event): EventProgram
    {
        return DB::transaction(function () use ($event) {
            $event = \App\Models\Event::lockForUpdate()->findOrFail($event->id);
            if ($event->program_id) {
                return $event->program;
            }
            $program = new EventProgram([
                'created_by' => $event->created_by, 'slug' => Str::slug($event->title).'-'.Str::lower(Str::random(10)),
            ]);
            $this->save(array_merge($event->only(self::COMMON_FIELDS), [
                'title' => $event->title, 'body' => $event->body ?: '<p>Detalle pendiente.</p>',
                'editorial_status' => in_array($event->editorial_status, ['draft', 'published', 'archived'], true) ? $event->editorial_status : 'draft',
                'published_at' => $event->published_at,
            ]), null, $program);
            app(EventService::class)->updateEvent($event, ['program_id' => $program->id, 'editorial_status' => $event->editorial_status]);

            return $program;
        });
    }

    public static function rules(bool $creating = true): array
    {
        return array_merge(VenueService::inputRules(), [
            'title' => 'required|string|max:255', 'body' => 'required|string', 'excerpt' => 'nullable|string|max:500',
            'ticket_url' => 'nullable|url|max:255', 'price_text' => 'nullable|string|max:100', 'is_free' => 'nullable|boolean',
            'editorial_status' => 'nullable|in:draft,published,archived', 'published_at' => 'nullable|date',
            'sessions' => ($creating ? 'required' : 'sometimes').'|array|min:1|max:60',
            'sessions.*.title' => 'required|string|max:255', 'sessions.*.start_at' => 'required|date',
            'sessions.*.end_at' => 'nullable|date|after_or_equal:sessions.*.start_at',
            'sessions.*.interprete_ids' => 'nullable|array', 'sessions.*.interprete_ids.*' => 'integer|exists:interpretes,id',
            'sessions.*.body' => 'nullable|string', 'sessions.*.ticket_url' => 'nullable|url|max:255',
            'sessions.*.price_text' => 'nullable|string|max:100', 'sessions.*.venue_id' => 'nullable|integer|exists:venues,id',
            'sessions.*.is_free' => 'nullable|boolean',
        ]);
    }

    public function save(array $input, mixed $image = null, ?EventProgram $program = null): EventProgram
    {
        $data = Validator::make($input, self::rules(! $program))->validate();

        return DB::transaction(function () use ($data, $image, $program) {
            $sessions = $data['sessions'] ?? [];
            unset($data['sessions']);
            app(VenueService::class)->resolve($data, $program?->venue_id);
            $data['body'] = RichTextHeadingSanitizer::normalize($data['body']);
            $data['is_free'] = $data['is_free'] ?? false;
            if (! auth()->user()?->canPublish()) {
                $data['editorial_status'] = 'draft';
                $data['published_at'] = null;
            }
            $data['editorial_status'] ??= 'draft';
            if ($data['editorial_status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = $program?->published_at ?? now();
            }
            $new = ! $program;
            $program ??= new EventProgram(['created_by' => auth()->id(), 'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(10))]);
            $program->fill($data)->save();
            $events = app(EventService::class);
            if (! $new) {
                $program->events()->orderBy('id')->chunkById(50, function ($sessions) use ($program, $events) {
                    foreach ($sessions as $event) {
                        $inherited = array_diff(self::COMMON_FIELDS, $event->program_overrides ?? []);
                        // Pasar el estado actual evita publicar hermanas al corregir defaults.
                        $events->updateEvent($event, array_merge($program->only($inherited), ['editorial_status' => $event->editorial_status]));
                    }
                });
            }
            foreach ($sessions as $session) {
                // Vacíos opcionales del formulario significan heredar; artistas vacíos nunca se heredan.
                foreach (self::COMMON_FIELDS as $field) {
                    if (array_key_exists($field, $session) && ($session[$field] === null || $session[$field] === '')) {
                        unset($session[$field]);
                    }
                }
                $events->createEvent(array_merge($session, [
                    'program_id' => $program->id, 'created_by' => $program->created_by,
                    'editorial_status' => $program->editorial_status, 'published_at' => $program->published_at,
                ]));
            }
            if ($image) {
                $resolver = app(ImageSourceResolver::class);
                $resolved = null;
                try {
                    $resolved = $resolver->resolve($image);
                    if ($resolved instanceof \Illuminate\Http\UploadedFile) {
                        app(ImageUploadService::class)->process($resolved, $program, 'event', 'events', ! $new, $program->slug, \App\Support\ImageSourceMetadata::from($image));
                    }
                } finally {
                    $resolver->cleanupTemporary($resolved);
                }
            }

            return $program;
        });
    }
}
