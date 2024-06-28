<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{

    public function index()
    {
        // Obtiene todos los usuarios con sus roles
        $data = User::with('roles')->select(['id', 'name', 'email'])->get();

        // Pasar los datos a la vista
        return view('backend.users.index', compact('data'));
    }

    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         // $data = User::with('roles')->get();
    //         $data = User::with('roles')->select(['id', 'name', 'email'])->get();
    //         // \Log::info($data); // Añade esta línea para ver los datos en el log de Laravel
    //         dd($data);
    //         return DataTables::of($data)
    //             ->addColumn('roles', function ($row) {
    //                 return $row->roles->pluck('name')->implode(', ');
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $editUrl = route('users.edit', $row->id);
    //                 $deleteUrl = route('users.destroy', $row->id);
    //                 return '
    //                     <a href="' . $editUrl . '" class="btn btn-warning">
    //                         <i class="fas fa-edit"></i>
    //                     </a>
    //                     <form action="' . $deleteUrl . '" method="POST" style="display:inline-block;">
    //                         ' . csrf_field() . '
    //                         ' . method_field('DELETE') . '
    //                         <button type="submit" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro de eliminar este usuario?\')">
    //                             <i class="fas fa-trash-alt"></i>
    //                         </button>
    //                     </form>
    //                 ';
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('backend.users.index');
    // }

    public function create()
    {
        $roles = Role::all();
        return view('backend.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->roles);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('backend.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:6',
            'roles' => 'required|array',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
