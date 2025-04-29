<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Jobs\ExportEmployeesJob;

class EmployeeController extends Controller
{
  public function index(Request $request)
  {
        $query = Employee::query();
    
        if ($request->filled('name')) {
            $query->where('name', $request->name);
        }
    
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
    
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
    
        if ($request->has('sort_by') && in_array($request->sort_by, ['name', 'email', 'created_at'])) {
            $direction = $request->get('sort_direction', 'asc') == 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    
        $perPage = $request->get('per_page', 50);
        $employees = $query->paginate($perPage);
    
        return response()->json([
            'total_data' => $employees->total(),
            'data' => $employees->items(),
        ]);
    }
    
    public function show($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        return response()->json($employee);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'is_active' => 'required|boolean',
            'metadata' => 'nullable|json',
        ]);

        $employee = Employee::create($request->all());

        return response()->json([
          'message' => 'Employee created successfully',
          'data' => $employee
      ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'is_active' => 'required|boolean',
            'metadata' => 'nullable|json',
        ]);

        $employee = Employee::findOrFail($id);
        $employee->update($request->all());

        return response()->json([
          'message' => 'Employee updated successfully',
          'data' => $employee,
        ], 200);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    public function showAuditTrail($id)
    {
        $employee = Employee::findOrFail($id);
        $auditTrail = $employee->audits;

        return response()->json([
            'audit_trail' => $auditTrail,
        ]);
    }

    public function export(Request $request)
    {
        $fields = $request->input('fields');
        $user = auth()->user();

        ExportEmployeesJob::dispatch($fields, $user);

        return response()->json(['message' => 'Export job dispatched!']);
    }

    // public function restore($id)
    // {
    //     $employee = Employee::withTrashed()->findOrFail($id);
    //     $employee->restore();

    //     return response()->json($employee);
    // }

    // public function trashed()
    // {
    //     $trashedEmployees = Employee::onlyTrashed()->get();
    //     return response()->json($trashedEmployees);
    // }
}