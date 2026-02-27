@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Edit Purchase Order</h3>
        <p class="text-muted mb-0">Modify your Draft PO #{{ $po->po_number }}</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('po.update', $po->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label" style="font-size: 0.85rem;"><strong>PO Date *</strong></label>
                        <input type="date" name="po_date" class="form-control"
                            value="{{ \Carbon\Carbon::parse($po->po_date)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size: 0.85rem;"><strong>Vendor *</strong></label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="">-- Choose Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $po->vendor_id == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->vendor_code }} - {{ $vendor->vendor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size: 0.85rem;"><strong>Exp. Delivery Date</strong></label>
                        <input type="date" name="expected_delivery_date" class="form-control"
                            value="{{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" style="font-size: 0.85rem;"><strong>Payment Terms</strong></label>
                        <input type="text" name="payment_terms" class="form-control" value="{{ $po->payment_terms }}">
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold border-bottom pb-2">Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0" id="itemsTable">
                            <thead class="bg-light text-muted small text-center">
                                <tr>
                                    <th style="width: 20%">Description *</th>
                                    <th style="width: 10%">Qty *</th>
                                    <th style="width: 10%">UoM</th>
                                    <th style="width: 15%">Unit Price *</th>
                                    <th style="width: 10%">Tax (%)</th>
                                    <th style="width: 20%">Total</th>
                                    <th>Notes/PR Reference</th>
                                    <th><button type="button" class="btn btn-sm btn-outline-success border-0 px-2"
                                            onclick="addRow()"><i class="fas fa-plus"></i></button></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @foreach($po->items as $idx => $item)
                                    <tr class="item-row">
                                        <td>
                                            <input type="hidden" name="items[{{ $idx }}][pr_item_id]"
                                                value="{{ $item->pr_item_id }}">
                                            <input type="text" class="form-control form-control-sm"
                                                name="items[{{ $idx }}][item_description]" value="{{ $item->item_description }}"
                                                required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01"
                                                class="form-control form-control-sm item-qty text-end"
                                                name="items[{{ $idx }}][qty]" value="{{ $item->qty }}" oninput="calcTotals()"
                                                required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                name="items[{{ $idx }}][uom]" value="{{ $item->uom }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01"
                                                class="form-control form-control-sm item-price text-end"
                                                name="items[{{ $idx }}][unit_price]" value="{{ $item->unit_price }}"
                                                oninput="calcTotals()" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.1"
                                                class="form-control form-control-sm item-tax text-end"
                                                name="items[{{ $idx }}][tax_percent]" value="{{ $item->tax_percent }}"
                                                oninput="calcTotals()">
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm item-total bg-light text-end fw-bold"
                                                readonly value="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                name="items[{{ $idx }}][notes]" value="{{ $item->notes }}">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                                onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold pt-3 pb-1 border-top"
                                        style="font-size: 0.85rem">Subtotal</td>
                                    <td class="pt-3 pb-1 border-top"><input type="text" id="grandSubtotal"
                                            class="form-control form-control-sm text-end fw-bold bg-light" readonly></td>
                                    <td colspan="2" class="border-top"></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold py-1 border-0" style="font-size: 0.85rem">Tax
                                        Amount</td>
                                    <td class="py-1 border-0"><input type="text" id="grandTax"
                                            class="form-control form-control-sm text-end fw-bold bg-light" readonly></td>
                                    <td colspan="2" class="border-0"></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold pb-3 pt-1 border-0 text-primary"
                                        style="font-size: 1rem">Grand Total</td>
                                    <td class="pb-3 pt-1 border-0"><input type="text" id="grandTotal"
                                            class="form-control form-control-sm text-end fw-bold bg-indigo text-primary"
                                            style="background-color:#e0e7ff;" readonly></td>
                                    <td colspan="2" class="border-0"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Delivery Terms</label>
                        <input type="text" name="delivery_terms" class="form-control" value="{{ $po->delivery_terms }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Plant/Delivery Address Info</label>
                        <input type="text" name="plant" class="form-control" value="{{ $po->plant }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $po->notes }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('po.show', $po->id) }}" class="btn btn-light px-4 border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Update Draft</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let rowIndex = {{ count($po->items) + 1 }};

        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm" name="items[${rowIndex}][item_description]" required></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm item-qty text-end" name="items[${rowIndex}][qty]" value="1" oninput="calcTotals()" required></td>
                <td><input type="text" class="form-control form-control-sm" name="items[${rowIndex}][uom]"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm item-price text-end" name="items[${rowIndex}][unit_price]" value="0" oninput="calcTotals()" required></td>
                <td><input type="number" step="0.1" class="form-control form-control-sm item-tax text-end" name="items[${rowIndex}][tax_percent]" value="0" oninput="calcTotals()"></td>
                <td><input type="text" class="form-control form-control-sm item-total bg-light text-end fw-bold" readonly value="0"></td>
                <td><input type="text" class="form-control form-control-sm" name="items[${rowIndex}][notes]"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
            `;
            tbody.appendChild(tr);
            rowIndex++;
            calcTotals();
        }

        function removeRow(btn) {
            if (document.querySelectorAll('.item-row').length > 1) {
                btn.closest('tr').remove();
                calcTotals();
            } else {
                alert('At least one item is required.');
            }
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
        }

        function calcTotals() {
            let grandSubSum = 0;
            let grandTaxSum = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                let q = parseFloat(row.querySelector('.item-qty').value) || 0;
                let p = parseFloat(row.querySelector('.item-price').value) || 0;
                let t = parseFloat(row.querySelector('.item-tax').value) || 0;

                let rowTotal = q * p;
                let rowTax = rowTotal * (t / 100);

                row.querySelector('.item-total').value = formatNumber(rowTotal);

                grandSubSum += rowTotal;
                grandTaxSum += rowTax;
            });

            document.getElementById('grandSubtotal').value = formatNumber(grandSubSum);
            document.getElementById('grandTax').value = formatNumber(grandTaxSum);
            document.getElementById('grandTotal').value = formatNumber(grandSubSum + grandTaxSum);
        }

        // Init calculation on page load
        document.addEventListener('DOMContentLoaded', calcTotals);
    </script>
@endpush