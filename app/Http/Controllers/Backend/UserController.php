<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use App\Support\BackendListing;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrador');
    }

    public function index()
    {
        $request = request();
        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['id', 'name', 'email'],
            'name',
            'asc'
        );

        // Obtiene todos los usuarios con sus roles
        $data = User::with('roles:id,name')
            ->select(['id', 'name', 'email'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('roles', fn ($roles) => $roles->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

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
        $apiTokens = $user->tokens()->latest()->get();

        return view('backend.users.edit', compact('user', 'roles', 'userRoles', 'apiTokens'));
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

    public function issueApiToken(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $token = $user->createToken($validated['token_name']);

        return redirect()
            ->route('users.edit', $user->id)
            ->with('success', 'Bearer token generado correctamente.')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function revokeApiToken($id, $tokenId)
    {
        $user = User::findOrFail($id);

        $token = $user->tokens()->whereKey($tokenId)->firstOrFail();
        $token->delete();

        return redirect()
            ->route('users.edit', $user->id)
            ->with('success', 'Bearer token revocado correctamente.');
    }
}
