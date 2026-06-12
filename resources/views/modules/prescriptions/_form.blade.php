@props([
    'prescription' => null,
    'appointments' => collect(),
    'doctors' => collect(),
    'medicines' => collect(),
    'statusOptions' => ['pending', 'dispensed', 'cancelled'],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $appointmentId = old('appointment_id', $prescription?->appointment_id);
        $doctorId = old('doctor_id', $prescription?->doctor_id);
        $description = old('description', $prescription?->description);
        $medicines = old('medicines', $prescription?->medicines);
        $status = old('status', $prescription?->status ?? 'pending');
        $existingItems = old('items');

        if ($existingItems === null) {
            $existingItems = $prescription?->items?->map(fn ($item) => [
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $item->medicine_name,
                'dosage' => $item->dosage,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'quantity' => $item->quantity,
                'instructions' => $item->instructions,
            ])->values()->all() ?? [];
        }

        $itemRows = collect($existingItems);

        while ($itemRows->count() < 3) {
            $itemRows->push([]);
        }
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Appointment</label>
            <select name="appointment_id" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                <option value="" disabled {{ $appointmentId ? '' : 'selected' }}>Select appointment</option>
                @foreach ($appointments as $appointment)
                    <option value="{{ $appointment->id }}" {{ (string) $appointmentId === (string) $appointment->id ? 'selected' : '' }}>
                        #{{ $appointment->id }} — {{ optional($appointment->patient)->mrn ?? 'No MRN' }} —
                        {{ optional($appointment->patient)->first_name }} {{ optional($appointment->patient)->last_name }}
                        ({{ $appointment->date }} {{ substr((string) $appointment->time, 0, 5) }})
                    </option>
                @endforeach
            </select>
        </div>

        @if (! auth()->user()->hasRole('doctor'))
            <div style="grid-column:1 / -1;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Doctor</label>
                <select name="doctor_id"
                    style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                    <option value="">Use appointment doctor</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ (string) $doctorId === (string) $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Diagnosis / Instructions</label>
            <textarea name="description" rows="5" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ $description }}</textarea>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Prescription Items</label>
            <div style="display:grid; gap:10px;">
                @foreach ($itemRows as $index => $item)
                    <div style="padding:12px; border:1px solid var(--border-color); border-radius:14px; background:#fff;">
                        <div style="display:grid; grid-template-columns:1.4fr 1fr 1fr 1fr 0.7fr; gap:10px;">
                            <select name="items[{{ $index }}][medicine_id]"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                                <option value="">Custom medicine</option>
                                @foreach ($medicines as $medicineOption)
                                    <option value="{{ $medicineOption->id }}" {{ (string) ($item['medicine_id'] ?? '') === (string) $medicineOption->id ? 'selected' : '' }}>
                                        {{ $medicineOption->name }} (stock: {{ $medicineOption->stock }})
                                    </option>
                                @endforeach
                            </select>

                            <input name="items[{{ $index }}][medicine_name]" value="{{ $item['medicine_name'] ?? '' }}" placeholder="Medicine name"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                            <input name="items[{{ $index }}][dosage]" value="{{ $item['dosage'] ?? '' }}" placeholder="Dosage"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                            <input name="items[{{ $index }}][frequency]" value="{{ $item['frequency'] ?? '' }}" placeholder="Frequency"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                            <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? '' }}" placeholder="Qty"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 2fr; gap:10px; margin-top:10px;">
                            <input name="items[{{ $index }}][duration]" value="{{ $item['duration'] ?? '' }}" placeholder="Duration"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                            <input name="items[{{ $index }}][instructions]" value="{{ $item['instructions'] ?? '' }}" placeholder="Special instructions"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Legacy Medicines Note</label>
            <textarea name="medicines" rows="3" placeholder="Optional free-text medicine notes"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ $medicines }}</textarea>
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:6px;">Status</label>
            <select name="status" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                @foreach ($statusOptions as $option)
                    <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>
                        {{ ucfirst($option) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
        <a href="{{ route('prescriptions.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background:var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>
