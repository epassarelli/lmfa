<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Mito;
use App\Http\Requests\MitoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\Provincia;
use App\Models\Mes;
use App\Services\ImageUploadService;

class MitoController extends Controller
{
  protected $imageService;

  public function __construct(ImageUploadService $imageService)
  {
    $this->middleware('auth');
    $this->imageService = $imageService;
  }

  public function index()
  {
    $user = Auth::user();
    $mitos = Mito::query()
      ->when($user->hasRole('colaborador'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->when($user->hasRole('prensa'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->with(['user', 'images'])
      ->get();

    return view('backend.mitos.index', compact('mitos'));
  }

  public function create()
  {
    $provincias = Provincia::all();
    $meses = Mes::all();
    return view('backend.mitos.create', compact('provincias', 'meses'));
  }

  public function store(MitoRequest $request)
  {
    $mito = new Mito($request->validated());
    $mito->slug = Str::slug($mito->titulo);
    $mito->user_id = Auth::id();
    $mito->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
    $mito->save();

    if ($request->hasFile('foto')) {
      $this->imageService->process(
        $request->file('foto'),
        $mito,
        'news_full',
        'mitos'
      );
    }

    if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
      $this->sendNotification($mito);
    }

    Alert::success('Mito creado', 'El mito ha sido creado con éxito.');
    return redirect()->route('backend.mitos.index');
  }

  public function edit(Mito $mito)
  {
    $provincias = Provincia::all();
    $meses = Mes::all();
    return view('backend.mitos.edit', compact('mito', 'provincias', 'meses'));
  }

  public function update(MitoRequest $request, Mito $mito)
  {
    $mito->fill($request->validated());
    $mito->slug = Str::slug($mito->titulo);
    $mito->user_id = Auth::id();
    $mito->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
    $mito->save();

    if ($request->hasFile('foto')) {
      $this->imageService->process(
        $request->file('foto'),
        $mito,
        'news_full',
        'mitos',
        true
      );
    }

    Alert::success('Mito actualizado', 'El mito ha sido actualizado con éxito.');
    return redirect()->route('backend.mitos.index');
  }

  public function destroy(Mito $mito)
  {
    $this->authorize('delete', $mito);
    $mito->delete();

    Alert::success('Mito eliminado', 'El mito ha sido eliminado con éxito.');
    return redirect()->route('backend.mitos.index');
  }

  private function sendNotification(Mito $mito)
  {
    $details = [
      'title' => 'Se ha agregado un/a Mito en el portal',
      'mito' => $mito->titulo,
      'user' => $mito->user->name,
    ];

    Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\MitoCreated($details));
  }
}
