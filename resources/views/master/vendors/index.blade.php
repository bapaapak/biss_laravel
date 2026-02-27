@extends('layouts.app')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Master Vendors</h4>
            <p class="text-muted mb-0 small">Manage suppliers and vendors for Purchase Orders.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus me-1"></i> Add Vendor
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Vendor Name</th>
                        <th>Contact</th>
                        <th>Email/Phone</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $vendor->vendor_code }}</td>
                            <td>{{ $vendor->vendor_name }}<br><small
                                    class="text-muted">{{ Str::limit($vendor->address, 30) }}</small></td>
                            <td>{{ $vendor->contact_person ?? '-' }}</td>
                            <td>{{ $vendor->email ?? '-' }}<br><small class="text-muted">{{ $vendor->phone ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $vendor->status == 'Active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $vendor->status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-secondary me-2 btn-edit"
                                    data-vendor="{{ json_encode($vendor) }}" data-bs-toggle="modal" data-bs-target="#editModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Silakan konfirmasi penghapusan master data ini.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No vendors available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
            <div class="card-footer bg-white border-0">{{ $vendors->links() }}</div>
        @endif
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('vendors.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Master Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Vendor Code *</label>
                            <input type="text" name="vendor_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Vendor Name *</label>
                            <input type="text" name="vendor_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Terms of Payment</label>
                            <input type="text" name="terms_of_payment" class="form-control" placeholder="e.g. NET 30, COD">
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Vendor</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Master Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Vendor Code *</label>
                            <input type="text" name="vendor_code" id="edit_vendor_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Vendor Name *</label>
                            <input type="text" name="vendor_name" id="edit_vendor_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" id="edit_contact_person" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" id="edit_phone" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Terms of Payment</label>
                            <input type="text" name="terms_of_payment" id="edit_terms" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Vendor</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const vendor = JSON.parse(this.dataset.vendor);
                        const form = document.getElementById('editForm');

                        form.action = `/vendors/${vendor.id}`;
                        document.getElementById('edit_vendor_code').value = vendor.vendor_code;
                        document.getElementById('edit_vendor_name').value = vendor.vendor_name;
                        document.getElementById('edit_contact_person').value = vendor.contact_person || '';
                        document.getElementById('edit_email').value = vendor.email || '';
                        document.getElementById('edit_phone').value = vendor.phone || '';
                        document.getElementById('edit_terms').value = vendor.terms_of_payment || '';
                        document.getElementById('edit_address').value = vendor.address || '';
                        document.getElementById('edit_status').value = vendor.status || 'Active';
                    });
                });
            });
        </script>
    @endpush
@endsection