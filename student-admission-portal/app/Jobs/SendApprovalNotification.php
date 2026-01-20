<?php

namespace App\Jobs;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendApprovalNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public string $type
    ) {}

    public function handle(): void
    {
        if (!$this->application->student || !$this->application->student->email) {
            return;
        }

        \Illuminate\Support\Facades\Mail::to($this->application->student->email)
            ->send(new \App\Mail\ApplicationResult($this->application, $this->type));
    }
}
