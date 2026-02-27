<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Budget Investment Plan - {{ $plan->io_number }}</title>
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
            font-size: 5.5pt;
            margin: 0;
            padding: 0;
            background-color: #525659; /* Dark grey background for preview */
            color: black;
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }
        
        /* A4 Portrait Paper Simulation */
        .page-container {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 10mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        th, td {
            border: 1px solid black;
            padding: 2px 4px;
            text-align: left;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        
        /* Header Styling */
        /* Header Styling */
        .logo-cell {
            text-align: center;
            vertical-align: middle;
        }
        .logo-cell img {
            max-width: 95%;
            height: auto;
            max-height: 100px;
        }
        
        /* Helper for Currency Alignment (Accounting Format) */
        .currency-cell {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        /* ... existing styles ... */
        
        /* Update signature text styling for better neatness */
        .signature-titles td {
            font-size: 5pt; 
            text-align: center;
            border: 1px solid black;
            padding: 2px 0;
            vertical-align: middle;
        }
        .title-cell {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            background-color: white;
        }
        
        /* Table Colors */
        thead th {
            background-color: #FFC000 !important; /* Orange */
            text-align: center;
            font-weight: bold;
            font-size: 5.5pt;
        }
        
        .category-row td {
            background-color: #FFFF00 !important; /* Yellow */
            font-weight: bold;
        }
        
        .subtotal-row td {
            background-color: #D8E4BC !important; /* Light Green */
            font-weight: bold;
        }
        
        .grand-total-row td {
            background-color: #D8E4BC !important; /* Same Green */
            font-weight: bold;
        }

        /* Signatures */
        .signature-table td {
            border: 1px solid black;
            text-align: center;
            vertical-align: top;
            height: 60px;
        }
        .signature-header td {
            height: auto;
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .no-print {
            padding: 10px 0;
            text-align: right;
        }


    </style>
</head>
<body>
    <div class="page-container">
    <div class="no-print">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer;">Print</button>
        <button onclick="window.close()" style="padding: 5px 15px; cursor: pointer;">Close</button>
    </div>

    <!-- Main Header -->
    <!-- Main Header -->
    <table style="border: 2px solid black; margin-bottom: 5px; width: 100%; border-collapse: collapse; table-layout: fixed;">
        <colgroup>
            <col style="width: 19%;"> <!-- NO (3%) + MACHINE (16%) -->
            <col style="width: 11%;"> <!-- BRAND (11%) -->
            <col style="width: 46%;"> <!-- QTY (5%) + PROC (15%) + COND (16%) + UNIT (10%) -->
            <col style="width: 10%;"> <!-- TOTAL COST (10%) -->
            <col style="width: 14%;"> <!-- TARGET (14%) -->
        </colgroup>
        <tr>
            <!-- Col 1: Logo -->
            <td rowspan="4" class="logo-cell" style="border: 1px solid black; padding: 5px;">
                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%;">
                    <img src="{{ asset('images/Update Logo DEM 2022 V2.png') }}" alt="Logo">
                </div>
            </td>
            <!-- Col 2+3: Title -->
            <td colspan="2" style="border: 1px solid black; font-weight: bold; text-align: center; vertical-align: middle; font-size: 14pt; height: 35px;">BUDGET INVESTMENT PLAN</td>
            <!-- Col 4: A11 -->
            <td rowspan="4" class="text-center fw-bold" style="font-size: 24pt; border: 1px solid black;">A11</td>
            <!-- Col 5: Prepared By -->
            <td rowspan="4" style="padding: 0; border: 1px solid black; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; height: 100%; table-layout: fixed;">
                    <tr>
                        <td style="background-color: #e6e6e6; font-weight: bold; border-bottom: 1px solid black; padding: 2px 5px; height: 18px;">Prepared by</td>
                    </tr>
                    <tr>
                        <td style="height: 52px;"></td> <!-- Signature Space -->
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid black; padding: 2px 5px; font-size: 5.5pt;">Date : {{ date('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid black; padding: 2px 5px; font-size: 5.5pt; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $plan->creator ? ($plan->creator->full_name ?? $plan->creator->username) : 'Arif Budianto' }}</td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid black; padding: 2px 5px; font-size: 5.5pt;">Rev.00</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <!-- Row 2: Project Name -->
            <td style="font-weight: bold; border: 1px solid black; padding-left: 5px;">Project Name</td>
            <td style="border: 1px solid black; font-weight: bold; padding-left: 5px;">{{ strtoupper($plan->project->project_name ?? '-') }}</td>
        </tr>
        <tr>
            <!-- Row 3: Customer -->
            <td style="font-weight: bold; border: 1px solid black; padding-left: 5px;">Customer</td>
            <td style="border: 1px solid black; font-weight: bold; padding-left: 5px;">{{ strtoupper($plan->customer) }}</td>
        </tr>
        <tr>
            <!-- Row 4: Purpose -->
            <td style="font-weight: bold; border: 1px solid black; padding-left: 5px;">Purpose</td>
            <td style="border: 1px solid black; padding-left: 5px;">
                <div style="display: flex; align-items: center;">
                    <span style="display: flex; align-items: center; margin-right: 15px;">
                        <input type="checkbox" {{ $plan->purpose == 'Production' ? 'checked' : '' }} style="margin-right: 5px;"> Production
                    </span>
                    <span style="display: flex; align-items: center; margin-right: 15px;">
                        <input type="checkbox" {{ $plan->purpose == 'R & D' ? 'checked' : '' }} style="margin-right: 5px;"> R & D
                    </span>
                    <span style="display: flex; align-items: center;">
                        <input type="checkbox" {{ $plan->purpose == 'Others' ? 'checked' : '' }} style="margin-right: 5px;"> Others
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table style="border: 2px solid black; width: 100%; border-collapse: collapse; table-layout: fixed;">
        <colgroup>
            <col style="width: 3%;">   <!-- NO. -->
            <col style="width: 16%;">  <!-- MACHINE NAME -->
            <col style="width: 11%;">  <!-- BRAND -->
            <col style="width: 5%;">   <!-- QTY. -->
            <col style="width: 15%;">  <!-- PROCESS -->
            <col style="width: 4.5%;"> <!-- READY -->
            <col style="width: 4.5%;"> <!-- NOT READY -->
            <col style="width: 7%;">   <!-- NOTES -->
            <col style="width: 10%;">  <!-- UNIT COST -->
            <col style="width: 10%;">  <!-- TOTAL COST -->
            <col style="width: 14%;">  <!-- TARGET -->
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" class="text-center">NO.</th>
                <th rowspan="2" class="text-center">MACHINE / EQUIP. NAME</th>
                <th rowspan="2" class="text-center">BRAND / SPECIFICATION</th>
                <th rowspan="2" class="text-center">QTY.</th>
                <th rowspan="2" class="text-center">APPLICATION PROCESS</th>
                <th colspan="3" class="text-center">CONDITION</th>
                <th rowspan="2" class="text-center">ESTIMATION COST INVEST. (RP / UNIT)</th>
                <th rowspan="2" class="text-center">ESTIMATION COST INVEST. TOTAL (RP.)</th>
                <th rowspan="2" class="text-center">TARGET SCHEDULE</th>
            </tr>
            <tr>
                <th class="text-center" style="font-size:4.5pt;">READY</th>
                <th class="text-center" style="font-size:4.5pt; white-space: normal; line-height: 1.1; padding: 1px 0;">NOT<br>READY</th>
                <th class="text-center" style="font-size:5pt;">NOTES</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($groupedItems as $letter => $group)
                @php $subtotal = 0; $rowIdx = 0; @endphp
                <tr class="category-row">
                    <td class="text-center" style="background-color: #FFFF00;">{{ $letter }}</td>
                    <td colspan="10" style="background-color: #FFFF00;">{{ strtoupper($group['title']) }}</td>
                </tr>
                
                @if(count($group['items']) > 0)
                    @foreach($group['items'] as $item)
                        @php 
                            $itemTotal = $item->qty * $item->estimated_price;
                            $subtotal += $itemTotal;
                            $grandTotal += $itemTotal;
                            $rowIdx++;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $rowIdx }}</td>
                            <td class="fw-bold">{{ $item->item_name }}</td>
                            <td>{{ $item->brand_spec }}</td>
                            <td class="text-center">{{ $item->qty }} {{ $item->uom }}</td>
                            <td>{{ $item->application_process }}</td>
                            <!-- Logic for checks: assuming condition_status stores 'Ready' or 'Not Ready' -->
                            <td class="text-center">{{ isset($item->condition_status) && $item->condition_status == 'Ready' ? 'x' : '' }}</td>
                            <td class="text-center">{{ isset($item->condition_status) && $item->condition_status == 'Not Ready' ? 'x' : '' }}</td>
                            <td>{{ $item->condition_notes ?? '' }}</td>
                            <td>
                                <div class="currency-cell">
                                    <span>Rp</span>
                                    <span>{{ number_format($item->estimated_price, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="currency-cell">
                                    <span>Rp</span>
                                    <span>{{ number_format($itemTotal, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="text-center">{{ $item->target_schedule }}</td>
                        </tr>
                        
                        {{-- Breakdown Items if any --}}
                         @if($item->breakdown && count($item->breakdown) > 0)
                            @foreach($item->breakdown as $sub)
                            <tr>
                                <td></td>
                                <td style="padding-left:15px; font-style:italic;">- {{ $sub->item_name }}</td>
                                <td style="font-style:italic;">{{ $sub->brand_spec }}</td>
                                <td class="text-center" style="font-style:italic;">{{ $sub->qty }} {{ $sub->uom ?? 'Unit' }}</td>
                                <td style="font-style:italic;">{{ $sub->application_process }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-style:italic;">
                                    <div class="currency-cell">
                                        <span>Rp</span>
                                        <span>{{ number_format($sub->estimated_price, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforeach
                        @endif

                    @endforeach
                     <tr class="subtotal-row">
                        <td colspan="9" class="text-right">Sub Total {{ $letter }}</td>
                        <td>
                            <div class="currency-cell" style="font-weight: bold;">
                                <span>Rp</span>
                                <span>{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                @endif
            @endforeach

             <tr class="grand-total-row">
                <td colspan="9" class="text-right">Grand Total Nilai Investasi</td>
                <td>
                    <div class="currency-cell" style="font-weight: bold;">
                         <span>Rp</span>
                         <span>{{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div style="border: 2px solid black; padding: 5px; margin-top: 5px; font-size: 6pt; margin-bottom: 5px;">
        <span class="fw-bold" style="text-decoration: underline;">NOTES:</span><br>
        Pengajuan A11 menggunakan No IO {{ $plan->io_number }}
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 5px;">
        <table style="width: 65%; border: 2px solid black; border-collapse: collapse;">
            <tr class="signature-header">
                <td colspan="1" style="width: 16%; border: 1px solid black;">Approved By</td>
                <td colspan="5" style="width: 84%; border: 1px solid black;">Acknowledged By</td>
            </tr>
            <tr>
                <!-- Approved By: Yohanes Susanto -->
                <td style="border: 1px solid black; height: 80px; vertical-align: bottom; text-align: center;">
                    <div class="fw-bold" style="text-decoration: underline; font-size: 5.5pt;">Yohanes Susanto</div>
                </td>
                <!-- Acknowledged By: Others -->
                <td style="border: 1px solid black; height: 80px; vertical-align: bottom; text-align: center; width: 16%;">
                    <div class="fw-bold" style="text-decoration: underline; font-size: 5.5pt;">Laberte Andri H.</div>
                </td>
                <td style="border: 1px solid black; height: 80px; vertical-align: bottom; text-align: center; width: 16%;">
                    <div class="fw-bold" style="text-decoration: underline; font-size: 5.5pt;">Adimas Hartanto</div>
                </td>
                <td style="border: 1px solid black; height: 80px; vertical-align: bottom; text-align: center; width: 16%;">
                    <div class="fw-bold" style="text-decoration: underline; font-size: 5.5pt;">Elisabeth Simarmata</div>
                </td>
                <td style="border: 1px solid black; height: 80px; vertical-align: bottom; text-align: center; width: 16%;">
                    <div class="fw-bold" style="text-decoration: underline; font-size: 5.5pt;">Zulkarnaen</div>
                </td>
                <td style="border: 1px solid black; height: 80px; vertical-align: bottom; text-align: center; width: 16%;">
                    <div class="fw-bold" style="text-decoration: underline; font-size: 5.5pt;">Supriyanto</div>
                </td>
            </tr>
            <tr class="signature-titles">
                <td>President Director</td>
                <td>MPD Div Head</td>
                <td>Fin/Acc Dept. Head</td>
                <td>Purchasing Dept. Head</td>
                <td>PME FA</td>
                <td>PME PP</td>
            </tr>
        </table>
    </div>
    </div>
</body>
</html>
