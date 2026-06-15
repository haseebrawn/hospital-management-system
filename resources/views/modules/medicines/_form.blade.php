@props([
    'medicine' => null,
    'statusOptions' => ['available', 'unavailable'],
    'action' => '#',
    'method' => 'POST',
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @php
        $name = old('name', $medicine?->name);
        $description = old('description', $medicine?->description);
        $stock = old('stock', $medicine?->stock ?? 0);
        $reorderLevel = old('reorder_level', $medicine?->reorder_level ?? 10);
        $price = old('price', $medicine?->price);
        $expiryDate = old('expiry_date', $medicine?->expiry_date);
        $status = old('status', $medicine?->status ?? 'available');
    @endphp

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Name</label>
            <input name="name" value="{{ $name }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Stock</label>
            <input type="number" min="0" name="stock" value="{{ $stock }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Reorder level</label>
            <input type="number" min="0" name="reorder_level" value="{{ $reorderLevel }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Price</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ $price }}" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Expiry date (optional)</label>
            <input type="date" name="expiry_date" value="{{ $expiryDate }}"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px;">
        </div>

        <div>
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Status</label>
            <select name="status" required
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                @foreach ($statusOptions as $opt)
                    <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>
                        {{ ucfirst($opt) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Description (optional)</label>
            <textarea name="description" rows="4"
                style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; resize:vertical;">{{ $description }}</textarea>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
        <a href="{{ route('medicines.index') }}"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
            Cancel
        </a>
        <button type="submit"
            style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
            Save
        </button>
    </div>
</form>
