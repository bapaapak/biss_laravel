@extends('layouts.app')

@section('content')
    <style>
        .eval-wrapper {
            padding: 0;
        }

        /* Back button */
        .eval-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #1a3a6c;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 12px;
            transition: color 0.15s;
        }

        .eval-back-link:hover {
            color: #3b82f6;
        }

        /* Header banner */
        .eval-header {
            background: linear-gradient(135deg, #0a1628 0%, #1a3a6c 100%);
            color: #fff;
            text-align: center;
            padding: 14px 20px;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border-radius: 6px 6px 0 0;
        }

        /* Project info bar */
        .eval-info-bar {
            background: #f0f4fa;
            border: 1px solid #c8d6e5;
            border-top: none;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .eval-info-bar .info-left,
        .eval-info-bar .info-right {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .eval-info-bar .info-item {
            font-size: 0.8rem;
            color: #333;
        }

        .eval-info-bar .info-item strong {
            color: #0a1628;
            min-width: 160px;
            display: inline-block;
        }

        /* Main spreadsheet table */
        .eval-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.78rem;
            table-layout: fixed;
        }

        .eval-table th {
            background: #1a3a6c;
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 6px 5px;
            border: 1px solid #0d2347;
            white-space: nowrap;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .eval-table th.sub-header {
            background: #2a5298;
            font-size: 0.7rem;
        }

        .eval-table td {
            border: 1px solid #c8d6e5;
            padding: 4px 6px;
            vertical-align: middle;
            background: #fff;
        }

        .eval-table td.num {
            text-align: right;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.75rem;
        }

        .eval-table td.center {
            text-align: center;
        }

        /* Process group header row - folder style */
        .process-header td {
            background: #e8edf4 !important;
            font-weight: 700;
            color: #0a1628;
            font-size: 0.8rem;
            padding: 6px 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
        }

        .process-header:hover td {
            background: #dce3f0 !important;
        }

        .process-header .folder-icon {
            color: #e6a817;
            font-size: 1rem;
            margin-right: 6px;
        }

        .process-header .chevron-icon {
            font-size: 0.65rem;
            margin-right: 6px;
            transition: transform 0.25s ease;
            display: inline-block;
            color: #64748b;
        }

        .process-header.collapsed .chevron-icon {
            transform: rotate(-90deg);
        }

        .process-child.hidden-row {
            display: none;
        }

        /* Subtotal rows */
        .subtotal-row td {
            background: #f5f7fb !important;
            font-weight: 700;
            color: #1a3a6c;
            font-size: 0.78rem;
        }

        .grand-total-row td {
            background: #1a3a6c !important;
            color: #fff !important;
            font-weight: 700;
            font-size: 0.82rem;
        }

        /* Balance coloring */
        .balance-positive {
            color: #0d6832;
            font-weight: 600;
        }

        .balance-negative {
            color: #c0392b;
            font-weight: 600;
        }

        /* Textarea for notes */
        .eval-textarea {
            width: 100%;
            border: 1px solid #dce3ed;
            border-radius: 3px;
            padding: 3px 5px;
            font-size: 0.75rem;
            resize: vertical;
            min-height: 28px;
            background: #fafbfc;
        }

        .eval-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
            background: #fff;
        }

        /* Status badges */
        .status-full {
            background: #0d6832;
            color: #fff;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 0.68rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-not-full {
            background: #e67e22;
            color: #fff;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 0.68rem;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Save button */
        .eval-save-btn {
            background: #1a3a6c;
            color: #fff;
            border: none;
            padding: 8px 24px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .eval-save-btn:hover {
            background: #0d2347;
            color: #fff;
        }

        /* Column widths */
        .col-no {
            width: 30px;
        }

        .col-name {
            width: 14%;
        }

        .col-qty {
            width: 45px;
        }

        .col-price {
            width: 8%;
        }

        .col-total {
            width: 9%;
        }

        .col-balance {
            width: 8%;
        }

        .col-status {
            width: 50px;
        }

        .col-cmp {
            width: 38px;
        }

        .col-notes {
            width: 12%;
        }

        /* Checkmark style */
        .cmp-check {
            color: #0d6832;
            font-size: 0.85rem;
        }

        /* Card wrapper */
        .eval-card {
            border: 1px solid #c8d6e5;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        @media print {

            .eval-save-btn,
            .no-print {
                display: none !important;
            }

            .eval-table {
                font-size: 0.7rem;
            }
        }
    </style>

    <!-- Back Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('analysis.budget_evaluation') }}" class="eval-back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Customer
        </a>
        <a href="{{ route('analysis.evaluation.print', $plan->plan_id) }}" target="_blank"
            class="btn btn-outline-primary btn-sm rounded-pill px-4">
            <i class="fas fa-print me-2"></i>Print Out
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $grandTotalBudget = $plan->items->sum('total_amount');
        $grandTotalPR = $plan->items->sum('realized_amount');
        $grandBalance = $grandTotalBudget - $grandTotalPR;
    @endphp

    <form action="{{ route('analysis.evaluation.save') }}" method="POST">
        @csrf
        <div class="eval-card">
            <!-- Company Letterhead -->
            <div
                style="padding: 15px 25px; border-bottom: 2px solid #0a1628; display: flex; justify-content: center; align-items: center; background: #fff;">
                <img src="{{ asset('images/company_logo.svg') }}" alt="Logo" style="height: 150px;">
            </div>

            <!-- Header Banner -->
            <div class="eval-header">
                EVALUASI PEMAKAIAN BUDGET — BP-{{ $plan->fiscal_year }}-{{ str_pad($plan->plan_id, 3, '0', STR_PAD_LEFT) }}
            </div>

            <!-- Info Bar -->
            <div class="eval-info-bar">
                <div class="info-left">
                    <div class="info-item"><strong>Project Code</strong>: {{ $plan->project_code }}</div>
                    <div class="info-item"><strong>Project Name</strong>: {{ $plan->project_name }}</div>
                    @if($plan->customer_name)
                        <div class="info-item"><strong>Customer</strong>: {{ $plan->customer_name }}</div>
                    @endif
                </div>
                <div class="info-right" style="text-align: right;">
                    <div class="info-item"><strong>Fiscal Year</strong>: {{ $plan->fiscal_year }}</div>
                    <div class="info-item"><strong>Category</strong>: {{ $plan->category }}</div>
                    <div class="info-item"><strong>Created By</strong>: {{ $plan->creator }}</div>
                </div>
            </div>

            <!-- Spreadsheet Table -->
            <div class="table-responsive">
                <table class="eval-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-no">No</th>
                            <th rowspan="2" class="col-name">Name</th>
                            <th colspan="2">QTY</th>
                            <th colspan="2">PRICE / ITEM</th>
                            <th colspan="2">TOTAL AMOUNT</th>
                            <th rowspan="2" class="col-balance">BALANCE</th>
                            <th rowspan="2" class="col-status">STATUS</th>
                            <th colspan="2">QTY</th>
                            <th colspan="2">PRICE</th>
                            <th rowspan="2" class="col-notes">LAINNYA</th>
                        </tr>
                        <tr>
                            <th class="sub-header col-qty">A11</th>
                            <th class="sub-header col-qty">PR</th>
                            <th class="sub-header col-price">A11</th>
                            <th class="sub-header col-price">PR</th>
                            <th class="sub-header col-total">A11</th>
                            <th class="sub-header col-total">PR</th>
                            <th class="sub-header col-cmp">A11&gt;PR</th>
                            <th class="sub-header col-cmp">A11&lt;PR</th>
                            <th class="sub-header col-cmp">A11&gt;PR</th>
                            <th class="sub-header col-cmp">A11&lt;PR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plan->itemsByProcess as $processName => $processItems)
                            @php
                                $globalNo = 0; // Reset numbering for each group
                                $processTotalBudget = $processItems->sum('total_amount');
                                $processTotalPR = $processItems->sum('realized_amount');
                                $processBalance = $processTotalBudget - $processTotalPR;
                            @endphp
                            <!-- Process Group Header -->
                            <tr class="process-header" onclick="toggleProcessGroup(this)">
                                <td colspan="15">
                                    <i class="fas fa-chevron-down chevron-icon"></i>
                                    <i class="fas fa-folder-open folder-icon"></i>
                                    {{ strtoupper($processName) }} PROCESS
                                </td>
                            </tr>

                            @foreach($processItems as $item)
                                @php
                                    $globalNo++;
                                    $planAmt = $item->total_amount;
                                    $realAmt = $item->realized_amount;
                                    $balance = $planAmt - $realAmt;
                                    $persen = ($planAmt > 0) ? ($realAmt / $planAmt) * 100 : 0;
                                    $isFull = $persen >= 90;
                                    // Comparison flags
                                    $qtyA11gtPR = ($item->pr_qty > 0 && $item->qty > $item->pr_qty);
                                    $qtyA11ltPR = ($item->pr_qty > 0 && $item->qty < $item->pr_qty);
                                    $priceA11gtPR = ($item->pr_price > 0 && $item->estimated_price > $item->pr_price);
                                    $priceA11ltPR = ($item->pr_price > 0 && $item->estimated_price < $item->pr_price);
                                @endphp
                                <tr class="process-child">
                                    <td class="center">{{ $globalNo }}</td>
                                    <td>
                                        <div style="font-weight:600;">{{ $item->item_name }}</div>
                                        @if($item->cc_code ?? $item->cc_id)
                                            <div style="font-size:.68rem;color:#888;">CC: {{ $item->cc_code ?? $item->cc_id }}</div>
                                        @endif
                                        <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                    </td>
                                    <td class="num">{{ number_format($item->qty, 0) }}</td>
                                    <td class="num">{{ $item->pr_qty > 0 ? number_format($item->pr_qty, 0) : '-' }}</td>
                                    <td class="num">{{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                                    <td class="num">{{ $item->pr_price ? number_format($item->pr_price, 0, ',', '.') : '-' }}</td>
                                    <td class="num">{{ number_format($planAmt, 0, ',', '.') }}</td>
                                    <td class="num">{{ $realAmt > 0 ? number_format($realAmt, 0, ',', '.') : '-' }}</td>
                                    <td class="num {{ $balance >= 0 ? 'balance-positive' : 'balance-negative' }}">
                                        {{ number_format($balance, 0, ',', '.') }}
                                    </td>
                                    <td class="center">
                                        @if($realAmt > 0)
                                            <span class="{{ $isFull ? 'status-full' : 'status-not-full' }}">
                                                {{ $isFull ? 'Full' : round($persen) . '%' }}
                                            </span>
                                        @else
                                            <span style="color:#999;font-size:.7rem;">—</span>
                                        @endif
                                    </td>
                                    <td class="center">@if($qtyA11gtPR)<span class="cmp-check">✓</span>@endif</td>
                                    <td class="center">@if($qtyA11ltPR)<span class="cmp-check">✓</span>@endif</td>
                                    <td class="center">@if($priceA11gtPR)<span class="cmp-check">✓</span>@endif</td>
                                    <td class="center">@if($priceA11ltPR)<span class="cmp-check">✓</span>@endif</td>
                                    <td>
                                        <textarea name="reason[]" class="eval-textarea" rows="1"
                                            placeholder="Catatan...">{{ $item->evaluation_reason }}</textarea>
                                        <input type="hidden" name="obstacle[]" value="{{ $item->evaluation_obstacle }}">
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Process Subtotal -->
                            <tr class="subtotal-row process-child">
                                <td colspan="6" style="text-align:right;padding-right:10px;">
                                    Subtotal Item {{ $processName }}
                                </td>
                                <td class="num">{{ number_format($processTotalBudget, 0, ',', '.') }}</td>
                                <td class="num">{{ $processTotalPR > 0 ? number_format($processTotalPR, 0, ',', '.') : '-' }}
                                </td>
                                <td class="num {{ $processBalance >= 0 ? 'balance-positive' : 'balance-negative' }}">
                                    {{ number_format($processBalance, 0, ',', '.') }}
                                </td>
                                <td colspan="6"></td>
                            </tr>
                        @endforeach

                        <!-- Grand Total -->
                        <tr class="grand-total-row">
                            <td colspan="6"
                                style="text-align:right;padding-right:10px;text-transform:uppercase;letter-spacing:1px;">
                                SUB TOTAL MACHINE
                            </td>
                            <td class="num">{{ number_format($grandTotalBudget, 0, ',', '.') }}</td>
                            <td class="num">{{ $grandTotalPR > 0 ? number_format($grandTotalPR, 0, ',', '.') : '-' }}</td>
                            <td class="num">{{ number_format($grandBalance, 0, ',', '.') }}</td>
                            <td colspan="6"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Save Button -->
            <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #c8d6e5; text-align: right;">
                <button type="submit" class="eval-save-btn no-print">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </form>

    <script>
        function toggleProcessGroup(headerRow) {
            const isCollapsed = headerRow.classList.toggle('collapsed');
            const folderIcon = headerRow.querySelector('.folder-icon');

            if (isCollapsed) {
                folderIcon.classList.remove('fa-folder-open');
                folderIcon.classList.add('fa-folder');
            } else {
                folderIcon.classList.remove('fa-folder');
                folderIcon.classList.add('fa-folder-open');
            }

            let nextRow = headerRow.nextElementSibling;
            while (nextRow && !nextRow.classList.contains('process-header') && !nextRow.classList.contains('grand-total-row')) {
                if (isCollapsed) {
                    nextRow.classList.add('hidden-row');
                } else {
                    nextRow.classList.remove('hidden-row');
                }
                nextRow = nextRow.nextElementSibling;
            }
        }
    </script>
@endsection