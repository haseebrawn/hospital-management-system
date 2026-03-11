<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Http\Requests\BillingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    // Create Invoice
    public function createInvoice(BillingRequest $request)
    {
        $total = 0;
        foreach ($request->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $billing = Billing::create([
            'patient_id' => $request->patient_id,
            'created_by' => Auth::id(),
            'total_amount' => $total,
            'status' => 'pending'
        ]);

        foreach ($request->items as $item) {
            BillingItem::create([
                'billing_id' => $billing->id,
                'service_name' => $item['service_name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'type' => $item['type']
            ]);
        }

        return response()->json([
            'message' => 'Invoice Created Successfully',
            'billing' => $billing->load('items')
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
        $billing = Billing::with('patient', 'items')->find($id);

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

        $billing->update([
            'status' => 'paid',
            'approved_by' => Auth::id()
        ]);

        return response()->json(['message' => 'Invoice Successfully Paid']);
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
}
