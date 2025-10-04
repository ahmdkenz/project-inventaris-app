<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Resources\AuditLogResource;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'action' => 'nullable|string',
            'model_type' => 'nullable|string',
            'model_id' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filter berdasarkan action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter berdasarkan model_type
        if ($request->has('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter berdasarkan model_id
        if ($request->has('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        // Filter berdasarkan user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = $request->input('per_page', 15);
        $auditLogs = $query->paginate($perPage);

        return AuditLogResource::collection($auditLogs);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $auditLog = AuditLog::with('user')->findOrFail($id);
        return new AuditLogResource($auditLog);
    }
}