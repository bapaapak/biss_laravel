<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluasi Budget - BP-{{ $plan->fiscal_year }}-{{ str_pad($plan->plan_id, 3, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm;
        }
        @media print {
            body { background: none !important; margin: 0 !important; }
            .page-container {
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                padding: 0 !important;
            }
            .no-print { display: none !important; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 4.8pt;
            margin: 0;
            padding: 0;
            background-color: #525659;
            color: black;
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }
        .page-container {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 8mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid black;
            padding: 2px 4px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        
        /* Header Banner */
        .eval-header {
            background: #1a3a6c !important;
            color: #fff !important;
            text-align: center;
            padding: 8px;
            font-size: 10pt;
            font-weight: 700;
            border: 1px solid black;
            text-transform: uppercase;
        }

        .info-table td {
            border: none;
            padding: 1px 5px;
        }

        thead th {
            background-color: #1a3a6c !important;
            color: white !important;
            text-align: center;
            font-weight: bold;
            font-size: 4.5pt;
            border: 1px solid black;
        }
        
        .sub-header {
            background-color: #2a5298 !important;
        }

        .process-header td {
            background-color: #e8edf4 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 5pt;
        }

        .subtotal-row td {
            background-color: #f5f7fb !important;
            font-weight: bold;
        }

        .grand-total-row td {
            background-color: #1a3a6c !important;
            color: white !important;
            font-weight: bold;
        }

        .num {
            text-align: right;
            font-family: Arial, sans-serif;
        }

        .currency-cell {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .logo-container {
            text-align: center;
            padding: 10px;
            border-bottom: 2px solid black;
            margin-bottom: 10px;
        }
        .logo-container img {
            height: 60px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="no-print" style="margin-bottom: 10px; text-align: right;">
            <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer;">Print</button>
            <button onclick="window.close()" style="padding: 5px 15px; cursor: pointer;">Close</button>
        </div>

        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ asset('images/Update Logo DEM 2022 V2.png') }}" alt="Logo">
        </div>

        <!-- Title Header -->
        <div class="eval-header">
            EVALUASI PEMAKAIAN BUDGET — BP-{{ $plan->fiscal_year }}-{{ str_pad($plan->plan_id, 3, '0', STR_PAD_LEFT) }}
        </div>

        <!-- Info Bar -->
        <table class="info-table" style="margin: 10px 0; border: 1px solid black;">
            <tr>
                <td style="width: 15%; font-weight: bold;">Project Code</td>
                <td style="width: 35%;">: {{ $plan->project_code }}</td>
                <td style="width: 15%; font-weight: bold;">Fiscal Year</td>
                <td style="width: 35%;">: {{ $plan->fiscal_year }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Project Name</td>
                <td>: {{ $plan->project_name }}</td>
                <td style="font-weight: bold;">Category</td>
                <td>: {{ $plan->category }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Customer</td>
                <td>: {{ $plan->customer_name }}</td>
                <td style="font-weight: bold;">Created By</td>
                <td>: {{ $plan->creator }}</td>
            </tr>
        </table>

        <!-- Main Table -->
        <table>
            <colgroup>
                <col style="width: 2%;">   <!-- No -->
                <col style="width: 14%;">  <!-- Name -->
                <col style="width: 4%;">   <!-- Qty Budget -->
                <col style="width: 4%;">   <!-- Qty PR -->
                <col style="width: 8%;">   <!-- Price Budget -->
                <col style="width: 8%;">   <!-- Price PR -->
                <col style="width: 9%;">   <!-- Total Budget -->
                <col style="width: 9%;">   <!-- Total PR -->
                <col style="width: 9%;">   <!-- Balance -->
                <col style="width: 5%;">   <!-- Status -->
                <col style="width: 4%;">   <!-- A11>PR Q -->
                <col style="width: 4%;">   <!-- A11<PR Q -->
                <col style="width: 4%;">   <!-- A11>PR P -->
                <col style="width: 4%;">   <!-- A11<PR P -->
                <col style="width: 12%;">  <!-- Notes -->
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">NAME</th>
                    <th colspan="2">QTY</th>
                    <th colspan="2">PRICE / ITEM</th>
                    <th colspan="2">TOTAL AMOUNT</th>
                    <th rowspan="2">BALANCE</th>
                    <th rowspan="2">STATUS</th>
                    <th colspan="2">QTY</th>
                    <th colspan="2">PRICE</th>
                    <th rowspan="2">LAINNYA</th>
                </tr>
                <tr>
                    <th class="sub-header">A11</th>
                    <th class="sub-header">PR</th>
                    <th class="sub-header">A11</th>
                    <th class="sub-header">PR</th>
                    <th class="sub-header">A11</th>
                    <th class="sub-header">PR</th>
                    <th class="sub-header">A11>PR</th>
                    <th class="sub-header">A11<PR</th>
                    <th class="sub-header">A11>PR</th>
                    <th class="sub-header">A11<PR</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $grandTotalBudget = 0;
                    $grandTotalPR = 0;
                @endphp
                @foreach($plan->itemsByProcess as $processName => $processItems)
                    @php
                        $processTotalBudget = $processItems->sum('total_amount');
                        $processTotalPR = $processItems->sum('realized_amount');
                        $grandTotalBudget += $processTotalBudget;
                        $grandTotalPR += $processTotalPR;
                        $globalNo = 1;
                    @endphp
                    <tr class="process-header">
                        <td colspan="15">{{ strtoupper($processName) }} PROCESS</td>
                    </tr>
                    @foreach($processItems as $item)
                        @php
                            $planAmt = $item->total_amount;
                            $realAmt = $item->realized_amount;
                            $balance = $planAmt - $realAmt;
                            $persen = ($planAmt > 0) ? ($realAmt / $planAmt) * 100 : 0;
                            $isFull = $persen >= 90;
                            $qtyA11gtPR = ($item->pr_qty > 0 && $item->qty > $item->pr_qty);
                            $qtyA11ltPR = ($item->pr_qty > 0 && $item->qty < $item->pr_qty);
                            $priceA11gtPR = ($item->pr_price > 0 && $item->estimated_price > $item->pr_price);
                            $priceA11ltPR = ($item->pr_price > 0 && $item->estimated_price < $item->pr_price);
                        @endphp
                        <tr>
                            <td class="text-center">{{ $globalNo++ }}</td>
                            <td class="fw-bold">{{ $item->item_name }}</td>
                            <td class="text-center">{{ number_format($item->qty, 0) }}</td>
                            <td class="text-center">{{ $item->pr_qty > 0 ? number_format($item->pr_qty, 0) : '-' }}</td>
                            <td class="num">{{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                            <td class="num">{{ $item->pr_price ? number_format($item->pr_price, 0, ',', '.') : '-' }}</td>
                            <td class="num">{{ number_format($planAmt, 0, ',', '.') }}</td>
                            <td class="num">{{ $realAmt > 0 ? number_format($realAmt, 0, ',', '.') : '-' }}</td>
                            <td class="num">{{ number_format($balance, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($realAmt > 0)
                                    {{ $isFull ? 'Full' : round($persen) . '%' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $qtyA11gtPR ? 'v' : '' }}</td>
                            <td class="text-center">{{ $qtyA11ltPR ? 'v' : '' }}</td>
                            <td class="text-center">{{ $priceA11gtPR ? 'v' : '' }}</td>
                            <td class="text-center">{{ $priceA11ltPR ? 'v' : '' }}</td>
                            <td style="font-size: 4.5pt;">{{ $item->evaluation_reason }}</td>
                        </tr>
                    @endforeach
                    <tr class="subtotal-row">
                        <td colspan="6" class="text-right fw-bold">Subtotal Item {{ $processName }}</td>
                        <td class="num fw-bold">{{ number_format($processTotalBudget, 0, ',', '.') }}</td>
                        <td class="num fw-bold">{{ $processTotalPR > 0 ? number_format($processTotalPR, 0, ',', '.') : '-' }}</td>
                        <td class="num fw-bold">{{ number_format($processTotalBudget - $processTotalPR, 0, ',', '.') }}</td>
                        <td colspan="6"></td>
                    </tr>
                @endforeach
                <tr class="grand-total-row">
                    <td colspan="6" class="text-right fw-bold" style="font-size: 6pt; color: white;">SUB TOTAL MACHINE</td>
                    <td class="num fw-bold" style="color: white;">{{ number_format($grandTotalBudget, 0, ',', '.') }}</td>
                    <td class="num fw-bold" style="color: white;">{{ $grandTotalPR > 0 ? number_format($grandTotalPR, 0, ',', '.') : '-' }}</td>
                    <td class="num fw-bold" style="color: white;">{{ number_format($grandTotalBudget - $grandTotalPR, 0, ',', '.') }}</td>
                    <td colspan="6"></td>
                </tr>
            </tbody>
        </table>

        <!-- Summary / Signatures could go here if needed -->
    </div>
</body>
</html>
