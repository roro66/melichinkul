<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a critical action for audit trail.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(
        string $action,
        string $model,
        ?int $modelId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log from a model instance (e.g. after create/update/delete).
     */
    public function logModel(string $action, Model $model, string $description, ?array $oldValues = null): AuditLog
    {
        $newValues = $model->getAttributes();
        $newValues = array_filter($newValues, fn ($key) => ! in_array($key, ['created_at', 'updated_at'], true), ARRAY_FILTER_USE_KEY);

        return $this->log(
            action: $action,
            model: class_basename($model),
            modelId: $model->getKey(),
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }
}
