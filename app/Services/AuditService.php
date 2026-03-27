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

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @param  list<string>  $keys
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}|null
     */
    public function diffTracked(array $old, array $new, array $keys): ?array
    {
        $oldOut = [];
        $newOut = [];
        foreach ($keys as $key) {
            $a = $old[$key] ?? null;
            $b = $new[$key] ?? null;
            if ($this->auditValuesEqual($a, $b)) {
                continue;
            }
            $oldOut[$key] = $a;
            $newOut[$key] = $b;
        }

        if ($oldOut === []) {
            return null;
        }

        return [$oldOut, $newOut];
    }

    private function auditValuesEqual(mixed $a, mixed $b): bool
    {
        if ($a instanceof \DateTimeInterface) {
            $a = $a->format('Y-m-d');
        }
        if ($b instanceof \DateTimeInterface) {
            $b = $b->format('Y-m-d');
        }

        return $a == $b;
    }
}
