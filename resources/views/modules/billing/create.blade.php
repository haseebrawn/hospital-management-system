@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
    <div class="card">
        <div class="card-title">Create Invoice</div>
        <div class="card-subtitle">Select patient and add invoice items.</div>

        <form method="POST" action="{{ route('billing.store') }}" id="billingForm">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; font-size:12px; color: var(--text-muted); margin-bottom:6px;">Patient</label>
                    <select name="patient_id" required
                        style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:12px; font-size:13px; background:#fff;">
                        <option value="" disabled {{ old('patient_id') ? '' : 'selected' }}>Select patient</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" {{ (string) old('patient_id') === (string) $p->id ? 'selected' : '' }}>
                                #{{ $p->id }} — {{ $p->first_name }} {{ $p->last_name }} ({{ $p->contact_number }})
                            </option>
                        @endforeach
                    </select>
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
                    <table class="dash-table" style="min-width: 980px;">
                        <thead>
                            <tr>
                                <th style="width:38%;">Service</th>
                                <th style="width:18%;">Type</th>
                                <th style="width:12%;">Qty</th>
                                <th style="width:16%;">Price</th>
                                <th style="width:16%;">Line total</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @php
                                $oldItems = old('items', [
                                    ['service_name' => '', 'type' => 'other', 'quantity' => 1, 'price' => 0],
                                ]);
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
                    const service = row.querySelector('input[name^="items["][name$="[service_name]"]');
                    const type = row.querySelector('select[name^="items["][name$="[type]"]');
                    const qty = row.querySelector('input[name^="items["][name$="[quantity]"]');
                    const price = row.querySelector('input[name^="items["][name$="[price]"]');
                    if (service) service.name = `items[${index}][service_name]`;
                    if (type) type.name = `items[${index}][type]`;
                    if (qty) qty.name = `items[${index}][quantity]`;
                    if (price) price.name = `items[${index}][price]`;
                });
            }

            function addRow() {
                const index = body.querySelectorAll('.item-row').length;
                const tr = document.createElement('tr');
                tr.className = 'item-row';
                tr.innerHTML = `
                    <td>
                        <input name="items[${index}][service_name]" required
                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                    </td>
                    <td>
                        <select name="items[${index}][type]"
                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px; background:#fff;">
                            ${typeOptions.map(t => `<option value="${t}">${t.charAt(0).toUpperCase() + t.slice(1)}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" min="1" name="items[${index}][quantity]" value="1" required class="qty-input"
                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="items[${index}][price]" value="0" required class="price-input"
                            style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:10px; font-size:13px;">
                    </td>
                    <td class="line-total" style="font-weight:600;">0.00</td>
                    <td style="text-align:right;">
                        <button type="button" class="remove-btn"
                            style="font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer;">
                            Remove
                        </button>
                    </td>
                `;
                body.appendChild(tr);
                recalc();
            }

            body.addEventListener('input', function (e) {
                if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
                    recalc();
                }
            });

            body.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-btn')) {
                    const row = e.target.closest('.item-row');
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

