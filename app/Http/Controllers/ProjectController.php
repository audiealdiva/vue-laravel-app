<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('sort_by') && in_array($request->sort_by, ['name', 'datestart', 'dateend', 'created_at'])) {
            $direction = $request->get('sort_direction', 'asc') == 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 50);
        $projects = $query->paginate($perPage);

        return response()->json([
            'total_data' => $projects->total(),
            'data' => $projects->items(),
        ]);
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);

        return response()->json($project);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_completed' => 'required|boolean',
            'settings' => 'nullable|json',
            'datestart' => 'nullable|date',
            'dateend' => 'nullable|date',
        ]);

        $project = Project::create($request->all());

        return response()->json([
            'message' => 'Project created successfully',
            'data' => $project
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_completed' => 'required|boolean',
            'settings' => 'nullable|json',
            'datestart' => 'nullable|date',
            'dateend' => 'nullable|date',
        ]);

        $project = Project::findOrFail($id);
        $project->update($request->all());

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => $project,
        ], 200);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
    
    public function showAuditTrail($id)
    {
        $project = Project::findOrFail($id);
        $auditTrail = $project->audits;

        return response()->json([
            'audit_trail' => $auditTrail,
        ]);
    }
}
