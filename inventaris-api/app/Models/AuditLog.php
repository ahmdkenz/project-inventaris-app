<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convert old_values from JSON to array
     */
    public function getOldValuesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Convert new_values from JSON to array
     */
    public function getNewValuesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Convert old_values to JSON before saving
     */
    public function setOldValuesAttribute($value)
    {
        $this->attributes['old_values'] = $value ? json_encode($value) : null;
    }

    /**
     * Convert new_values to JSON before saving
     */
    public function setNewValuesAttribute($value)
    {
        $this->attributes['new_values'] = $value ? json_encode($value) : null;
    }
}