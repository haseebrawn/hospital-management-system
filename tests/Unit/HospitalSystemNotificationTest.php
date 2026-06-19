<?php

namespace Tests\Unit;

use App\Notifications\HospitalSystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class HospitalSystemNotificationTest extends TestCase
{
    public function test_hospital_system_notification_is_queued(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(HospitalSystemNotification::class));
    }
}
