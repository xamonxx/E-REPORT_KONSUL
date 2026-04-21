<?php

namespace App\Jobs;

use App\Models\ConsultationImport;
use App\Services\ConsultationImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessConsultationImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $importId
    ) {
    }

    public function handle(ConsultationImportService $service): void
    {
        $import = ConsultationImport::find($this->importId);

        if (!$import) {
            return;
        }

        $service->process($import);
    }
}
