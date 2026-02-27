<?php

namespace App\Imports;

use App\Models\PurchaseRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PurchaseRequestImport
{
    /**
     * Import PR data from an Excel file with multiple sheets.
     * Each sheet has header cells (I3=Dept, I4=Periode, I5=Type) and data rows from row 9.
     *
     * Column mapping (Excel letter → 1-based column):
     * C=3  IO Number       | D=4  Cost Center    | E=5  G/L Account
     * F=6  Asset No (AUC)  | H=8  Tgl PR         | I=9  No. PR
     * J=10 Budget Link     | K=11 Item ID        | L=12 Description
     * M=13 Qty             | N=14 UOM            | O=15 Cost/Unit (@Price)
     * P=16 Total Price     | Q=17 Peruntukan     | R=18 Plant
     * S=19 Storage Location| T=20 PIC            | U=21 Due Date
     */
    public function import($file)
    {
        // Determine file path
        $filePath = $file instanceof UploadedFile ? $file->getPathname() : $file;

        $spreadsheet = IOFactory::load($filePath);
        $sheetCount = $spreadsheet->getSheetCount();

        // Generate ONE PR number for the entire file
        $bulan = date('m');
        $tahun = date('Y');
        $prefix = "PR/{$tahun}/{$bulan}/";
        $lastPr = PurchaseRequest::where('pr_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('pr_number');
        $newUrutan = '001';
        if ($lastPr) {
            $lastDigit = (int) substr($lastPr, -3);
            $newUrutan = str_pad($lastDigit + 1, 3, '0', STR_PAD_LEFT);
        }
        $prNumber = $prefix . $newUrutan;

        $userId = Auth::id() ?? 1;
        $importedCount = 0;

        for ($s = 0; $s < $sheetCount; $s++) {
            $sheet = $spreadsheet->getSheet($s);

            // Read header cells dynamically from column H and I
            $department = null;
            $periode = null;
            $businessCategory = null;

            for ($headRow = 2; $headRow <= 8; $headRow++) {
                $hVal = strtoupper(trim((string) $sheet->getCell("H{$headRow}")->getCalculatedValue()));
                $iVal = $this->cleanHeaderValue($sheet->getCell("I{$headRow}")->getCalculatedValue());
                if ($hVal === 'DEPT') {
                    $department = $iVal;
                } elseif ($hVal === 'PERIODE') {
                    $periode = $iVal;
                } elseif ($hVal === 'CATEGORY') {
                    $businessCategory = $iVal;
                }
            }

            // Map Dept Code to Dept Name if matched
            if ($department) {
                $deptRow = \Illuminate\Support\Facades\DB::table('master_departments')->where('dept_code', $department)->first();
                if ($deptRow) {
                    $department = $deptRow->dept_name;
                }
            }

            // Map Category Code to Category Name if matched
            if ($businessCategory) {
                $catRow = \Illuminate\Support\Facades\DB::table('master_categories')->where('category_code', $businessCategory)->first();
                if ($catRow) {
                    $businessCategory = $catRow->category_name;
                }
            }

            // Process data rows (starting from row 9)
            $highestRow = $sheet->getHighestRow();

            for ($row = 9; $row <= $highestRow; $row++) {
                // L = Description
                $description = trim((string) ($sheet->getCell("L{$row}")->getCalculatedValue() ?? ''));

                // M = Qty
                $qty = $sheet->getCell("M{$row}")->getCalculatedValue();

                // Skip rows without description or non-numeric qty
                if (empty($description) || empty($qty) || !is_numeric($qty)) {
                    continue;
                }

                // O = @Price (cost/unit)
                $costUnit = $sheet->getCell("O{$row}")->getCalculatedValue();

                // Skip TOTAL rows
                if (strtoupper(trim((string) $costUnit)) === 'TOTAL') {
                    continue;
                }

                // C = IO Number
                $ioNumber = trim((string) ($sheet->getCell("C{$row}")->getCalculatedValue() ?? ''));

                // D = Cost Center
                $costCenter = trim((string) ($sheet->getCell("D{$row}")->getCalculatedValue() ?? ''));

                // E = G/L Account
                $glAccount = trim((string) ($sheet->getCell("E{$row}")->getCalculatedValue() ?? ''));

                // F = Asset No (AUC)
                $assetNo = trim((string) ($sheet->getCell("F{$row}")->getCalculatedValue() ?? ''));

                // H = Tanggal PR
                $reqDate = now();
                $tglPrRaw = trim((string) $sheet->getCell("H{$row}")->getCalculatedValue());
                if (!empty($tglPrRaw)) {
                    if (is_numeric($tglPrRaw)) {
                        try {
                            $reqDate = ExcelDate::excelToDateTimeObject($tglPrRaw);
                        } catch (\Exception $e) {
                            $reqDate = now();
                        }
                    } else {
                        try {
                            $reqDate = \Carbon\Carbon::createFromFormat('d/m/y', $tglPrRaw);
                        } catch (\Exception $e) {
                            try {
                                $reqDate = \Carbon\Carbon::parse($tglPrRaw);
                            } catch (\Exception $e2) {
                                $reqDate = now();
                            }
                        }
                    }
                }

                // I = No. PR (use from Excel if filled, otherwise use generated)
                $rowPrNumber = trim((string) ($sheet->getCell("I{$row}")->getCalculatedValue() ?? ''));
                if (empty($rowPrNumber)) {
                    $rowPrNumber = $prNumber;
                }

                // J = Budget link
                $budgetLink = trim((string) ($sheet->getCell("J{$row}")->getCalculatedValue() ?? ''));

                // K = Item ID
                $itemCode = trim((string) ($sheet->getCell("K{$row}")->getCalculatedValue() ?? ''));

                // Try to find budget_item_id
                $budgetItemId = null;
                if (!empty($budgetLink)) {
                    if (is_numeric($budgetLink)) {
                        $budgetItemId = (int) $budgetLink;
                    } else {
                        // Regex to parse "1501101277 - Poin B (Silver Box Assembly Car)"
                        // Pattern: {IO} - Poin {Code}
                        if (preg_match('/^([0-9]+)\s*-\s*Poin\s+([A-Z0-9]+)/i', $budgetLink, $matches)) {
                            $extractedIo = $matches[1];
                            $extractedCode = $matches[2];

                            $lookupBi = \App\Models\BudgetItem::join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                                ->join('master_io', 'budget_plans.io_id', '=', 'master_io.id')
                                ->where('master_io.io_number', $extractedIo)
                                ->where('budget_items.item_code', $extractedCode)
                                ->where('budget_plans.status', 'Approved')
                                ->select('budget_items.id')
                                ->first();

                            if ($lookupBi) {
                                $budgetItemId = $lookupBi->id;
                            }
                        }

                        // Fallback: If still not found, try previous lookup by description and IO
                        if (!$budgetItemId) {
                            $lookupBi = \App\Models\BudgetItem::join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                                ->where('budget_items.item_name', $description)
                                ->where('budget_plans.io_number', $ioNumber)
                                ->where('budget_plans.status', 'Approved')
                                ->select('budget_items.id')
                                ->first();

                            if ($lookupBi) {
                                $budgetItemId = $lookupBi->id;
                            }
                        }
                    }
                }

                // N = UOM
                $uom = trim((string) ($sheet->getCell("N{$row}")->getCalculatedValue() ?? ''));

                // P = Total Price
                $totalPriceRaw = $sheet->getCell("P{$row}")->getCalculatedValue();
                $totalPrice = is_numeric($totalPriceRaw) ? (float) $totalPriceRaw : null;

                // Q = Peruntukan
                $peruntukan = trim((string) ($sheet->getCell("Q{$row}")->getCalculatedValue() ?? ''));

                // R = Plant
                $plant = trim((string) ($sheet->getCell("R{$row}")->getCalculatedValue() ?? ''));

                // S = Storage Location
                $storageLocation = trim((string) ($sheet->getCell("S{$row}")->getCalculatedValue() ?? ''));

                // T = PIC
                $pic = trim((string) ($sheet->getCell("T{$row}")->getCalculatedValue() ?? ''));

                // U = Due Date
                $dueDate = null;
                $dueDateRaw = trim((string) $sheet->getCell("U{$row}")->getCalculatedValue());
                if (!empty($dueDateRaw)) {
                    if (is_numeric($dueDateRaw)) {
                        try {
                            $dueDate = ExcelDate::excelToDateTimeObject($dueDateRaw);
                        } catch (\Exception $e) {
                            $dueDate = null;
                        }
                    } else {
                        try {
                            $dueDate = \Carbon\Carbon::createFromFormat('d/m/y', $dueDateRaw);
                        } catch (\Exception $e) {
                            try {
                                $dueDate = \Carbon\Carbon::parse($dueDateRaw);
                            } catch (\Exception $e2) {
                                $dueDate = null;
                            }
                        }
                    }
                }

                PurchaseRequest::create([
                    'pr_number' => $rowPrNumber,
                    'department' => $department,
                    'business_category' => $businessCategory,
                    'periode' => $periode,
                    'io_number' => $ioNumber !== '' ? $ioNumber : null,
                    'cost_center' => $costCenter !== '' ? $costCenter : null,
                    'budget_item_id' => $budgetItemId,
                    'budget_link' => $budgetLink !== '' ? $budgetLink : null,
                    'item_code' => $itemCode !== '' ? $itemCode : null,
                    'request_date' => $reqDate,
                    'requester_id' => $userId,
                    'qty_req' => (int) $qty,
                    'uom' => $uom !== '' ? $uom : null,
                    'estimated_price' => is_numeric($costUnit) ? (float) $costUnit : 0,
                    'total_price' => $totalPrice,
                    'purpose' => $peruntukan !== '' ? $peruntukan : $description,
                    'status' => 'Submitted',
                    'current_approver_role' => 'Dept Head',
                    'notes' => $description,
                    'asset_no' => $assetNo !== '' ? $assetNo : null,
                    'gl_account' => $glAccount !== '' ? $glAccount : null,
                    'storage_location' => $storageLocation !== '' ? $storageLocation : null,
                    'plant' => $plant !== '' ? $plant : null,
                    'pic' => $pic !== '' ? $pic : null,
                    'due_date' => $dueDate,
                ]);

                $importedCount++;
            }
        }

        if ($importedCount === 0) {
            throw new \Exception('Tidak ada data PR yang valid ditemukan di file Excel.');
        }

        return $importedCount;
    }

    /**
     * Clean header value: remove leading ": " prefix
     */
    private function cleanHeaderValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        $value = trim((string) $value);
        if (str_starts_with($value, ': ')) {
            $value = trim(substr($value, 2));
        } elseif (str_starts_with($value, ':')) {
            $value = trim(substr($value, 1));
        }
        return $value ?: null;
    }
}
