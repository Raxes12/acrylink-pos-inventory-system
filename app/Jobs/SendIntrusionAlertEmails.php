<?php

namespace App\Jobs;

use App\Models\IntrusionAlert;
use App\Services\IntrusionDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendIntrusionAlertEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $alertId)
    {
        $this->onQueue('notifications');
    }

    public function handle(IntrusionDetectionService $intrusionDetection): void
    {
        $alert = IntrusionAlert::find($this->alertId);

        if ($alert) {
            $intrusionDetection->sendAlertEmails($alert);
        }
    }
}
