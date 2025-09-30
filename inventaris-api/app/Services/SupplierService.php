<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierService
{
    /**
     * Get all suppliers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSuppliers()
    {
        return Supplier::latest()->get();
    }

    /**
     * Get active suppliers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveSuppliers()
    {
        return Supplier::where('status', 'active')->latest()->get();
    }

    /**
     * Get a specific supplier by id.
     *
     * @param int $id
     * @return \App\Models\Supplier
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getSupplierById($id)
    {
        return Supplier::findOrFail($id);
    }

    /**
     * Create a new supplier.
     *
     * @param array $data
     * @return \App\Models\Supplier
     */
    public function createSupplier($data)
    {
        try {
            DB::beginTransaction();
            
            $supplier = Supplier::create([
                'name' => $data['name'],
                'contact_person' => $data['contact_person'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);
            
            DB::commit();
            return $supplier;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating supplier: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a supplier.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Supplier
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateSupplier($id, $data)
    {
        try {
            DB::beginTransaction();
            
            $supplier = $this->getSupplierById($id);
            $supplier->update([
                'name' => $data['name'] ?? $supplier->name,
                'contact_person' => $data['contact_person'] ?? $supplier->contact_person,
                'email' => $data['email'] ?? $supplier->email,
                'phone' => $data['phone'] ?? $supplier->phone,
                'address' => $data['address'] ?? $supplier->address,
                'notes' => $data['notes'] ?? $supplier->notes,
                'status' => $data['status'] ?? $supplier->status,
            ]);
            
            DB::commit();
            return $supplier;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Supplier not found: ' . $id);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating supplier: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a supplier.
     *
     * @param int $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteSupplier($id)
    {
        try {
            DB::beginTransaction();
            
            $supplier = $this->getSupplierById($id);
            $supplier->delete();
            
            DB::commit();
            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Supplier not found: ' . $id);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting supplier: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update supplier status.
     *
     * @param int $id
     * @param string $status
     * @return \App\Models\Supplier
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateSupplierStatus($id, $status)
    {
        try {
            DB::beginTransaction();
            
            $supplier = $this->getSupplierById($id);
            $supplier->update([
                'status' => $status
            ]);
            
            DB::commit();
            return $supplier;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Supplier not found: ' . $id);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating supplier status: ' . $e->getMessage());
            throw $e;
        }
    }
}