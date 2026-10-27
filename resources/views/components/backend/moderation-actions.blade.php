@props(['edit', 'type' => null, 'id' => null, 'reviewLabel' => 'Revisar'])

<td>
    <a href="{{ $edit }}" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> {{ $reviewLabel }}</a>
    @if($type)
        <form action="{{ route('backend.moderation.publish', ['type' => $type, 'id' => $id]) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Publicar</button>
        </form>
    @endif
</td>
