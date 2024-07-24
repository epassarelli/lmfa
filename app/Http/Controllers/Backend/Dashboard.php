<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');
        // Traer las cantidades de cada entidad
        
        
        return view('backend.dashboard', compact('user', 'roles', 'permissions'));
    }
}
