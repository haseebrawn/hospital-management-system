<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\BillingPaymentRequest;
use App\Http\Requests\Web\BillingStoreRequest;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\BillingPayment;
use App\Models\Patient;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $billings = Billing::query()
            ->with(['patient', 'creator', 'approver'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('patient', function ($patientQuery) use ($search) {
                    $patientQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statusOptions = ['pending', 'partial', 'paid', 'cancelled'];

        return view('modules.billing.index', compact('billings', 'search', 'status', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $typeOptions = ['lab', 'medicine', 'appointment', 'other'];
        $sourceTypeOptions = ['appointment', 'lab_test', 'medicine', 'medical_record', 'other'];
        $paymentMethods = ['cash', 'card', 'bank_transfer', 'online', 'insurance'];
        $linkedSource = null;
        $prefilledPatientId = old('patient_id');
        $defaultItems = old('items', [
            ['service_name' => '', 'type' => 'other', 'quantity' => 1, 'price' => 0, 'source_type' => '', 'source_id' => '', 'source_name' => ''],
        ]);

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor'])->find($request->query('appointment_id'));

            if ($appointment) {
                $linkedSource = [
                    'title' => 'Linked Appointment',
                    'type' => 'appointment',
                    'id' => $appointment->id,
                    'patient_name' => trim((string) (($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? ''))),
                    'doctor_name' => optional($appointment->doctor)->name ?? '-',
                    'reason' => $appointment->reason ?: '-',
                    'notes' => $appointment->notes ?: '-',
                ];

                $prefilledPatientId = $appointment->patient_id;
                $defaultItems = [
                    [
                        'service_name' => trim((string) ($appointment->reason ?: 'Appointment consultation')),
                        'type' => 'appointment',
                        'quantity' => 1,
                        'price' => 0,
                        'source_type' => 'appointment',
                        'source_id' => $appointment->id,
                        'source_name' => trim((string) ($appointment->reason ?: ('Appointment #' . $appointment->id))),
                    ],
                ];
            }
        }

        return view('modules.billing.create', compact('patients', 'typeOptions', 'sourceTypeOptions', 'paymentMethods', 'linkedSource', 'prefilledPatientId', 'defaultItems'));
    }

    public function store(BillingStoreRequest $request, HospitalNotificationService $notifications)
    {
        $data = $request->validated();

        $items = collect($data['items'])
            ->map(function (array $item) {
                return [
                    'service_name' => $item['service_name'],
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'type' => $item['type'],
                    'source_type' => $item['source_type'] ?? null,
                    'source_id' => $item['source_id'] ?? null,
                    'source_name' => $item['source_name'] ?? null,
                ];
            })
            ->values();

        $totalAmount = $items->sum(fn ($item) => $item['price'] * $item['quantity']);

        $billing = DB::transaction(function () use ($data, $items, $totalAmount) {
            $billing = Billing::create([
                'patient_id' => $data['patient_id'],
                'created_by' => Auth::id(),
                'approved_by' => null,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => null,
                'paid_amount' => 0,
                'balance_due' => $totalAmount,
            ]);

            foreach ($items as $item) {
                BillingItem::create([
                    'billing_id' => $billing->id,
                    'service_name' => $item['service_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'type' => $item['type'],
                    'source_type' => $item['source_type'],
                    'source_id' => $item['source_id'],
                    'source_name' => $item['source_name'],
                ]);
            }

            $billing->update([
                'invoice_number' => $this->generateInvoiceNumber($billing->id),
            ]);

            return $billing;
        });

        $billing->load('patient');
        $patientName = trim((string) (($billing->patient->first_name ?? '') . ' ' . ($billing->patient->last_name ?? '')));

        $notifications->notifyRoles(['super_admin', 'admin', 'accountant'], [
            'title' => 'New invoice created',
            'message' => "Invoice {$billing->invoice_number} for {$patientName} was created for {$billing->total_amount}.",
            'module' => 'billing',
            'type' => 'success',
            'url' => route('billing.show', $billing),
            'icon' => 'fa-solid fa-file-invoice-dollar',
        ], $request->user());

        return redirect()
            ->route('billing.show', $billing)
            ->with('status', 'Invoice created successfully.');
    }

    public function show(Billing $billing)
    {
        $billing->load(['patient', 'items', 'creator', 'approver', 'payments.receiver']);
        $sourceChain = $this->buildSourceChain($billing);
        $paymentSummary = $this->buildPaymentSummary($billing);

        return view('modules.billing.show', compact('billing', 'sourceChain', 'paymentSummary'));
    }

    public function pay(Billing $billing, HospitalNotificationService $notifications)
    {
        if ($billing->status === 'paid') {
            return redirect()
                ->route('billing.show', $billing)
                ->with('status', 'Invoice is already paid.');
        }

        $outstanding = max(0, (float) $billing->balance_due);

        if ($outstanding <= 0) {
            return redirect()
                ->route('billing.show', $billing)
                ->with('status', 'Invoice has no outstanding balance.');
        }

        $this->recordPayment($billing, $outstanding, 'cash', Auth::id(), null, 'Full payment collected from quick action.');

        $notifications->notifyRoles(['super_admin', 'admin', 'accountant'], [
            'title' => 'Invoice paid',
            'message' => "{$billing->invoice_number} has been marked as paid.",
            'module' => 'billing',
            'type' => 'success',
            'url' => route('billing.show', $billing),
            'icon' => 'fa-solid fa-money-bill-wave',
        ], request()->user());

        return redirect()
            ->route('billing.show', $billing)
            ->with('status', 'Invoice marked as paid.');
    }

    public function storePayment(BillingPaymentRequest $request, Billing $billing, HospitalNotificationService $notifications)
    {
        if ($billing->status === 'cancelled') {
            return redirect()
                ->route('billing.show', $billing)
                ->with('status', 'Cancelled invoice cannot receive payments.');
        }

        $validated = $request->validated();
        $amount = (float) $validated['amount'];
        $method = $validated['payment_method'];

        if ($amount > (float) $billing->balance_due) {
            return back()->withErrors(['amount' => 'Amount cannot exceed the outstanding balance.'])->withInput();
        }

        $this->recordPayment(
            $billing,
            $amount,
            $method,
            Auth::id(),
            $validated['reference'] ?? null,
            $validated['notes'] ?? null
        );

        $notifications->notifyRoles(['super_admin', 'admin', 'accountant'], [
            'title' => $billing->status === 'paid' ? 'Invoice paid' : 'Partial payment received',
            'message' => "{$billing->invoice_number} received a {$amount} {$method} payment.",
            'module' => 'billing',
            'type' => 'success',
            'url' => route('billing.show', $billing),
            'icon' => 'fa-solid fa-money-bill-wave',
        ], request()->user());

        return redirect()
            ->route('billing.show', $billing)
            ->with('status', $billing->status === 'paid' ? 'Invoice marked as paid.' : 'Payment recorded successfully.');
    }

    public function receipt(Billing $billing)
    {
        $billing->load(['patient', 'items', 'payments.receiver', 'creator', 'approver']);
        $sourceChain = $this->buildSourceChain($billing);
        $paymentSummary = $this->buildPaymentSummary($billing);

        return view('modules.billing.receipt', compact('billing', 'sourceChain', 'paymentSummary'));
    }

    public function cancel(Billing $billing, HospitalNotificationService $notifications)
    {
        if ($billing->status === 'paid') {
            return redirect()
                ->route('billing.show', $billing)
                ->with('status', 'Paid invoice cannot be cancelled.');
        }

        $billing->update([
            'status' => 'cancelled',
        ]);

        $notifications->notifyRoles(['super_admin', 'admin', 'accountant'], [
            'title' => 'Invoice cancelled',
            'message' => "Invoice {$billing->invoice_number} has been cancelled.",
            'module' => 'billing',
            'type' => 'warning',
            'url' => route('billing.show', $billing),
            'icon' => 'fa-solid fa-ban',
        ], request()->user());

        return redirect()
            ->route('billing.show', $billing)
            ->with('status', 'Invoice cancelled.');
    }

    public function destroy(Billing $billing)
    {
        $billing->delete();

        return redirect()
            ->route('billing.index')
            ->with('status', 'Invoice deleted.');
    }

    private function generateInvoiceNumber(int $billingId): string
    {
        return sprintf('INV-%s-%06d', now()->format('Ymd'), $billingId);
    }

    private function recordPayment(
        Billing $billing,
        float $amount,
        string $method,
        ?int $receivedBy,
        ?string $reference,
        ?string $notes
    ): void {
        DB::transaction(function () use ($billing, $amount, $method, $receivedBy, $reference, $notes) {
            BillingPayment::create([
                'billing_id' => $billing->id,
                'received_by' => $receivedBy,
                'amount' => $amount,
                'payment_method' => $method,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            $paidAmount = (float) $billing->payments()->sum('amount');
            $balanceDue = max(0, (float) $billing->total_amount - $paidAmount);
            $status = $balanceDue <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');

            $billing->update([
                'payment_method' => $method,
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
                'status' => $status,
                'approved_by' => $status === 'paid' ? $receivedBy : $billing->approved_by,
            ]);
        });
    }

    private function buildSourceChain(Billing $billing): array
    {
        return $billing->items
            ->filter(fn (BillingItem $item) => filled($item->source_type) || filled($item->source_id) || filled($item->source_name))
            ->map(function (BillingItem $item) {
                $sourceLabel = ucwords(str_replace('_', ' ', (string) $item->source_type));
                $sourceName = $item->source_name ?: $item->service_name;

                return [
                    'label' => $sourceLabel !== '' ? $sourceLabel : 'Source',
                    'reference' => $item->source_id ? '#'.$item->source_id : '-',
                    'name' => $sourceName ?: '-',
                    'service' => $item->service_name,
                ];
            })
            ->values()
            ->all();
    }

    private function buildPaymentSummary(Billing $billing): array
    {
        $latestPayment = $billing->payments->sortByDesc('created_at')->first();
        $paidAmount = (float) $billing->paid_amount;
        $totalAmount = (float) $billing->total_amount;
        $balanceDue = (float) $billing->balance_due;
        $progress = $totalAmount > 0 ? min(100, round(($paidAmount / $totalAmount) * 100, 1)) : 0;

        return [
            'paid_amount' => $paidAmount,
            'total_amount' => $totalAmount,
            'balance_due' => $balanceDue,
            'progress' => $progress,
            'payment_count' => $billing->payments->count(),
            'latest_payment' => $latestPayment ? [
                'amount' => (float) $latestPayment->amount,
                'method' => $latestPayment->payment_method,
                'reference' => $latestPayment->reference,
                'notes' => $latestPayment->notes,
                'received_at' => optional($latestPayment->created_at)->format('Y-m-d H:i'),
                'received_by' => optional($latestPayment->receiver)->name ?? '-',
            ] : null,
        ];
    }
}
