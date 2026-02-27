@extends('layouts.app')

@php
// Helper function to format currency with jt (million) and M (billion)
function formatRupiah($amount) {
    if ($amount >= 1000000000) {
        // Billions - use M (Milyar)
        return 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
    } elseif ($amount >= 1000000) {
        // Millions - use jt (juta)
        return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' jt';
    } else {
        // Below million - show full number
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
@endphp

@section('content')
<style>
    .card-stat { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .text-label { font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .text-value { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-top: 5px; }
    .table-custom thead th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem; }
    .table-custom tbody td { padding: 1rem; vertical-align: middle; font-size: 0.9rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
    .btn-expand { background: none; border: none; color: #94a3b8; transition: 0.3s; }
    .btn-expand.active { transform: rotate(180deg); color: #3b82f6; }
    .expanded-row { background-color: #f8fafc; }
    .detail-table th { font-size: 0.75rem; color: #64748b; }
    .detail-table td { font-size: 0.85rem; }
    .progress-custom { height: 8px; border-radius: 4px; background-color: #e2e8f0; }
    .cursor-pointer { cursor: pointer; }

    @media print {
        .no-print, .btn, .d-flex.gap-2 { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        body { background: white; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Budget Realization Report</h4>
        <p class="text-muted mb-0 small">Detailed breakdown of Plan vs. Actual Spending.</p>
    </div>
    <!-- Buttons removed as requested -->
</div>

<!-- ... Stats Rows ... -->
<!-- (Omitted for brevity, assuming standard view logic remains) -->

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom mb-0 table-hover" id="plan-realization-table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="no-print"></th>
                    <th>IO Number</th>
                    <th>Cost Center</th>
                    <th>Machine / Asset</th>
                    <th>Project</th>
                    <th class="text-end">Plan Amount</th>
                    <th class="text-end">Realized</th>
                    <th class="text-end">Balance</th>
                    <th class="text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemsWithPRs as $item)
                @php
                    $plan = $item->total_amount;
                    $real = $item->realized_amount;
                    $bal = $plan - $real;
                    $pct = ($plan > 0) ? ($real / $plan) * 100 : 0;
                    
                    $badgeBg = 'bg-success';
                    if($pct > 90) $badgeBg = 'bg-danger';
                    elseif($pct > 70) $badgeBg = 'bg-warning';
                    
                    $hasPr = count($item->prs) > 0;
                @endphp
                
                <tr class="{{ $hasPr ? 'cursor-pointer' : '' }}" 
                    @if($hasPr) onclick="toggleRow('details-{{ $item->id }}', this)" @endif>
                    <td class="text-center no-print">
                        @if($hasPr)
                        <button class="btn-expand"><i class="fas fa-chevron-right"></i></button>
                        @endif
                    </td>
                    <td class="font-monospace fw-bold text-secondary">{{ $item->io_number }}</td>
                    <td>{{ $item->cc_code }}</td>
                    <td class="fw-bold text-dark">{{ $item->item_name }}</td>
                    <td class="text-muted small">{{ $item->project_name }}</td>
                    <td class="text-end text-dark">Rp {{ number_format($plan, 0, ',', '.') }}</td>
                    <td class="text-end text-primary fw-bold">Rp {{ number_format($real, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($bal, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeBg }} text-white" style="padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem;">{{ round($pct) }}%</span>
                    </td>
                </tr>

                @if($hasPr)
                <tr id="details-{{ $item->id }}" style="display: none;" class="expanded-row no-print">
                    <td colspan="9" class="p-4 border-bottom">
                        <h6 class="text-secondary small fw-bold text-uppercase mb-3 ps-2 border-start border-3 border-primary">
                            Associated Purchase Requests
                        </h6>
                        <table class="table table-sm table-borderless detail-table mb-0 bg-white rounded shadow-sm">
                            <thead class="border-bottom">
                                <tr>
                                    <th class="ps-3">PR No</th>
                                    <th>Date</th>
                                    <th>Item Description (Notes)</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->prs as $pr)
                                @php
                                    $totalPr = $pr->qty_req * $pr->estimated_price;
                                    $st = $pr->status;
                                    $cls = 'success';
                                    if($st == 'Submitted') $cls = 'primary';
                                    if($st == 'Rejected') $cls = 'danger';
                                    if($st == 'On Process') $cls = 'warning';
                                @endphp
                                <tr>
                                    <td class="ps-3 text-primary fw-bold">{{ $pr->pr_number }}</td>
                                    <td>{{ date('Y-m-d', strtotime($pr->request_date)) }}</td>
                                    <td>{{ $pr->notes ?: '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $cls }} text-white" style="padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem;">
                                            {{ $st }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold">Rp {{ number_format($totalPr, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($itemsWithPRs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top no-print">
            <div class="text-muted small">
                Showing {{ $itemsWithPRs->firstItem() }} to {{ $itemsWithPRs->lastItem() }} of {{ $itemsWithPRs->total() }} items
            </div>
            <div>
                {{ $itemsWithPRs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleRow(rowId, trElement) {
    const detailRow = document.getElementById(rowId);
    if (!detailRow) return;

    if (detailRow.style.display === "none") {
        detailRow.style.display = "table-row";
    } else {
        detailRow.style.display = "none";
    }

    const btn = trElement.querySelector('.btn-expand');
    if(btn) {
        btn.classList.toggle('active');
        if (detailRow.style.display === "table-row") {
            btn.innerHTML = '<i class="fas fa-chevron-down"></i>';
        } else {
            btn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        }
    }
}

function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // Specify file name
    filename = filename?filename+'.xls':'excel_data.xls';
    
    // Create download link element
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
    
        // Setting the file name
        downloadLink.download = filename;
        
        //triggering the function
        downloadLink.click();
    }
}
</script>
@endpush
