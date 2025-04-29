<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $auditable = $request->input('auditable_type'); 
        $auditableId = $request->input('auditable_id');

        $logs = Audit::where('auditable_type', $auditable)
                     ->where('auditable_id', $auditableId)
                     ->orderBy('created_at', 'desc')
                     ->get();

        return response()->json($logs);
    }

    public function showFor($type, $id)
    {
        $model = app("App\\Models\\" . $type)::findOrFail($id);
        
        $audits = $model->audits()->latest()->get(); 

        return view('audit.trail', compact('model', 'audits'));
    }

    public function storeAudit($model, $oldValues, $newValues, $userId)
    {
        Audit::create([
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'event' => 'updated',
            'user_id' => $userId,
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
        ]);
    }
}
