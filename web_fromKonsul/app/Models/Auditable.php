<?php

namespace App\Models;

trait Auditable
{
    /**
     * Property declaration to avoid PHP 8.2+ dynamic property deprecation.
     */
    protected array $auditOriginalAttributes = [];

    public static function bootAuditable()
    {
        // Only capture originals before update — no need on every retrieved event
        static::updating(function ($model) {
            $model->auditOriginalAttributes = $model->getOriginal();
        });
    }

    public function originalAttributes(): array
    {
        return $this->auditOriginalAttributes;
    }
}