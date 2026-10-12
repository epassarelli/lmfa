@extends('adminlte::page')

@section('metaTitle', 'Mensaje de contacto')

@section('content_header')
  <h1>Mensaje de contacto #{{ $message->id }}</h1>
@stop

@php
  $badges = ['new' => 'primary', 'read' => 'secondary', 'answered' => 'success', 'spam' => 'danger'];
  $reply = 'mailto:'.$message->email.'?subject='.rawurlencode('Re: '.$message->asunto);
@endphp

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row">
    <div class="col-lg-8">
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
          <h3 class="card-title">{{ $message->asunto }}</h3>
          <div class="card-tools"><span class="badge badge-{{ $badges[$message->status] ?? 'secondary' }}">{{ $message->statusLabel() }}</span></div>
        </div>
        <div class="card-body">
          <dl class="row mb-3">
            <dt class="col-sm-3">De</dt>
            <dd class="col-sm-9">{{ $message->nombre }} &lt;{{ $message->email }}&gt;</dd>
            <dt class="col-sm-3">Recibido</dt>
            <dd class="col-sm-9">{{ optional($message->fecha_envio)->format('d/m/Y H:i') }}</dd>
            @if ($message->user)
              <dt class="col-sm-3">Usuario</dt>
              <dd class="col-sm-9">Cuenta registrada: {{ $message->user->name }}</dd>
            @endif
            @if ($message->spam_reason)
              <dt class="col-sm-3">Marcado como spam</dt>
              <dd class="col-sm-9"><span class="text-danger">{{ $message->spam_reason === 'too_many_links' ? 'Demasiados enlaces en el mensaje' : $message->spam_reason }}</span></dd>
            @endif
          </dl>
          <div class="border rounded p-3 bg-light" style="white-space: pre-wrap">{{ $message->mensaje }}</div>
        </div>
        <div class="card-footer">
          <a href="{{ $reply }}" class="btn btn-primary"><i class="fas fa-reply mr-1"></i> Responder por email</a>
          <a href="{{ route('backend.contact-messages.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header"><h3 class="card-title">Estado</h3></div>
        <div class="card-body">
          @foreach (\App\Models\ContactMessage::statuses() as $key => $label)
            @if ($key !== $message->status)
              <form action="{{ route('backend.contact-messages.status', $message) }}" method="POST" class="mb-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $key }}">
                <button type="submit" class="btn btn-block btn-outline-{{ $badges[$key] }} btn-sm">Marcar como {{ strtolower($label) }}</button>
              </form>
            @endif
          @endforeach
        </div>
      </div>

      <div class="card card-outline card-danger shadow-sm">
        <div class="card-header"><h3 class="card-title">Eliminar</h3></div>
        <form action="{{ route('backend.contact-messages.destroy', $message) }}" method="POST" onsubmit="return confirm('¿Eliminar este mensaje? No se puede deshacer.')">
          @csrf
          @method('DELETE')
          <div class="card-body">
            <p class="small text-muted mb-0">Borra el mensaje y los datos de la persona (por ejemplo, ante un pedido de eliminación de datos).</p>
          </div>
          <div class="card-footer"><button type="submit" class="btn btn-danger btn-sm">Eliminar mensaje</button></div>
        </form>
      </div>
    </div>
  </div>
@stop
