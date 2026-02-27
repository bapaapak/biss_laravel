@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Purchase Orders</h3>
            <p class="text-muted small mb-0">Manage and track your Purchase Orders (PO).</p>
        </div>
        <div>
            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#createPoModal">
                <i class="fas fa-file-import me-1"></i> Create from PR
            </button>
            <a href="{{ route('po.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Default PO
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <form action="{{ route('po.index') }}" method="GET" class="d-flex gap-2" style="max-width: 400px;">
                <input type="text" name="search" class="form-control" placeholder="Search PO # or Vendor..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">PO Number</th>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>Total Base</th>
                        <th>Status</th>
                        <th class="text-center pe-4" style="width: 4%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                        <tr>
                            <td class="ps-4 fw-bold">
                                <a href="{{ route('po.show', $po->id) }}" class="text-decoration-none">{{ $po->po_number }}</a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($po->po_date)->format('d-M-Y') }}</td>
                            <td>{{ $po->vendor->vendor_name ?? '-' }}</td>
                            <td>Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $badge = 'bg-secondary';
                                    if ($po->status == 'Approved')
                                        $badge = 'bg-success';
                                    if ($po->status == 'Rejected')
                                        $badge = 'bg-danger';
                                    if ($po->status == 'Draft')
                                        $badge = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $badge }}">{{ $po->status }}</span>
                            </td>
                            <td class="text-center pe-4 align-middle">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0 bg-transparent" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.8rem;">
                                        <li>
                                            <a class="dropdown-item text-primary" href="{{ route('po.show', $po->id) }}">
                                                <i class="fas fa-eye me-2"></i> View Details
                                            </a>
                                        </li>
                                        @if($po->status === 'Draft' || auth()->user()->role === 'Super Admin')
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <form action="{{ route('po.destroy', $po->id) }}" method="POST"
                                                    onsubmit="return confirm('Silakan konfirmasi delete');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i> Delete PO
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No Purchase Orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pos->hasPages())
            <div class="card-footer bg-white border-0">{{ $pos->links() }}</div>
        @endif
    </div>

    <!-- Modal Create PO from PR -->
    <div class="modal fade" id="createPoModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('po.create') }}" method="GET">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create PO from PR</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label>Select Approved PR:</label>
                        <select name="pr_number" class="form-select" required>
                            <option value="">-- Choose PR --</option>
                            @foreach($approvedPrs as $pr)
                                <option value="{{ $pr->pr_number }}">{{ $pr->pr_number }} - {{ Str::limit($pr->purpose, 40) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Process <i
                                class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection