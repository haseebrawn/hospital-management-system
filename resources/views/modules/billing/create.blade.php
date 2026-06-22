@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
    <div class="card">
        <div class="card-title">Create Invoice</div>
        <div class="card-subtitle">Select patient and add invoice items. Linked appointment context is shown when available.</div>

        @if (! empty($linkedSource))
            <div style="margin-top:16px; padding:14px; border:1px solid rgba(37,99,235,0.18); border-radius:14px; background:rgba(37,99,235,0.05);">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">{{ $linkedSource['title'] }}</div>
                <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px;">
                    <div>
                        <div style="font-size:12px; color:var(--text-muted);">Type / ID</div>
                        <div style="font-weight:700;">{{ ucfirst($linkedSource['type']) }} #{{ $linkedSource['id'] }}</div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-muted);">Patient</div>
                        <div style="font-weight:700;">{{ $linkedSource['patient_name'] }}</div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-muted);">Doctor</div>
                        <div style="font-weight:700;">{{ $linkedSource['doctor_name'] }}</div>
                    </div>
                </div>
                <div style="margin-top:12px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <div style="font-size:12px; color:var(--text-muted);">Reason</div>
                        <div style="font-weight:600;">{{ $linkedSource['reason'] }}</div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-muted);">Notes</div>
                        <div style="font-weight:600;">{{ $linkedSource['notes'] }}</div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('billing.store') }}" id="billingForm">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Patient</label>
                    <select name="patient_id" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        <option value="" disabled {{ old('patient_id', $prefilledPatientId ?? null) ? '' : 'selected' }}>Select patient</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" {{ (string) old('patient_id', $prefilledPatientId ?? null) === (string) $p->id ? 'selected' : '' }}>
                                {{ $p->mrn ?? ('#' . $p->id) }} - {{ $p->first_name }} {{ $p->last_name }} ({{ $p->contact_number }})
                            </option>
                        @endforeach
                    </select>
                    <div style="margin-top:6px; font-size:12px; color:var(--text-muted);">
                        The selected patient should match the linked source record when available.
                    </div>
                </div>
            </div>

            <div style="margin-top: 16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap: 10px; flex-wrap:wrap;">
                    <div style="font-weight:700;">Items</div>
                    <button type="button" id="addItemBtn"
                        style="padding:8px 12px; border-radius:10px; border:1px solid var(--border-color); background:#fff; cursor:pointer;">
                        + Add Item
                    </button>
                </div>

                <div style="margin-top: 10px; overflow:auto;">
                    <table class="dash-table" style="min-width: 1220px;">
                        <thead>
                            <tr>
                                <th style="width:24%;">Service</th>
                                <th style="width:12%;">Type</th>
                                <th style="width:10%;">Qty</th>
                                <th style="width:12%;">Price</th>
                                <th style="width:14%;">Source Type</th>
                                <th style="width:12%;">Source ID</th>
                                <th style="width:14%;">Source Name</th>
                                <th style="width:12%;">Line total</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @php
                                $oldItems = $defaultItems;
                            @endphp

                            @foreach ($oldItems as $i => $item)
                                <tr class="item-row">
                                    <td>
                                        <input name="items[{{ $i }}][service_name]" value="{{ $item['service_name'] ?? '' }}" required
                                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                                    </td>
                                    <td>
                                        <select name="items[{{ $i }}][type]"
                                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                                            @foreach ($typeOptions as $t)
                                                <option value="{{ $t }}" {{ ($item['type'] ?? 'other') === $t ? 'selected' : '' }}>
                                                    {{ ucfirst($t) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" min="1" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required
                                            class="qty-input"
                                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $i }}][price]" value="{{ $item['price'] ?? 0 }}" required
                                            class="price-input"
                                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                                    </td>
                                    <td>
                                        <select name="items[{{ $i }}][source_type]"
                                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                                            <option value="">None</option>
                                            @foreach ($sourceTypeOptions as $sourceType)
                                                <option value="{{ $sourceType }}" {{ ($item['source_type'] ?? '') === $sourceType ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $sourceType)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" min="1" name="items[{{ $i }}][source_id]" value="{{ $item['source_id'] ?? '' }}"
                                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                                    </td>
                                    <td>
                                        <input name="items[{{ $i }}][source_name]" value="{{ $item['source_name'] ?? '' }}"
                                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                                    </td>
                                    <td class="line-total" style="font-weight:600;">0.00</td>
                                    <td style="text-align:right;">
                                        <button type="button" class="remove-btn"
                                            style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top: 10px; font-size:14px;">
                    <div style="font-weight:800;">
                        Total: <span id="grandTotal">0.00</span>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 16px;">
                <a href="{{ route('billing.index') }}"
                    style="padding:10px 12px; border-radius:12px; border:1px solid var(--border-color); background:#fff; text-decoration:none; color:inherit; font-size:13px;">
                    Cancel
                </a>
                <button type="submit"
                    style="padding:10px 14px; border-radius:12px; border:none; background: var(--primary); color:#fff; cursor:pointer; font-size:13px;">
                    Create Invoice
                </button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const body = document.getElementById('itemsBody');
            const addBtn = document.getElementById('addItemBtn');
            const grandTotalEl = document.getElementById('grandTotal');
            const typeOptions = @json($typeOptions);
            const sourceTypeOptions = @json($sourceTypeOptions);

            function recalc() {
                let grand = 0;
                const rows = body.querySelectorAll('.item-row');
                rows.forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty-input')?.value || '0');
                    const price = parseFloat(row.querySelector('.price-input')?.value || '0');
                    const total = qty * price;
                    grand += total;
                    const line = row.querySelector('.line-total');
                    if (line) line.textContent = total.toFixed(2);
                });
                grandTotalEl.textContent = grand.toFixed(2);
            }

            function reindex() {
                const rows = body.querySelectorAll('.item-row');
                rows.forEach((row, index) => {
                    const fields = ['service_name', 'type', 'quantity', 'price', 'source_type', 'source_id', 'source_name'];
                    fields.forEach((field) => {
                        const control = row.querySelector(`[name^="items["][name$="[${field}]"]`);
                        if (control) {
                            control.name = `items[${index}][${field}]`;
                        }
                    });
                });
            }

            function addRow() {
                const index = body.querySelectorAll('.item-row').length;
                const tr = document.createElement('tr');
                tr.className = 'item-row';
                tr.innerHTML = `
                    <td><input name="items[${index}][service_name]" required style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;"></td>
                    <td>
                        <select name="items[${index}][type]" style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                            ${typeOptions.map(t => `<option value="${t}">${t.charAt(0).toUpperCase() + t.slice(1)}</option>`).join('')}
                        </select>
                    </td>
                    <td><input type="number" min="1" name="items[${index}][quantity]" value="1" required class="qty-input" style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;"></td>
                    <td><input type="number" step="0.01" min="0" name="items[${index}][price]" value="0" required class="price-input" style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;"></td>
                    <td>
                        <select name="items[${index}][source_type]" style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                            <option value="">None</option>
                            ${sourceTypeOptions.map(t => `<option value="${t}">${t.replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase())}</option>`).join('')}
                        </select>
                    </td>
                    <td><input type="number" min="1" name="items[${index}][source_id]" style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;"></td>
                    <td><input name="items[${index}][source_name]" style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;"></td>
                    <td class="line-total" style="font-weight:600;">0.00</td>
                    <td style="text-align:right;"><button type="button" class="remove-btn" style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">Remove</button></td>
                `;
                body.appendChild(tr);
                recalc();
            }

            body.addEventListener('input', function (event) {
                if (event.target.classList.contains('qty-input') || event.target.classList.contains('price-input')) {
                    recalc();
                }
            });

            body.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-btn')) {
                    const row = event.target.closest('.item-row');
                    if (!row) return;
                    row.remove();
                    reindex();
                    recalc();
                }
            });

            addBtn.addEventListener('click', addRow);
            recalc();
        })();
    </script>
@endsection
