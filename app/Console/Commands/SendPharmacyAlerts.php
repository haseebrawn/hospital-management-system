<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Services\HospitalNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPharmacyAlerts extends Command
{
    protected $signature = 'pharmacy:send-alerts';

    protected $description = 'Send medicine expiry and reorder alerts to pharmacy roles.';

    public function handle(HospitalNotificationService $notifications): int
    {
        $expiryThreshold = now()->addDays(30)->toDateString();

        $medicines = Medicine::query()
            ->where(function ($query) use ($expiryThreshold) {
                $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<=', $expiryThreshold)
                    ->where('expiry_alert_sent', false);
            })
            ->orWhere(function ($query) {
                $query->whereColumn('stock', '<=', 'reorder_level')
                    ->where('reorder_alert_sent', false);
            })
            ->get();

        foreach ($medicines as $medicine) {
            $isExpiry = $medicine->expiry_date && Carbon::parse($medicine->expiry_date)->lessThanOrEqualTo(now()->addDays(30));
            $isReorder = (int) $medicine->stock <= (int) ($medicine->reorder_level ?? 10);

            if ($isExpiry) {
                $notifications->notifyRoles(['super_admin', 'admin', 'pharmacist'], [
                    'title' => 'Medicine expiry alert',
                    'message' => "{$medicine->name} expires on {$medicine->expiry_date}.",
                    'module' => 'pharmacy',
                    'type' => 'warning',
                    'url' => route('medicines.show', $medicine),
                    'icon' => 'fa-solid fa-calendar-exclamation',
                ]);

                $medicine->update(['expiry_alert_sent' => true]);
            }

            if ($isReorder) {
                $notifications->notifyRoles(['super_admin', 'admin', 'pharmacist'], [
                    'title' => 'Medicine reorder alert',
                    'message' => "{$medicine->name} is at {$medicine->stock} stock, reorder level {$medicine->reorder_level}.",
                    'module' => 'pharmacy',
                    'type' => 'warning',
                    'url' => route('medicines.show', $medicine),
                    'icon' => 'fa-solid fa-triangle-exclamation',
                ]);

                $medicine->update(['reorder_alert_sent' => true]);
            }
        }

        $this->info('Pharmacy alerts processed.');

        return self::SUCCESS;
    }
}
