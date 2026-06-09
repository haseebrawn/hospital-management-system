<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\MedicineStoreRequest;
use App\Http\Requests\Web\MedicineUpdateRequest;
use App\Models\Medicine;
use App\Services\HospitalNotificationService;
use Illuminate\Http\Request;

class MedicinesController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $medicines = Medicine::query()
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $statusOptions = ['available', 'unavailable'];

        return view('modules.medicines.index', compact('medicines', 'search', 'status', 'statusOptions'));
    }

    public function create()
    {
        $statusOptions = ['available', 'unavailable'];

        return view('modules.medicines.create', compact('statusOptions'));
    }

    public function store(MedicineStoreRequest $request, HospitalNotificationService $notifications)
    {
        $medicine = Medicine::create($request->validated());

        $notifications->notifyRoles(['super_admin', 'admin', 'pharmacist'], [
            'title' => 'Medicine added',
            'message' => "{$medicine->name} was added to pharmacy stock.",
            'module' => 'pharmacy',
            'type' => 'success',
            'url' => route('medicines.show', $medicine),
            'icon' => 'fa-solid fa-pills',
        ], $request->user());

        return redirect()
            ->route('medicines.index')
            ->with('status', 'Medicine created successfully.');
    }

    public function show(Medicine $medicine)
    {
        return view('modules.medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine)
    {
        $statusOptions = ['available', 'unavailable'];

        return view('modules.medicines.edit', compact('medicine', 'statusOptions'));
    }

    public function update(MedicineUpdateRequest $request, Medicine $medicine, HospitalNotificationService $notifications)
    {
        $medicine->update($request->validated());

        $type = ((int) $medicine->stock <= 10 || $medicine->status === 'unavailable') ? 'warning' : 'info';
        $message = $type === 'warning'
            ? "{$medicine->name} needs pharmacy attention. Stock: {$medicine->stock}, status: {$medicine->status}."
            : "{$medicine->name} pharmacy details were updated.";

        $notifications->notifyRoles(['super_admin', 'admin', 'pharmacist'], [
            'title' => $type === 'warning' ? 'Medicine stock alert' : 'Medicine updated',
            'message' => $message,
            'module' => 'pharmacy',
            'type' => $type,
            'url' => route('medicines.show', $medicine),
            'icon' => $type === 'warning' ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-pills',
        ], $request->user());

        return redirect()
            ->route('medicines.show', $medicine)
            ->with('status', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return redirect()
            ->route('medicines.index')
            ->with('status', 'Medicine deleted successfully.');
    }
}
