<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = $this->supplierService->getAllSuppliers();
        
        return response()->json([
            'status' => 'success',
            'data' => $suppliers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $supplier = $this->supplierService->createSupplier($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier created successfully',
            'data' => $supplier
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $supplier = $this->supplierService->getSupplierById($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $supplier
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'contact_person' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $supplier = $this->supplierService->updateSupplier($id, $validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier updated successfully',
                'data' => $supplier
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->supplierService->deleteSupplier($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this supplier because it is being used by purchase orders'
            ], 409);
        }
    }
    
    /**
     * Update supplier status.
     */
    public function updateStatus(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $supplier = $this->supplierService->updateSupplierStatus($id, $request->status);

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier status updated successfully',
                'data' => $supplier
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }
    }
}
