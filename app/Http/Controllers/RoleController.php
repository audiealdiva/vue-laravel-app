<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query();
    
        if ($request->filled('name')) {
            $query->where('name', $request->name);
        }
    
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
    
        if ($request->has('sort_by') && in_array($request->sort_by, ['name', 'created_at'])) {
            $direction = $request->get('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    
        $perPage = $request->get('per_page', 50);
        $roles = $query->paginate($perPage);
    
        return response()->json([
            'total_data' => $roles->total(),
            'data' => $roles->items(),
        ]);
    }


    public function store(Request $request)
    {
        $role = Role::create($request->only('name'));
        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->only('name'));
        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}
