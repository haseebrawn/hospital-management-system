<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\BillingPayment;
use App\Http\Requests\BillingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    // Create Invoice
    public function createInvoice(BillingRequest $request)
    {
        $validated = $request->validated();
        $items = collect($validated['items'])->map(function (array $item) {
            return [
                'service_name' => $item['service_name'],
                'quantity' => (int) $item['quantity'],
                'price' => (float) $item['price'],
                'type' => $item['type'],
                'source_type' => $item['source_type'] ?? null,
                'source_id' => $item['source_id'] ?? null,
                'source_name' => $item['source_name'] ?? null,
            ];
        });

        $total = $items->sum(fn (array $item) => $item['price'] * $item['quantity']);

        $billing = DB::transaction(function () use ($validated, $items, $total) {
            $billing = Billing::create([
                'patient_id' => $validated['patient_id'],
                'created_by' => Auth::id(),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => null,
                'paid_amount' => 0,
                'balance_due' => $total,
            ]);

            $items->each(function (array $item) use ($billing) {
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
            });

            $billing->update([
                'invoice_number' => $this->generateInvoiceNumber($billing->id),
            ]);

            return $billing;
        });

        return response()->json([
            'message' => 'Invoice created successfully',
            'billing' => $billing->load(['patient', 'items']),
        ], 201);
    }

    // All Billing Records
    public function index()
    {
        return response()->json(Billing::with('patient')->latest()->get());
    }

    // Single Invoice
    public function show($id)
    {
        $billing = Billing::with('patient', 'items', 'payments.receiver')->find($id);

        if (!$billing) {
            return response()->json(['message' => 'Invoice Not Found'], 404);
        }

        return response()->json($billing);
    }

    // Mark as PAID
    public function markAsPaid($id)
    {
        $billing = Billing::find($id);

        if (!$billing) {
            return response()->json(['message' => 'Invoice Not Found'], 404);
        }

        $this->recordPayment($billing, (float) $billing->balance_due, 'cash', Auth::id(), null, 'Marked paid from API.');

        return response()->json(['message' => 'Invoice successfully paid']);
    }

    public function storePayment(Request $request, $id)
    {
        $billing = Billing::find($id);

        if (!$billing) {
            return response()->json(['message' => 'Invoice Not Found'], 404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,online,insurance',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ((float) $validated['amount'] > (float) $billing->balance_due) {
            return response()->json(['message' => 'Amount cannot exceed the outstanding balance.'], 422);
        }

        $this->recordPayment(
            $billing,
            (float) $validated['amount'],
            $validated['payment_method'],
            Auth::id(),
            $validated['reference'] ?? null,
            $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Payment recorded successfully',
            'billing' => $billing->fresh()->load(['patient', 'items', 'payments.receiver']),
        ]);
    }

    // Cancel Invoice
    public function cancelInvoice($id)
    {
        $billing = Billing::find($id);

        if (!$billing) {
            return response()->json(['message' => 'Invoice Not Found'], 404);
        }

        $billing->update([
            'status' => 'cancelled'
        ]);

        return response()->json(['message' => 'Invoice Cancelled Successfully']);
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

            $billing->refresh();
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
}
