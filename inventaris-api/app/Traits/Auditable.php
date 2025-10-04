<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable()
    {
        // Merekam ketika model dibuat
        static::created(function ($model) {
            static::audit('created', $model, null, $model->getAttributes());
        });

        // Merekam ketika model diperbarui
        static::updated(function ($model) {
            $oldAttributes = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newAttributes = array_intersect_key($model->getAttributes(), $model->getChanges());
            
            static::audit('updated', $model, $oldAttributes, $newAttributes);
        });

        // Merekam ketika model dihapus
        static::deleted(function ($model) {
            static::audit('deleted', $model, $model->getAttributes(), null);
        });
    }

    /**
     * Membuat log audit
     */
    protected static function audit($action, $model, $oldValues = null, $newValues = null)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}