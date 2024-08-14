<?php

namespace App\Http\Controllers;

use App\Models\Classified;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{
    public function index()
    {
        $classifieds = Classified::with('category', 'tags', 'images')->latest()->paginate(10);
        return view('frontend.classifieds.index', compact('classifieds'));
    }

    public function show(Classified $classified)
    {
        $classified->load('category', 'tags', 'images');
        return view('frontend.classifieds.show', compact('classified'));
    }

    public function category(Classified $classified)
    {
        $categories = Category::all();
        return view('frontend.classifieds.category', compact('categories', 'classified'));
    }

    public function search(Request $request)
    {
        $query = Classified::query();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('id', $request->tag_id);
            });
        }

        $classifieds = $query->with('category', 'tags', 'images')->latest()->paginate(10);

        return view('frontend.classifieds.search', compact('classifieds'));
    }
}
