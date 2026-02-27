<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\PurchaseRequest;
use App\Models\MasterVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PoController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['vendor', 'creator'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('po_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('plant', 'like', "%{$search}%")
                ->orWhere('po_date', 'like', "%{$search}%")
                ->orWhereHas('vendor', function ($q) use ($search) {
                    $q->where('vendor_name', 'like', "%{$search}%");
                });
        }

        $pos = $query->paginate(15);

        // Fetch approved PRs to show in a modal "Create PO from PR"
        $approvedPrs = PurchaseRequest::where('status', 'Approved')
            ->select('pr_number', 'purpose', DB::raw('MAX(id) as id'))
            ->groupBy('pr_number', 'purpose')
            ->get();

        $vendors = MasterVendor::where('status', 'Active')->get();

        return view('po.index', compact('pos', 'approvedPrs', 'vendors'));
    }

    public function create(Request $request)
    {
        $prNumber = $request->pr_number;
        $prItems = [];
        $vendors = MasterVendor::where('status', 'Active')->get();

        if ($prNumber) {
            $prItems = PurchaseRequest::where('pr_number', $prNumber)->where('status', 'Approved')->get();
            if ($prItems->isEmpty()) {
                return redirect()->route('po.index')->with('error', 'PR number not found or not approved.');
            }
        }

        return view('po.create', compact('prItems', 'prNumber', 'vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_date' => 'required|date',
            'vendor_id' => 'required|exists:master_vendors,id',
            'items' => 'required|array|min:1',
            'items.*.item_description' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate PO Number
            $tahun = date('y', strtotime($request->po_date));
            $bulan = date('m', strtotime($request->po_date));
            $lastPo = PurchaseOrder::whereYear('po_date', date('Y', strtotime($request->po_date)))
                ->whereMonth('po_date', date('m', strtotime($request->po_date)))
                ->orderBy('id', 'desc')->first();

            $urut = 1;
            if ($lastPo) {
                $lastNum = intval(substr($lastPo->po_number, -4));
                $urut = $lastNum + 1;
            }
            $poNumber = "PO-{$tahun}{$bulan}-" . str_pad($urut, 4, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'po_date' => $request->po_date,
                'vendor_id' => $request->vendor_id,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'Draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'plant' => $request->plant,
                'payment_terms' => $request->payment_terms,
                'delivery_terms' => $request->delivery_terms,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            $subtotal = 0;
            $tax_amount = 0;

            foreach ($request->items as $item) {
                $qty = $item['qty'];
                $unitPrice = $item['unit_price'];
                $taxPercent = $item['tax_percent'] ?? 0;
                $totalPrice = $qty * $unitPrice;
                $tax = $totalPrice * ($taxPercent / 100);

                PoItem::create([
                    'po_id' => $po->id,
                    'pr_item_id' => $item['pr_item_id'] ?? null,
                    'item_description' => $item['item_description'],
                    'qty' => $qty,
                    'uom' => $item['uom'] ?? null,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'tax_percent' => $taxPercent,
                    'notes' => $item['notes'] ?? null,
                ]);

                $subtotal += $totalPrice;
                $tax_amount += $tax;
            }

            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'total_amount' => $subtotal + $tax_amount,
            ]);

            DB::commit();
            return redirect()->route('po.show', $po->id)->with('success', 'Purchase Order created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating PO: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['vendor', 'items.prItem', 'creator'])->findOrFail($id);
        return view('po.show', compact('po'));
    }

    public function edit($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        if ($po->status !== 'Draft') {
            return redirect()->route('po.show', $po->id)->with('error', 'Cannot edit PO that is not in Draft status.');
        }
        $vendors = MasterVendor::where('status', 'Active')->get();
        return view('po.edit', compact('po', 'vendors'));
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'Draft') {
            return redirect()->route('po.show', $po->id)->with('error', 'Cannot update PO that is not in Draft status.');
        }

        $request->validate([
            'po_date' => 'required|date',
            'vendor_id' => 'required|exists:master_vendors,id',
            'items' => 'required|array|min:1',
            'items.*.item_description' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $po->update([
                'po_date' => $request->po_date,
                'vendor_id' => $request->vendor_id,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes,
                'plant' => $request->plant,
                'payment_terms' => $request->payment_terms,
                'delivery_terms' => $request->delivery_terms,
            ]);

            // Clear old items and recreate
            $po->items()->delete();

            $subtotal = 0;
            $tax_amount = 0;

            foreach ($request->items as $item) {
                $qty = $item['qty'];
                $unitPrice = $item['unit_price'];
                $taxPercent = $item['tax_percent'] ?? 0;
                $totalPrice = $qty * $unitPrice;
                $tax = $totalPrice * ($taxPercent / 100);

                PoItem::create([
                    'po_id' => $po->id,
                    'pr_item_id' => $item['pr_item_id'] ?? null,
                    'item_description' => $item['item_description'],
                    'qty' => $qty,
                    'uom' => $item['uom'] ?? null,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'tax_percent' => $taxPercent,
                    'notes' => $item['notes'] ?? null,
                ]);

                $subtotal += $totalPrice;
                $tax_amount += $tax;
            }

            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'total_amount' => $subtotal + $tax_amount,
            ]);

            DB::commit();
            return redirect()->route('po.show', $po->id)->with('success', 'Purchase Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating PO: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'Draft') {
            return redirect()->route('po.index')->with('error', 'Only Draft PO can be deleted.');
        }
        $po->delete();
        return redirect()->route('po.index')->with('success', 'Purchase Order deleted.');
    }

    public function approve($po_number)
    {
        $po = PurchaseOrder::where('po_number', $po_number)->firstOrFail();

        // Simple 1-step logic for approval for demonstration
        // You can attach complex hierarchy if needed (similar to PR approval logic)
        $po->status = 'Approved';
        $po->save();

        return back()->with('success', "PO {$po_number} has been approved.");
    }

    public function reject($po_number)
    {
        $po = PurchaseOrder::where('po_number', $po_number)->firstOrFail();
        $po->status = 'Rejected';
        $po->save();

        return back()->with('success', "PO {$po_number} has been rejected.");
    }

    public function print($po_number)
    {
        $po = PurchaseOrder::with(['vendor', 'items.prItem', 'creator'])->where('po_number', $po_number)->firstOrFail();

        // If you want to use DOMPDF:
        // $pdf = Pdf::loadView('po.print', compact('po'));
        // return $pdf->stream("PO_{$po_number}.pdf");

        // For HTML print view:
        return view('po.print', compact('po'));
    }
}
