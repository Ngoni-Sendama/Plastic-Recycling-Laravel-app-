<?php

namespace App\Observers;

use App\Services\AuditLogWriter;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    public function __construct(private readonly AuditLogWriter $writer) {}

    public function created(Model $model): void
    {
        $this->writer->write('created', $model, class_basename($model).' created.', [], $model->toArray());
    }

    public function updated(Model $model): void
    {
        $this->writer->write(
            'updated',
            $model,
            class_basename($model).' updated.',
            $model->getOriginal(),
            $model->getChanges(),
        );
    }

    public function deleted(Model $model): void
    {
        $this->writer->writeForDeletion('deleted', $model, class_basename($model).' deleted.', $model->getOriginal());
    }

    public function restored(Model $model): void
    {
        $this->writer->write('restored', $model, class_basename($model).' restored.', [], $model->toArray());
    }
}
