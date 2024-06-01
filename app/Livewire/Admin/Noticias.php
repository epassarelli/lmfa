<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

use App\Models\Interprete;
use App\Models\Noticia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\MiTrait;



class Noticias extends Component
{
    public $titulo;
    public $slug;
    public $noticia;
    public $foto;
    public $visitas = 0;
    public $publicar;
    public $estado;
    public $noticia_id;
    public $interpretes = [];
    public $todos_interpretes;


    public $modal = false;
    public $search;
    public $sort = 'id';
    public $order = 'desc';
    public $accion;
    public $cambioImg = false;

    protected $noticias;

    protected $listeners = ['delete'];

    use WithPagination;
    use WithFileUploads;
    use MiTrait;

    // protected $rules = [
    //     'titulo' => 'required|string|max:255',
    //     'noticia' => 'required|string',
    //     'foto' => 'required|image|max:2048',
    //     'interpretes' => 'nullable|array',
    //     'interpretes.*' => 'exists:interpretes,id',
    // ];


    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'noticia' => 'required|string',
            'foto' => 'required|image|max:2048',
            // 'publicar' => 'nullable|date_format:Y-m-d H:i:s',
            'interpretes' => 'nullable|array',
            'interpretes.*' => 'exists:interpretes,id',
        ];
    }

    // public function mount()
    // {
    //     dd("El componente de Livewire 'Noticias' se está cargando correctamente");
    // }
    public function mount()
    {
        $this->todos_interpretes = Interprete::all();
    }


    public function render()
    {
        // Si no soy ADMIN verifico y obtengo un array con todos los interpretes sobre los cuales son Prensa
        // $this->noticias = Noticia::all();
        // Obtengo las Gacetillas de los interpretes que soy prensa

        // Sino

        // Obtengo las gacetillas de todos
        $this->noticias = Noticia::where('estado', 1)->get();

        // $noticias = Noticia::with('interprete')
        // $this->noticias = Noticia::where('titulo', 'like', '%' . $this->search . '%')
        //     ->orWhere('noticia', 'like', '%' . $this->search . '%')
        //     ->orderBy($this->sort, $this->order)
        //     ->paginate(5);
        // dd($noticias);
        return view('livewire.backend.noticias', ['noticias' => $this->noticias])->layout('layouts.adminlte');
    }


    private function resetInputFields()
    {
        $this->titulo = '';
        $this->slug = '';
        $this->noticia = '';
        $this->foto = '';
        $this->interpretes = [];
        $this->publicar = '';
        $this->estado = 0;
        $this->noticia_id = '';
    }


    public function create()
    {
        $this->resetInputFields();
        $this->modal = true;
    }


    public function edit($id)
    {
        // dd($id);
        $noticia = Noticia::findOrFail($id);
        $this->noticia_id = $id;
        $this->titulo = $noticia->titulo;
        $this->slug = $noticia->slug;
        $this->noticia = $noticia->noticia;
        $this->foto = $noticia->foto;
        // $this->interpretes = $noticia->interpretes()->pluck('id')->toArray();
        $this->interpretes = $noticia->interprete()->pluck('interpretes.id')->toArray();
        $this->publicar = $noticia->publicar;
        $this->estado = $noticia->estado;

        $this->accion = 'edit';
        $this->modal = true;
    }


    public function save()
    {
        $this->validate();

        $slug = Str::slug($this->titulo);
        $foto = $this->foto->store('public/noticias');
        $noticia = Noticia::create([
            'titulo' => $this->titulo,
            'slug' => $slug,
            'noticia' => $this->noticia,
            'foto' => $foto,
            'visitas' => $this->visitas,
            // 'publicar' => $this->publicar,
            'user_id' => auth()->user()->id,
            'estado' => 0,
        ]);
        $noticia->interprete()->sync($this->interpretes);

        session()->flash('message', 'Noticia guardada correctamente.');
        $this->reset();
    }


    public function delete($id)
    {
        $noticia = Noticia::findOrFail($id);
        Storage::delete($noticia->foto);
        $noticia->delete();

        session()->flash('message', 'Noticia eliminada con éxito.');
    }



    public function updatedNoticia($value)
    {
        $this->noticia = $value;
    }




    // public function old_store()
    // {
    //     $validatedData = $this->validate([
    //         'titulo' => 'required',
    //         'noticia' => 'required',
    //         'foto' => 'required|image|max:2048',
    //         'interpretes' => 'required|array|min:1',
    //         'publicar' => 'nullable|date',
    //         'estado' => 'nullable|boolean',
    //     ]);

    //     $slug = str_slug($validatedData['titulo'], '-');

    //     if (Noticia::where('slug', $slug)->count() > 0) {
    //         $slug = $slug . '-' . time();
    //     }

    //     $validatedData['slug'] = $slug;
    //     $validatedData['user_id'] = Auth::id();
    //     $validatedData['foto'] = $this->foto->store('public/noticias');
    //     $validatedData['visitas'] = 0;

    //     $noticia = Noticia::create($validatedData);
    //     $noticia->interpretes()->sync($this->interpretes);

    //     session()->flash('message', 'Noticia creada con éxito.');
    //     $this->resetInputFields();
    // }



    // public function store()
    // {
    //     $validatedData = $this->validate([
    //         'titulo' => 'required|string|max:255',
    //         'slug' => 'required|string|unique:noticias,slug',
    //         'noticia' => 'required|string',
    //         'foto' => 'required|image|max:2048',
    //         'visitas' => 'nullable|numeric|min:0',
    //         'publicar' => 'nullable|date',
    //         // 'publicar' => 'nullable|date_format:Y-m-d H:i:s',
    //         'estado' => 'nullable|in:0,1',
    //         'interpretes' => 'nullable|array',
    //         'interpretes.*' => 'exists:interpretes,id',
    //     ]);

    //     $slug = Str::slug($validatedData['titulo']);

    //     if (Noticia::where('slug', $slug)->count() > 0) {
    //         $slug = $slug . '-' . time();
    //     }

    //     $validatedData['slug'] = $slug;

    //     if ($this->foto) {
    //         $validatedData['foto'] = $this->foto->store('public/noticias');
    //     }

    //     $validatedData['user_id'] = Auth::id();
    //     $validatedData['visitas'] = 0;

    //     $noticia = Noticia::create($validatedData);
    //     $noticia->interpretes()->sync($this->interpretes);

    //     session()->flash('message', 'Noticia creada con éxito.');
    //     $this->resetInputFields();
    //     $this->modal = false;
    // }



    // public function update()
    // {
    //     $validatedData = $this->validate([
    //         'titulo' => 'required',
    //         'noticia' => 'required',
    //         'foto' => 'nullable|image|max:2048',
    //         'interpretes' => 'required|array|min:1',
    //         'publicar' => 'nullable|date',
    //         // 'publicar' => 'nullable|date_format:Y-m-d H:i:s',
    //         'estado' => 'nullable|boolean',
    //     ]);

    //     $noticia = Noticia::findOrFail($this->noticia_id);

    //     $slug = Str::slug($validatedData['titulo'], '-');


    //     if ($slug != $noticia->slug && Noticia::where('slug', $slug)->count() > 0) {
    //         $slug = $slug . '-' . time();
    //     }
    //     $validatedData['slug'] = $slug;

    //     if ($this->foto) {
    //         Storage::delete($noticia->foto);
    //         $validatedData['foto'] = $this->foto->store('public/noticias');
    //     }

    //     $noticia->update($validatedData);
    //     $noticia->interpretes()->sync($this->interpretes);

    //     session()->flash('message', 'Noticia actualizada con éxito.');
    //     $this->resetInputFields();
    // }

}
