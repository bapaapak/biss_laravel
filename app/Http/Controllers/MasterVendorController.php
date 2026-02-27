<?php

namespace App\Http\Controllers;

use App\Models\MasterVendor;
use Illuminate\Http\Request;

class MasterVendorController extends Controller
{
    public function index()
    {
        $vendors = MasterVendor::orderBy('vendor_name')->paginate(15);
        return view('master.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('master.vendors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_code' => 'required|unique:master_vendors',
            'vendor_name' => 'required',
        ]);

        MasterVendor::create($request->all());

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully');
    }

    public function edit(MasterVendor $vendor)
    {
        return view('master.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, MasterVendor $vendor)
    {
        $request->validate([
            'vendor_code' => 'required|unique:master_vendors,vendor_code,' . $vendor->id,
            'vendor_name' => 'required',
        ]);

        $vendor->update($request->all());

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully');
    }

    public function destroy(MasterVendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully');
    }
}
