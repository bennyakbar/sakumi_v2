<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentObligation;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendArrearsReminder extends Command
{
    protected $signature = 'arrears:remind';

    protected $description = 'Send WhatsApp reminders to students with arrears exceeding threshold';

    public function handle(WhatsAppService $whatsAppService): int
    {
        $thresholdMonths = (int) getSetting('arrears_threshold_months', 1);

        $studentsWithArrears = Student::where('status', 'active')
            ->whereNotNull('parent_whatsapp')
            ->whereHas('obligations', fn ($q) => $q->where('is_paid', false))
            ->with(['obligations' => fn ($q) => $q->where('is_paid', false)->with('feeType')])
            ->get();

        $sent = 0;

        foreach ($studentsWithArrears as $student) {
            $unpaidCount = $student->obligations->count();
            if ($unpaidCount < $thresholdMonths) {
                continue;
            }

            $totalArrears = $student->obligations->sum('amount');
            $feeTypes = $student->obligations->pluck('feeType.name')->unique()->implode(', ');

            $notification = $whatsAppService->sendArrearsReminder($student, $feeTypes, $totalArrears);
            if ($notification) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} arrears reminder(s).");

        return self::SUCCESS;
    }
}
