<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Permission::all();
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $editUrl = route('permissions.edit', $row->id);
                    $deleteUrl = route('permissions.destroy', $row->id);
                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" style="display:inline-block;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro de eliminar este permiso?\')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.permissions.index');
    }

    public function create()
    {
        return view('backend.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permiso creado exitosamente.');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('backend.permissions.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permiso actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permiso eliminado exitosamente.');
    }
}
