<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Interprete;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticiaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $noticias = Noticia::all();
        // return view('backend.noticias.index', compact('noticias'));

        $noticias = Noticia::with('interpretes')->paginate(10);
        // dd($noticias);
        return view('backend.noticias.index', compact('noticias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $interpretes = Interprete::all();
        return view('backend.noticias.create', compact('interpretes', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:noticias',
            'article' => 'required|string',
            'photo' => 'required|image|max:2048|dimensions:max_width=400,max_height=400',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:0,1',
            'interpretes' => 'nullable|array',
            'interpretes.*' => 'exists:interpretes,id',
            // 'biography' => 'nullable|string',
        ]);

        $photo = $request->file('photo')->store('public/noticias');

        $noticia = Noticia::create([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'article' => $request->input('article'),
            'photo' => $photo,
            'user_id' => $request->input('user_id'),
            'status' => $request->input('status'),
            'views' => 0,
        ]);

        if ($request->has('interpretes')) {
            $interpretes = $request->input('interpretes');
            $noticia->interpretes()->attach($interpretes);
        }

        // if ($request->has('biography')) {
        //     $noticia->biography()->create([
        //         'content' => $request->input('biography'),
        //     ]);
        // }

        return redirect()->route('noticias.index')->with('success', 'Noticia creada con éxito.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Noticia $noticia)
    {
        return view('backend.noticias.show', compact('noticia'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Noticia $noticia)
    {
        $users = User::all();
        $interpretes = Interprete::all();

        $noticia->load('interpretes');

        return view('backend.noticias.edit', compact('noticia', 'users', 'interpretes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Noticia $noticia)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:noticias,slug,' . $noticia->id,
            'article' => 'required|string',
            'photo' => 'nullable|image|max:2048|dimensions:max_width=400,max_height=400',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:0,1',
            'interpretes' => 'nullable|array',
            'interpretes.*' => 'exists:interpretes,id',
            // 'biography' => 'nullable|string',
        ]);

        $noticia->title = $request->input('title');
        $noticia->slug = $request->input('slug');
        $noticia->article = $request->input('article');
        $noticia->user_id = $request->input('user_id');
        $noticia->status = $request->input('status');

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('public/noticias');
            $noticia->photo = $photo;
        }

        $noticia->interpretes()->sync($request->input('interpretes'));

        // if ($request->has('biography')) {
        //     if ($noticia->biography) {
        //         $noticia->biography->content = $request->input('biography');
        //         $noticia->biography->save();
        //     } else {
        //         $noticia->biography()->create([
        //             'content' => $request->input('biography'),
        //         ]);
        //     }
        // } else {
        //     if ($noticia->biography) {
        //         $noticia->biography->delete();
        //     }
        // }

        $noticia->save();

        return redirect()->route('noticias.index')->with('success', 'Noticia actualizada con éxito.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
