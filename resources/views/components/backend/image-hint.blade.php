@props(['profile'])
@php($spec = config("backoffice_help.images.$profile"))
@if ($spec)
  <small class="form-text text-muted" data-image-hint="{{ $profile }}">
    Formato <strong>{{ $spec['shape'] }}</strong>, ideal {{ $spec['size'] }} (mínimo {{ $spec['min'] }}).
    JPG, PNG o WebP, hasta {{ $spec['max_mb'] }} MB. {{ $spec['tip'] }}
  </small>
@endif
