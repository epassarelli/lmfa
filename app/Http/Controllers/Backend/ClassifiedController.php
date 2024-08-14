<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassifiedRequest;
use App\Http\Requests\UpdateClassifiedRequest;
use App\Models\Classified;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class ClassifiedController extends Controller
{
    public function index()
    {
        $classifieds = Classified::with('category', 'tags', 'images')->get();
        return view('backend.classifieds.index', compact('classifieds'));
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('backend.classifieds.create', compact('categories', 'tags'));
    }

    public function store(StoreClassifiedRequest $request)
    {
        $classified = Classified::create($request->validated());

        if ($request->has('tags')) {
            $classified->tags()->sync($request->tags);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('classified_images');
                $classified->images()->create(['image_path' => $path]);
            }
        }

        return redirect()->route('classifieds.index')
            ->with('success', 'Classified created successfully.');
    }

    public function edit(Classified $classified)
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('backend.classifieds.edit', compact('classified', 'categories', 'tags'));
    }

    public function update(UpdateClassifiedRequest $request, Classified $classified)
    {
        $classified->update($request->validated());

        if ($request->has('tags')) {
            $classified->tags()->sync($request->tags);
        }

        if ($request->hasFile('images')) {
            $classified->images()->delete(); // Remove old images
            foreach ($request->file('images') as $image) {
                $path = $image->store('classified_images');
                $classified->images()->create(['image_path' => $path]);
            }
        }

        return redirect()->route('classifieds.index')
            ->with('success', 'Classified updated successfully.');
    }

    public function destroy(Classified $classified)
    {
        $classified->delete();

        return redirect()->route('classifieds.index')
            ->with('success', 'Classified deleted successfully.');
    }
}
