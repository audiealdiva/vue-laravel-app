<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function index(Request $request)
  {
      $query = User::with('role');
  
      if ($request->filled('name')) {
          $query->where('name', $request->name);
      }
  
      if ($request->filled('search')) {
          $query->where(function ($q) use ($request) {
              $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
          });
      }
  
      if ($request->has('sort_by') && in_array($request->sort_by, ['name', 'email', 'role_id', 'created_at'])) {
          $direction = $request->get('sort_direction', 'asc') == 'desc' ? 'desc' : 'asc';
          $query->orderBy($request->sort_by, $direction);
      } else {
          $query->orderBy('created_at', 'desc');
      }
  
      $perPage = $request->get('per_page', 50);
      $users = $query->paginate($perPage);
  
      return response()->json([
          'total_data' => $users->total(),
          'data' => $users->items(),
      ]);
  }  
  
  public function store(Request $request)
  {
      $validatedData = $request->validate([
          'name' => 'required|string|max:255',
          'email' => 'required|email|max:255|unique:users,email',
          'password' => 'required|string|min:8',
          'role_id' => 'required|exists:roles,id',
      ]);

      $user = User::create([
          'name' => $validatedData['name'],
          'email' => $validatedData['email'],
          'password' => bcrypt($validatedData['password']),
          'role_id' => $validatedData['role_id'],
      ]);

      return response()->json([
        'message' => 'User created successfully',
        'data' => $user,
      ], 201);
  }

  public function update(Request $request, $id)
  {
      $user = User::findOrFail($id);

      $validatedData = $request->validate([
          'name' => 'required|string|max:255',
          'email' => 'required|email|max:255',
          'role_id' => 'required|exists:roles,id',
      ]);

      if ($request->filled('password')) {
          $request->validate([
              'password' => 'required|string|min:8',
          ]);
          $validatedData['password'] = bcrypt($request->password);
      } else {
          unset($validatedData['password']);
      }

      $user->update($validatedData);

      return response()->json([
        'message' => 'User updated successfully',
        'data' => $user,
      ], 200);
  }

  public function destroy($id)
  {
      $user = User::findOrFail($id);
      $user->delete();
      
      return response()->json(['message' => 'User deleted successfully'], 200);
  }
}

