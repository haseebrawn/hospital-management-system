<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\MedicineStoreRequest;
use App\Http\Requests\Web\MedicineUpdateRequest;
use App\Models\Medicine;
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

    public function store(MedicineStoreRequest $request)
    {
        Medicine::create($request->validated());

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

    public function update(MedicineUpdateRequest $request, Medicine $medicine)
    {
        $medicine->update($request->validated());

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
