@extends('layouts.app')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">Purchase Order Detail</h3>
            <p class="text-muted small mb-0">{{ $po->po_number }} • {{ $po->status }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('po.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>
                Back</a>

            @if($po->status === 'Draft')
                <a href="{{ route('po.edit', $po->id) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Edit PO</a>
                <a href="{{ route('po.approve', $po->po_number) }}" class="btn btn-success"
                    onclick="return confirm('Proses PO ke status Approved?')"><i class="fas fa-check me-1"></i> Mark as
                    Approved</a>
            @endif

            @if($po->status === 'Approved')
                <a href="{{ route('po.print', $po->po_number) }}" target="_blank" class="btn btn-outline-primary"><i
                        class="fas fa-print me-1"></i> Print / Download PDF</a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Details -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0"><i class="fas fa-info-circle text-primary me-2"></i> Document Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted w-50">PO Number</td>
                            <td class="fw-bold">{{ $po->po_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">PO Date</td>
                            <td class="fw-bold">{{ \Carbon\Carbon::parse($po->po_date)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By</td>
                            <td class="fw-bold">{{ $po->creator->full_name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Exp. Delivery</td>
                            <td class="fw-bold">
                                {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d F Y') : '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0"><i class="fas fa-building text-primary me-2"></i> Vendor Details</h6>
                </div>
                <div class="card-body">
                    @if($po->vendor)
                        <div class="fw-bold mb-1">{{ $po->vendor->vendor_name }} ({{ $po->vendor->vendor_code }})</div>
                        <div class="small text-muted mb-2">{{ $po->vendor->address ?? 'No address' }}</div>

                        <table class="table table-sm table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted w-50">Contact</td>
                                <td class="fw-bold">{{ $po->vendor->contact_person ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone</td>
                                <td class="fw-bold">{{ $po->vendor->phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td class="fw-bold">{{ $po->vendor->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Terms</td>
                                <td class="fw-bold">{{ $po->payment_terms ?? $po->vendor->terms_of_payment ?? '-' }}</td>
                            </tr>
                        </table>
                    @else
                        <div class="text-danger">Vendor not found or deleted.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Items and Totals -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0"><i class="fas fa-box-open text-primary me-2"></i> Ordered Items</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th class="text-end">Unit Price (Rp)</th>
                                <th class="text-end">Tax Req</th>
                                <th class="text-end pe-4">Total (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $idx => $item)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $item->item_description }}</div>
                                        @if($item->prItem)
                                            <div class="small text-primary"><i class="fas fa-link"></i> Linked to PR</div>
                                        @endif
                                        @if($item->notes)
                                            <div class="small text-muted">{{ $item->notes }}</div>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->qty, 2, ',', '.') }} {{ $item->uom }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($item->tax_percent, 1, ',', '.') }}%</td>
                                    <td class="text-end pe-4 fw-bold">{{ number_format($item->total_price, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top-0 d-flex justify-content-end py-4">
                    <div style="width: 350px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal Base</span>
                            <span class="fw-bold">Rp {{ number_format($po->subtotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Tax Summary</span>
                            <span class="fw-bold">Rp {{ number_format($po->tax_amount, 2, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold text-primary fs-5">Grand Total</span>
                            <span class="fw-bold text-primary fs-5">Rp
                                {{ number_format($po->total_amount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($po->notes || $po->plant || $po->delivery_terms)
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold"><i class="fas fa-clipboard-list text-primary me-2"></i> Additional Instructions</h6>
                        <div class="row mt-3">
                            @if($po->delivery_terms)
                                <div class="col-md-6 mb-2"><strong>Delivery Terms:</strong> {{ $po->delivery_terms }}</div>
                            @endif
                            @if($po->plant)
                                <div class="col-md-6 mb-2"><strong>Plant/Location:</strong> {{ $po->plant }}</div>
                            @endif
                            @if($po->notes)
                                <div class="col-12 mt-2"><strong>Notes:</strong><br>{{ nl2br(e($po->notes)) }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection