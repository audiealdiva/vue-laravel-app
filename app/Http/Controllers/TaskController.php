<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('is_done')) {
            $query->where('is_done', $request->is_done);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('sort_by') && in_array($request->sort_by, ['project_id', 'employee_id', 'title', 'duedate', 'created_at'])) {
            $direction = $request->get('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 50);
        $tasks = Task::with(['project', 'employee'])->paginate($perPage);

        return response()->json([
            'total_data' => $tasks->total(),
            'data' => $tasks->items(),
        ]);
    }

    public function show($id)
    {
        $task = Task::findOrFail($id);
        return response()->json($task);
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'project_id' => 'required|exists:projects,id',
    //         'employee_id' => 'required|exists:employees,id',
    //         'title' => 'required|string|max:100',
    //         'description' => 'nullable|string',
    //         'details' => 'nullable|json',
    //         'duedate' => 'nullable|date',
    //         'is_done' => 'boolean',
    //         'document' => 'nullable|string',
    //     ]);

    //     $data = $request->all();
    //     $data['id'] = Str::uuid();

    //     $task = Task::create($data);

    //     return response()->json([
    //         'message' => 'Task created successfully',
    //         'data' => $task,
    //     ], 201);
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|string',
            'employee_id' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'is_done' => 'required|boolean',
            'duedate' => 'nullable|date',
            'document' => 'nullable|mimes:pdf|max:2048'
        ]);

        $task = new Task($validated);
        
        if ($request->hasFile('document')) {
            $originalName = $request->file('document')->getClientOriginalName();
            $documentPath = $request->file('document')->storeAs('documents', $originalName, 'public');
            $task->document = Storage::url($documentPath);
        }

        $task->save();

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task,
        ], 201);
    }

    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'details' => 'nullable|json',
            'duedate' => 'nullable|date',
            'is_done' => 'boolean',
            'document' => 'nullable|mimes:pdf|max:5120',
        ]);

        $task = Task::findOrFail($id);

        if ($request->hasFile('document')) {
            if ($task->document) {
                Storage::delete('public/' . basename($task->document));
            }

            $originalName = $request->file('document')->getClientOriginalName();
            $documentPath = $request->file('document')->storeAs('documents', $originalName, 'public');
            $task->document = Storage::url($documentPath);
        }

        $task->update($validated);
        $task->save();

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'project_id' => 'required|exists:projects,id',
    //         'employee_id' => 'required|exists:employees,id',
    //         'title' => 'required|string|max:100',
    //         'description' => 'nullable|string',
    //         'details' => 'nullable|json',
    //         'duedate' => 'nullable|date',
    //         'is_done' => 'boolean',
    //         'document' => 'nullable|string',
    //     ]);

    //     $task = Task::findOrFail($id);
    //     $task->update($request->all());

    //     return response()->json([
    //         'message' => 'Task updated successfully',
    //         'data' => $task,
    //     ]);
    // }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
    
        if ($task->document && Storage::exists($task->document)) {
            Storage::delete($task->document);
        }
    
        $task->delete();
    
        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
    
    public function showAuditTrail($id)
    {
        $task = Task::findOrFail($id);
        $auditTrail = $task->audits;

        return response()->json([
            'audit_trail' => $auditTrail,
        ]);
    }
}
