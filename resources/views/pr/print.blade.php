<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Purchase Request - {{ $header['pr_number'] }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        @media print {
            body { background: none !important; margin: 0 !important; width: 100% !important; height: auto !important; }
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
            font-size: 8pt;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #525659; /* Dark grey background for preview */
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }

        /* A4 Landscape Paper Simulation */
        .page-container {
            background-color: white;
            width: 297mm; /* A4 width in landscape */
            min-height: 210mm; /* A4 height in landscape */
            margin: 20px auto;
            padding: 10mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            box-sizing: border-box;
        }

        .header-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .top-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .top-info-left {
            width: 50%;
        }
        .top-info-right {
            width: 30%;
        }
        .top-info-table {
            width: 100%;
        }
        .top-info-table td {
            padding: 2px 5px;
            vertical-align: top;
            font-size: 9pt;
        }
        .top-info-table td:first-child {
            width: 100px;
            font-weight: bold;
        }
        
        table.excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.excel-table th, table.excel-table td {
            border: 1px solid #000;
            padding: 2px;
            vertical-align: middle;
            text-align: center;
        }
        table.excel-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 6.5pt;
        }
        table.excel-table td {
            font-size: 7.5pt;
        }
        table.excel-table td.text-left { text-align: left; }
        table.excel-table td.text-right { text-align: right; }
        
        .footer-signatures {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            text-align: center;
        }
        .footer-signatures td {
            border: 1px solid #000;
            width: 20%;
            padding-top: 5px;
            padding-bottom: 5px;
            font-weight: bold;
        }
        .sig-space {
            height: 60px;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 5px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        .btn-print {
            padding: 8px 16px;
            background: #1e3a5f;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print Document</button>
        <button class="btn-print" style="background:#6c757d" onclick="window.close()">Close</button>
    </div>

    <div class="page-container">
        <div class="header-title">FORM PERMINTAAN PEMBELIAN (PR)</div>

    <div class="top-info" style="position: relative; display: flex; justify-content: center; align-items: flex-start; min-height: 60px;">
        <div style="position: absolute; left: 0; top: 0;">
            <img src="{{ asset('images/company_logo.svg') }}" alt="Logo" style="height: 45px;">
        </div>
        <div>
            <table class="top-info-table" style="width: auto;">
                <tr><td style="width: 80px;">DEPT</td><td>: {{ $header['department'] }}</td></tr>
                <tr><td>PERIODE</td><td>: {{ \Carbon\Carbon::parse($header['request_date'])->format('Y') }}</td></tr>
                <tr><td>CATEGORY</td><td>: {{ $header['category'] }}</td></tr>
            </table>
        </div>
    </div>

    <!-- Items Table (Excel Layout) -->
    <table class="excel-table">
            <tr>
                <th rowspan="2" style="width: 2%;">No</th>
                <th rowspan="2">IO Number</th>
                <th rowspan="2">Cost center</th>
                <th rowspan="2">G/L Account</th>
                <th rowspan="2">AUC</th>
                <th rowspan="2">Tgl PR</th>
                <th rowspan="2">No. PR</th>
                <th rowspan="2">A11 link</th>
                <th rowspan="2">Item ID</th>
                <th rowspan="2" style="width: 15%;">Description</th>
                <th rowspan="2">Qty</th>
                <th rowspan="2">UoM</th>
                <th rowspan="2">@Price</th>
                <th rowspan="2">Total Price</th>
                <th rowspan="2" style="width: 10%;">Peruntukan</th>
                <th rowspan="2">Plant</th>
                <th rowspan="2">Storage loc</th>
                <th rowspan="2">PIC</th>
                <th rowspan="2">Due Date</th>
                <th colspan="6">Paraf dan tanggal</th>
            </tr>
            <tr>
                <th>Paraf yang<br>mengajukan</th>
                <th>Konfirmasi Dept<br>(manual)</th>
                <th>Verifikasi div<br>(manual & system)</th>
                <th>Verifikasi controlling<br>budget (Manual & system)</th>
                <th>Approval Dir<br>(manual & sistem)</th>
                <th>PNP</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @foreach($items as $index => $item)
                @php 
                                    $amount = $item->qty_req * $item->estimated_price;
                    $totalAmount += $amount;
                    $parts = explode(' | Item: ', $item->notes);
                    $desc = count($parts) > 1 ? $parts[1] : $item->notes;
                    $tglPr = $item->request_date ? \Carbon\Carbon::parse($item->request_date)->format('d/m/y') : '';
                    $dueDateStr = $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/y') : '';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->io_number ?? '-' }}</td>
                    <td>{{ $item->cost_center ?? '-' }}</td>
                    <td>{{ $item->gl_account ?? '0' }}</td>
                    <td>{{ $item->asset_no ?? '0' }}</td>
                    <td>{{ $tglPr }}</td>
                    <td>{{ $item->pr_number }}</td>
                    <td>{{ $item->budget_link ?? '-' }}</td>
                    <td>{{ $item->item_code ?? '-' }}</td>
                    <td class="text-left">{{ $desc }}</td>
                    <td>{{ number_format($item->qty_req, 0) }}</td>
                    <td>{{ $item->uom ?? 'Unit' }}</td>
                    <td class="text-right">{{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                    <td class="text-right fw-bold">{{ number_format($amount, 0, ',', '.') }}</td>
                    <td class="text-left">{{ $item->purpose ?? '-' }}</td>
                    <td>{{ $item->plant ?? '-' }}</td>
                    <td>{{ $item->storage_location ?? '-' }}</td>
                    <td>{{ $item->pic ?? '-' }}</td>
                    <td>{{ $dueDateStr }}</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endforeach
            
            {{-- Calculate empty rows to fill standard page --}}
            @for($i = count($items); $i < 5; $i++)
                <tr>
                    <td style="color:transparent">{{ $i + 1 }}</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor
            
            <tr>
                <td colspan="13" class="text-right fw-bold" style="background-color: #f2f2f2;">GRAND TOTAL (IDR)</td>
                <td class="text-right fw-bold" style="background-color: #f2f2f2;">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td colspan="11" style="background-color: #f2f2f2;"></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 7pt; color: #999; text-align: center;">
        Generated on {{ date('d/m/Y H:i:s') }} | Processed by BISS System
    </div>

    </div>

</body>
</html>
