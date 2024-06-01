<?php

namespace App\Livewire\Admin;


use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use Illuminate\Support\Str;

use App\Models\Interprete;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\MiTrait;

class Interpretes extends Component
{
    public $interprete;
    public $slug;
    public $foto;
    public $biografia;
    public $visitas = 0;
    public $publicar;
    public $estado = 0;
    public $user_id;
    public $interprete_id;
    public $user;
    public $foto_actual;

    public $modal = false;
    public $search;
    public $sort = 'id';
    public $order = 'desc';
    public $accion;
    // public $cambioImg = false;

    use WithPagination;
    use WithFileUploads;
    use MiTrait;

    protected $rules = [
        'interprete' => 'required|string',
        'slug' => 'required|string',
        'foto' => 'required|image|max:2048',
        'biografia' => 'required',
        'visitas' => 'integer',
        'publicar' => 'nullable|date',
        'estado' => 'integer',
        'user_id' => 'exists:users,id'
    ];


    public function mount($interprete_id = null)
    {
        if ($interprete_id) {
            $interprete = Interprete::find($interprete_id);
            $this->interprete = $interprete->interprete;
            $this->slug = $interprete->slug;
            $this->foto = $interprete->foto;
            $this->biografia = $interprete->biografia;
            $this->visitas = $interprete->visitas;
            $this->publicar = $interprete->publicar;
            $this->estado = $interprete->estado;
            $this->user_id = $interprete->user_id;
            $this->interprete_id = $interprete->id;
        }
    }

    public function render()
    {
        $interpretes = Interprete::where('interprete', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->order)
            ->paginate(5);
        //dd($interpretes);
        return view('livewire.backend.interpretes', compact('interpretes'))->layout('layouts.adminlte');
    }

    public function resetInputFields()
    {
        $this->interprete = '';
        $this->slug = '';
        $this->foto = '';
        $this->biografia = '';
        $this->visitas = 0;
        $this->publicar = null;
        $this->estado = 0;
        $this->user_id = null;
        $this->interprete_id = null;
        $this->foto_actual = null;
    }

    public function create()
    {
        $this->resetInputFields();
        $this->modal = true;
    }

    public function store()
    {
        $this->validate();

        $interprete = new Interprete;
        $interprete->interprete = $this->interprete;
        $interprete->slug = $this->slug;
        $interprete->foto = $this->foto->store('interpretes');
        $interprete->biografia = $this->biografia;
        $interprete->visitas = $this->visitas;
        $interprete->publicar = $this->publicar;
        $interprete->estado = $this->estado;
        $interprete->user_id = $this->user_id;
        $interprete->save();

        session()->flash('message', 'Interprete creado exitosamente.');

        $this->resetInputFields();
    }

    public function edit($id)
    {
        $interprete = Interprete::findOrFail($id);
        $this->interprete_id = $id;
        $this->interprete = $interprete->interprete;
        $this->slug = $interprete->slug;
        $this->biografia = $interprete->biografia;
        $this->visitas = $interprete->visitas;
        $this->publicar = $interprete->publicar;
        $this->estado = $interprete->estado;
        $this->user_id = $interprete->user_id;
        $this->foto_actual = $interprete->foto;

        // $this->dispatchBrowserEvent('show-form');
        $this->modal = true;
    }

    public function update()
    {
        $this->validate([
            'interprete' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:interpretes,slug,' . $this->interprete_id,
            'foto' => 'nullable|image|max:2048',
            'biografia' => 'required|string',
            'visitas' => 'sometimes|integer',
            'publicar' => 'nullable|date',
            'estado' => 'sometimes|integer',
            'user_id' => 'required|exists:users,id',
        ]);

        $interprete = Interprete::findOrFail($this->interprete_id);
        $interprete->interprete = $this->interprete;
        $interprete->slug = $this->slug;

        if ($this->foto) {
            if ($this->foto_actual) {
                \Storage::disk('public')->delete($this->foto_actual);
            }

            $interprete->foto = $this->foto->store('interpretes', 'public');
        }

        $interprete->biografia = $this->biografia;
        $interprete->visitas = $this->visitas;
        $interprete->publicar = $this->publicar;
        $interprete->estado = $this->estado;
        $interprete->user_id = $this->user_id;
        $interprete->save();

        session()->flash('message', 'Interprete actualizado exitosamente.');

        $this->resetInputFields();
    }

    public function destroy($id)
    {
        $interprete = Interprete::findOrFail($id);

        if ($interprete->foto) {
            \Storage::disk('public')->delete($interprete->foto);
        }

        $interprete->delete();

        session()->flash('message', 'Interprete eliminado exitosamente.');
    }



    public function save()
    {

        $this->validate();
        // $slug = Str::slug($this->interprete);
        // $foto = $this->foto->store('interpretes');

        $imagen_name = $this->foto->getClientOriginalName();
        $upload_imagen = $this->foto->storeAs('interpretes', $imagen_name);
        // list($width, $height) = getimagesize(storage_path('app/' . $foto));
        // dd($this->imagen_name);
        // if ($width > 400 || $height > 400) {
        //     \Intervention\Image\Facades\Image::make(storage_path('app/' . $foto))->resize(400, 400, function ($constraint) {
        //         $constraint->aspectRatio();
        //     })->save();
        // }
        // dd($interprete);
        if ($this->interprete_id) {
            $interprete = Interprete::find($this->interprete_id);
            $interprete->update([
                'interprete' => $this->interprete,
                'slug' => $this->slug,
                'foto' => str_replace('public/', '', $foto),
                'biografia' => $this->biografia,
                'visitas' => $this->visitas,
                'publicar' => $this->publicar,
                'estado' => $this->estado,
                'user_id' => auth()->user()->id,
            ]);
        } else {
            $interprete = Interprete::create([
                'interprete' => $this->interprete,
                'slug' => $this->slug,
                'foto' => str_replace('public/', '', $foto),
                'biografia' => $this->biografia,
                'visitas' => $this->visitas,
                'publicar' => $this->publicar, //$fecha_actual = date('Y-m-d H:i:s');
                'estado' => $this->estado,
                'user_id' => auth()->user()->id,
            ]);
            dd($interprete);
            $this->interprete_id = $interprete->id;
        }
        dd($interprete);
        session()->flash('message', $this->interprete_id ? 'Intérprete actualizado exitosamente.' : 'Intérprete creado exitosamente.');

        // return redirect()->route('interpretes.index');
        $this->reset();
        $this->modal = false;
    }
}
