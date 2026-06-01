<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\BillingStoreRequest;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Patient;
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
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('patient', function ($p) use ($search) {
                    $p->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statusOptions = ['pending', 'paid', 'cancelled'];

        return view('modules.billing.index', compact('billings', 'search', 'status', 'statusOptions'));
    }

    public function create()
    {
        $patients = Patient::query()->orderByDesc('id')->limit(200)->get();
        $typeOptions = ['lab', 'medicine', 'appointment', 'other'];

        return view('modules.billing.create', compact('patients', 'typeOptions'));
    }

    public function store(BillingStoreRequest $request)
    {
        $data = $request->validated();

        $items = collect($data['items'])
            ->map(function (array $item) {
                return [
                    'service_name' => $item['service_name'],
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'type' => $item['type'],
                ];
            })
            ->values();

        $totalAmount = $items->sum(fn ($i) => $i['price'] * $i['quantity']);

        $billing = DB::transaction(function () use ($data, $items, $totalAmount) {
            $billing = Billing::create([
                'patient_id' => $data['patient_id'],
                'created_by' => Auth::id(),
                'approved_by' => null,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                BillingItem::create([
                    'billing_id' => $billing->id,
                    'service_name' => $item['service_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'type' => $item['type'],
                ]);
            }

            return $billing;
        });

        return redirect()
            ->route('billing.show', $billing)
            ->with('status', 'Invoice created successfully.');
    }

    public function show(Billing $billing)
    {
        $billing->load(['patient', 'items', 'creator', 'approver']);

        return view('modules.billing.show', compact('billing'));
    }

    public function pay(Billing $billing)
    {
        if ($billing->status !== 'pending') {
            return redirect()
                ->route('billing.show', $billing)
                ->with('status', 'Invoice is not pending.');
        }

        $billing->update([
            'status' => 'paid',
            'approved_by' => Auth::id(),
        ]);

        return redirect()
            ->route('billing.show', $billing)
            ->with('status', 'Invoice marked as paid.');
    }

    public function cancel(Billing $billing)
    {
        if ($billing->status === 'paid') {
            return redirect()
                ->route('billing.show', $billing)
                ->with('status', 'Paid invoice cannot be cancelled.');
        }

        $billing->update([
            'status' => 'cancelled',
        ]);

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
}
