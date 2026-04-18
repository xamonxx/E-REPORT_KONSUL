<?php

namespace App\Observers;

use App\Models\AuditLog;

class AuditObserver
{
    public function created($model)
    {
        AuditLog::logCreated($model);
    }

    public function updated($model)
    {
        $original = $model->originalAttributes();
        
        if (!empty($original)) {
            AuditLog::logUpdated($model, $original);
        }
    }

    public function deleted($model)
    {
        AuditLog::logDeleted($model);
    }
}