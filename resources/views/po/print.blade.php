<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Print PO - {{ $po->po_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        .table th {
            background-color: #f8f9fa;
        }

        @media print {
            .invoice-box {
                box-shadow: none;
                border: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="container my-5 invoice-box">
        <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
            <div>
                <h2 class="mb-0 fw-bold">PURCHASE ORDER</h2>
                <div class="text-muted">PO #{{ $po->po_number }}</div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0">PT. BISS Company Name</h5>
                <small class="text-muted">Jl. Company Address No. 12<br>Jakarta, Indonesia<br>Phone: (021)
                    12345678</small>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6">
                <div class="text-muted small fw-bold mb-1">VENDOR / SUPPLIER</div>
                @if($po->vendor)
                    <div class="fw-bold fs-6">{{ $po->vendor->vendor_name }}</div>
                    <div>{{ nl2br(e($po->vendor->address)) }}</div>
                    <div>Attn: {{ $po->vendor->contact_person ?? '-' }}</div>
                    <div>{{ $po->vendor->phone ?? '-' }}</div>
                @else
                    <div class="text-danger">Vendor Details Missing</div>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <div class="text-muted small fw-bold mb-1">SHIPPING/DELIVERY INFO</div>
                <div><strong>PO Date:</strong> {{ \Carbon\Carbon::parse($po->po_date)->format('d F Y') }}</div>
                <div><strong>Expected Delivery:</strong>
                    {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d F Y') : 'TBA' }}
                </div>
                <div><strong>Terms of Payment:</strong>
                    {{ $po->payment_terms ?? $po->vendor->terms_of_payment ?? 'N/A' }}</div>
                <div><strong>Delivery Terms:</strong> {{ $po->delivery_terms ?? 'N/A' }}</div>
            </div>
        </div>

        <table class="table table-bordered table-sm align-middle mt-4">
            <thead class="text-center">
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 40%">Description</th>
                    <th style="width: 10%">Qty</th>
                    <th style="width: 10%">UoM</th>
                    <th style="width: 15%">Unit Price (Rp)</th>
                    <th style="width: 20%">Amount (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($po->items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $item->item_description }}</div>
                            @if($item->notes)
                            <div class="small text-muted">{{ $item->notes }}</div> @endif
                        </td>
                        <td class="text-center">{{ number_format($item->qty, 2, ',', '.') }}</td>
                        <td class="text-center">{{ $item->uom }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->total_price, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if($po->tax_amount > 0)
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Subtotal</td>
                        <td class="text-end fw-bold">Rp {{ number_format($po->subtotal, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Tax Amount</td>
                        <td class="text-end fw-bold">Rp {{ number_format($po->tax_amount, 2, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="table-group-divider">
                    <td colspan="5" class="text-end fw-bold fs-6">GRAND TOTAL</td>
                    <td class="text-end fw-bold fs-6">Rp {{ number_format($po->total_amount, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-5">
            <div class="col-8">
                <p class="mb-1 text-muted small fw-bold">Additional Notes / Instructions:</p>
                <div class="small border p-2 rounded" style="min-height: 80px;">
                    {{ $po->notes ? nl2br(e($po->notes)) : 'None' }}
                    @if($po->plant)
                        <br><strong>Location:</strong> {{ $po->plant }}
                    @endif
                </div>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5 small fw-bold">Authorized By,</p>
                <div style="height: 60px;">
                    @if($po->status === 'Approved')
                        <span class="text-success badge border border-success px-3 py-2"
                            style="font-size:14px; transform: rotate(-10deg);">APPROVED</span>
                    @endif
                </div>
                <p class="mb-0 fw-bold border-top pt-2">{{ $po->creator->full_name ?? 'Management' }}</p>
                <p class="small text-muted mb-0">Purchasing Department</p>
            </div>
        </div>
    </div>

    <div class="text-center no-print mt-4 mb-5">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i> Print
            Document</button>
    </div>

</body>

</html>