@extends('layouts.app')

@section('content')
<style>
    .form-label-custom { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.4rem; }
    .card-section-title { font-size: 0.95rem; font-weight: 700; color: #334155; margin-bottom: 1rem; border-left: 4px solid #0d6efd; padding-left: 0.75rem; }
    .date-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; }
    .mass-pro-highlight { border: 1px solid #10b981; background-color: #ecfdf5; }
    .mass-pro-highlight input { border-color: #10b981; color: #047857; font-weight: 600; }
    .event-row { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; display: flex; align-items: center; }
</style>

<form action="{{ route('projects.store') }}" method="POST" id="createProjectForm">
    @csrf

    <div class="card border-0 shadow-sm rounded-3">
        <!-- Header -->
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-briefcase text-primary"></i>
                <h5 class="fw-bold mb-0 text-dark">New Project</h5>
            </div>
            <a href="{{ route('projects.index') }}" class="btn-close"></a>
        </div>

        <div class="card-body p-4">
            
            @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- 1. General Information -->
            <div class="mb-5">
                <h6 class="card-section-title">General Information</h6>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label-custom">Project Name *</label>
                        <input type="text" name="project_name" class="form-control" placeholder="Project Name" value="{{ old('project_name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Project Code *</label>
                        <input type="text" name="project_code" class="form-control" placeholder="P-{{ date('Y') }}-XX" value="{{ old('project_code') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Customer</label>
                        <select name="customer" class="form-select">
                            <option value="">Select Customer</option>
                            @foreach($customers as $cust)
                            <option value="{{ $cust->customer_code }}" {{ old('customer') == $cust->customer_code ? 'selected' : '' }}>{{ $cust->customer_code }} | {{ $cust->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label-custom">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->category_name }}" {{ old('category') == $cat->category_name ? 'selected' : '' }}>{{ $cat->category_code }} | {{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Project Manager</label>
                        <select name="pic_user_id" class="form-select">
                            <option value="">PM Name</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('pic_user_id') == $u->id ? 'selected' : '' }}>{{ $u->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Model</label>
                        <input type="text" name="model" class="form-control" placeholder="Vehicle Model" value="{{ old('model') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Year</label>
                        <input type="number" name="year" class="form-control" placeholder="{{ date('Y') }}" value="{{ old('year', date('Y')) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Start Date *</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">End Date *</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Project Scope...">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3" style="max-width: 300px;">
                    <label class="form-label-custom">Status</label>
                    <select name="status" class="form-select">
                        <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Ongoing" {{ old('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="Hold" {{ old('status') == 'Hold' ? 'selected' : '' }}>Hold</option>
                        <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- 2. Master Schedule -->
            <div class="mb-5">
                <h6 class="card-section-title"><i class="far fa-calendar-alt me-2"></i>Master Schedule</h6>
                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="date-card">
                            <label class="form-label-custom small text-muted">DIE GO</label>
                            <input type="date" name="die_go" class="form-control form-control-sm border-0 bg-light" value="{{ old('die_go') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="date-card">
                            <label class="form-label-custom small text-muted">TO</label>
                            <input type="date" name="to" class="form-control form-control-sm border-0 bg-light" value="{{ old('to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="date-card">
                            <label class="form-label-custom small text-muted">PP1</label>
                            <input type="date" name="pp1" class="form-control form-control-sm border-0 bg-light" value="{{ old('pp1') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="date-card">
                            <label class="form-label-custom small text-muted">PP2</label>
                            <input type="date" name="pp2" class="form-control form-control-sm border-0 bg-light" value="{{ old('pp2') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="date-card">
                            <label class="form-label-custom small text-muted">PP3</label>
                            <input type="date" name="pp3" class="form-control form-control-sm border-0 bg-light" value="{{ old('pp3') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="date-card mass-pro-highlight">
                            <label class="form-label-custom small text-success">MASS PRO</label>
                            <input type="date" name="mass_pro" class="form-control form-control-sm bg-transparent" value="{{ old('mass_pro') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Key Milestones & Events -->
            <div class="mb-4">
                <h6 class="card-section-title text-danger border-danger"><i class="far fa-flag me-2"></i>Key Milestones & Events</h6>
                
                <div class="card bg-light border-0 p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label-custom">Event Name</label>
                            <input type="text" id="newEventName" class="form-control bg-white" placeholder="e.g. Kickoff Meeting">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Date</label>
                            <input type="date" id="newEventDate" class="form-control bg-white">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-dark w-100" onclick="addMilestone()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="milestonesList">
                    <!-- Dynamic milestones will be added here -->
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="card-footer bg-white border-top p-4 text-end">
            <a href="{{ route('projects.index') }}" class="btn btn-link text-decoration-none text-muted me-3">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold">
                <i class="fas fa-save me-2"></i>Save Project
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
let msCounter = 0;

function addMilestone() {
    const name = document.getElementById('newEventName').value;
    const date = document.getElementById('newEventDate').value;

    if (!name || !date) {
        alert("Please fill event name and date.");
        return;
    }

    const id = 'ms-new-' + msCounter++;
    const container = document.getElementById('milestonesList');
    
    const html = `
        <div class="event-row" id="${id}">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-1">
                    <i class="far fa-flag text-warning me-2"></i>
                    <span class="fw-bold text-dark">${name}</span>
                </div>
                <div class="small text-muted ms-4">${date}</div>
                <input type="hidden" name="milestone_names[]" value="${name}">
                <input type="hidden" name="milestone_dates[]" value="${date}">
            </div>
            <button type="button" class="btn btn-link text-danger p-0" onclick="removeMilestone('${id}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    
    document.getElementById('newEventName').value = '';
    document.getElementById('newEventDate').value = '';
}

function removeMilestone(id) {
    document.getElementById(id).remove();
}
</script>
@endpush
