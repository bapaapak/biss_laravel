@extends('layouts.app')

@section('content')
    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1e3a5f;">Create Budget Investment Plan</h5>
                    <p class="text-muted small mb-0">Format: A11 Budget Investment Plan</p>
                </div>
            </div>
            <a href="{{ route('budget.index') }}" class="btn btn-light btn-sm rounded-circle shadow-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('budget.store') }}" method="POST" id="budgetForm">
                @csrf

                <!-- Header Section: Purpose & Metadata -->
                <div class="row mb-4 bg-light p-3 rounded-3 g-3">
                    <div class="col-md-4">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Purpose</label>
                        <div class="d-flex gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="purpose" id="purpose_prod"
                                    value="Production" checked>
                                <label class="form-check-label small" for="purpose_prod">Production</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="purpose" id="purpose_rnd"
                                    value="R and D">
                                <label class="form-check-label small" for="purpose_rnd">R & D</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="purpose" id="purpose_others"
                                    value="Others">
                                <label class="form-check-label small" for="purpose_others">Others</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-uppercase text-muted small fw-bold" style="font-size: 0.7rem;">Fiscal
                            Year</label>
                        <select name="fiscal_year" class="form-select form-select-sm border-0 shadow-sm">
                            @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Department</label>
                        <select name="department" class="form-select form-select-sm border-0 shadow-sm" required>
                            <option value="">-- Select Dept --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->dept_code }}">{{ $dept->dept_code }} - {{ $dept->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold" style="font-size: 0.7rem;">Cost
                            Center</label>
                        <select name="cost_center" class="form-select form-select-sm border-0 shadow-sm" required>
                            <option value="">-- Select CC --</option>
                            @foreach($ccs as $cc)
                                <option value="{{ $cc->cc_code }}">{{ $cc->cc_code }} - {{ $cc->cc_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Project & IO Selection -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label text-uppercase text-muted small fw-bold" style="font-size: 0.7rem;">Project
                            / IO Reference</label>
                        <select id="projectIoSelect" class="form-select border-0 shadow-sm" onchange="updateProjectIo(this)"
                            required>
                            <option value="">-- Select Project/IO --</option>
                            @foreach($ios as $io)
                                <option value="{{ $io->io_number }}" data-project-id="{{ $io->project_id }}"
                                    data-project-name="{{ $io->project_name }}">
                                    {{ $io->io_number }} | {{ $io->project_name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="project_id" id="project_id">
                        <input type="hidden" name="io_number" id="io_number">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Customer</label>
                        <select name="customer" class="form-select border-0 shadow-sm" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->customer_name }}">{{ $cust->customer_code ?? '-' }} -
                                    {{ $cust->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Model</label>
                        <input type="text" name="model" class="form-control border-0 shadow-sm" placeholder="Model Name"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Investment Type</label>
                        <div class="bg-white p-2 rounded-3 border d-flex gap-3 shadow-sm">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="investment_type" id="type_capex"
                                    value="Capex" checked>
                                <label class="form-check-label small" for="type_capex">Capex</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="investment_type" id="type_opex"
                                    value="Opex">
                                <label class="form-check-label small" for="type_opex">Opex</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Item Form (Expanded) -->
                <div class="border rounded-4 p-4 mb-4 bg-white shadow-sm"
                    style="border-style: dashed !important; border-width: 2px !important;">
                    <h6 class="fw-bold mb-3 text-primary d-flex align-items-center">
                        <i class="fas fa-plus-circle me-2"></i> Add Item Details
                    </h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-1">
                            <label class="form-label small fw-bold text-muted">Code</label>
                            <input type="text" id="item_code" class="form-control form-control-sm" placeholder="A/B"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Category</label>
                            <input type="text" id="item_category" class="form-control form-control-sm" list="category_list"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Section / Process</label>
                            <select id="item_process" class="form-select form-select-sm">
                                <option value="Preparation">Preparation</option>
                                <option value="Casting">Casting</option>
                                <option value="Machining">Machining</option>
                                <option value="Painting">Painting</option>
                                <option value="Assembly">Assembly</option>
                                <option value="Quality">Quality</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Machine / Equipment Name</label>
                            <input type="text" id="item_name" class="form-control form-control-sm"
                                placeholder="Full name of the item" onblur="this.value = toFlexibleProperCase(this.value)">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Brand / Spec</label>
                            <input type="text" id="item_brand" class="form-control form-control-sm"
                                placeholder="e.g., Fanuc / High Speed"
                                onblur="this.value = toFlexibleProperCase(this.value)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Application Process</label>
                            <input type="text" id="item_app_process" class="form-control form-control-sm"
                                placeholder="e.g., Milling Case Engine"
                                onblur="this.value = toFlexibleProperCase(this.value)">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Qty</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="item_qty" class="form-control" value="1" min="1">
                                <span class="input-group-text">Unit</span>
                            </div>
                            <input type="hidden" id="item_uom" value="Unit">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Cost (Unit)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light">IDR</span>
                                <input type="text" id="item_price_display" class="form-control" value="0"
                                    onkeyup="formatNumber(this)">
                            </div>
                            <input type="hidden" id="item_price" value="0">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Condition</label>
                            <select id="item_condition_status" class="form-select form-select-sm">
                                <option value="Ready">Ready</option>
                                <option value="Not Ready">Not Ready</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Notes / Reason (Not Ready)</label>
                            <input type="text" id="item_condition_notes" class="form-control form-control-sm"
                                placeholder="Reason if not ready" onblur="this.value = toFlexibleProperCase(this.value)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Target Schedule</label>
                            <input type="text" id="item_schedule" class="form-control form-control-sm"
                                placeholder="e.g., Q3 2026">
                        </div>
                    </div>

                    <!-- Breakdown Section -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_breakdown" onchange="toggleBreakdown()">
                            <label class="form-check-label fw-bold text-primary" for="has_breakdown">
                                <i class="fas fa-list-ul me-1"></i> Add Detail Breakdown (Components)
                            </label>
                        </div>
                    </div>

                    <div id="breakdown_section" class="d-none bg-light p-3 rounded-3 mb-3 border">
                        <h6 class="small fw-bold text-muted mb-2">Breakdown Components</h6>
                        <table class="table table-sm table-bordered bg-white mb-2">
                            <thead class="table-light">
                                <tr class="text-center small">
                                    <th>Component Name</th>
                                    <th>Brand / Spec</th>
                                    <th>App Process</th>
                                    <th width="10%">Qty</th>
                                    <th width="20%">Price (Unit)</th>
                                    <th width="5%"><i class="fas fa-trash-alt"></i></th>
                                </tr>
                            </thead>
                            <tbody id="breakdown_table_body">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                            onclick="addBreakdownRow()">
                            <i class="fas fa-plus me-1"></i> Add Component
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-primary px-4 shadow-sm" id="addItemBtn" onclick="addItem()">
                        <i class="fas fa-plus me-2"></i>Add to List
                    </button>
                    <button type="button" class="btn btn-warning px-4 shadow-sm d-none" id="updateItemBtn"
                        onclick="updateItem()">
                        <i class="fas fa-save me-2"></i>Update Item
                    </button>
                    <button type="button" class="btn btn-secondary px-4 shadow-sm d-none ms-2" id="cancelEditBtn"
                        onclick="cancelEdit()">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>
        </div>

        <!-- Items Table Section -->
        <div class="table-responsive rounded-4 border shadow-sm mb-4">
            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                <thead class="bg-primary text-white">
                    <tr class="align-middle">
                        <th width="5%" class="text-center">No</th>
                        <th width="15%">Machine / Equipment</th>
                        <!-- Removed Model Column -->
                        <th width="15%">Brand / Spec</th>
                        <th width="5%" class="text-center">Qty</th>
                        <th width="15%">App Process</th>
                        <th width="10%">Condition</th>
                        <th width="15%" class="text-end">Total Cost</th>
                        <th width="10%" class="text-center">Schedule</th>
                        <th width="5%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="itemsTable">
                    <tr id="emptyRow">
                        <td colspan="9" class="text-center py-4 text-muted small">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="48"
                                class="opacity-25 mb-2"><br>
                            No items added yet.
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-light fw-bold" id="tableFooter" style="display: none;">
                    <tr>
                        <td colspan="6" class="text-end py-3">GRAND TOTAL INVESTMENT</td>
                        <td class="text-end py-3 text-primary" id="grandTotalDisplay">IDR 0</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Action Footer -->
        <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
            <a href="{{ route('budget.index') }}" class="btn btn-light px-4 rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <div class="d-flex gap-2">
                <button type="submit" name="action" value="draft"
                    class="btn btn-outline-primary px-4 rounded-pill shadow-sm">
                    <i class="fas fa-save me-2"></i>Save as Draft
                </button>
                <button type="submit" name="action" value="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                    <i class="fas fa-paper-plane me-2"></i>Submit
                </button>
            </div>
        </div>
        <div id="itemsContainer"></div>
        </form>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        let items = [];
        let editingindex = -1;

        function toggleBreakdown() {
            const isChecked = document.getElementById('has_breakdown').checked;
            const section = document.getElementById('breakdown_section');
            const priceInput = document.getElementById('item_price_display');

            if (isChecked) {
                section.classList.remove('d-none');
                calculateBreakdownTotal(); // Recalculate if checked
            } else {
                section.classList.add('d-none');
            }
        }

        function addBreakdownRow(data = null) {
            const tbody = document.getElementById('breakdown_table_body');
            const row = document.createElement('tr');

            const name = data ? data.name : '';
            const brand = data ? data.brand : '';
            const process = data ? data.app_process : '';
            const qty = data ? data.qty : 1;
            const price = data ? data.price : 0;
            const priceDisplay = formatWithDots(price);

            row.innerHTML = `
                                                                                            <td><input type="text" class="form-control form-control-sm bd-name" placeholder="Component Name" value="${name}" onblur="this.value = toFlexibleProperCase(this.value)"></td>
                                                                                            <td><input type="text" class="form-control form-control-sm bd-brand" placeholder="Brand/Spec" value="${brand}" onblur="this.value = toFlexibleProperCase(this.value)"></td>
                                                                                            <td><input type="text" class="form-control form-control-sm bd-process" placeholder="App Process" value="${process}" onblur="this.value = toFlexibleProperCase(this.value)"></td>
                                                                                            <td><input type="number" class="form-control form-control-sm bd-qty" value="${qty}" min="1" onchange="calculateBreakdownTotal()"></td>
                                                                                            <td>
                                                                                                <input type="text" class="form-control form-control-sm bd-price-display" value="${priceDisplay}" onkeyup="formatNumber(this); calculateBreakdownTotal()">
                                                                                                <input type="hidden" class="bd-price" value="${price}">
                                                                                            </td>
                                                                                            <td class="text-center">
                                                                                                <button type="button" class="btn btn-sm text-danger" onclick="removeBreakdownRow(this)">
                                                                                                    <i class="fas fa-times"></i>
                                                                                                </button>
                                                                                            </td>
                                                                                        `;
            tbody.appendChild(row);
        }

        function removeBreakdownRow(btn) {
            btn.closest('tr').remove();
            calculateBreakdownTotal();
        }

        function calculateBreakdownTotal() {
            if (!document.getElementById('has_breakdown').checked) return;

            let total = 0;
            document.querySelectorAll('#breakdown_table_body tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.bd-qty').value) || 0;
                const price_disp = row.querySelector('.bd-price-display').value.replace(/\./g, '');
                const price = parseFloat(price_disp) || 0;
                row.querySelector('.bd-price').value = price; // Sync hidden input
                total += qty * price;
            });

            // Set main item price (unit price usually assumes 1 unit for main item if it's a bundle, 
            // but here we are summing up components. Let's assume Main Item Qty is 1 for the calculation logic,
            // or we set the "Price (Unit)" of the main item to the total sum.)

            // DECOUPLED: User inputs main item price manually.
            // document.getElementById('item_price').value = total;
            // document.getElementById('item_price_display').value = formatWithDots(total);
        }

        function updateProjectIo(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            document.getElementById('project_id').value = selectedOption.getAttribute('data-project-id');
            document.getElementById('io_number').value = selectElement.value;
        }

        function formatNumber(input) {
            let value = input.value.replace(/[^\d]/g, '');
            // Remove leading zeros
            if (value.length > 1) {
                value = value.replace(/^0+/, '');
            }
            if (value === '') value = '0';

            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            // Fix: Only update main item_price if this is the main price input
            if (input.id === 'item_price_display') {
                document.getElementById('item_price').value = value;
            }
        }

        function toFlexibleProperCase(str) {
            if (!str) return str;
            // If already all caps and has letters, keep it
            if (str === str.toUpperCase() && /[A-Z]/.test(str)) {
                return str;
            }
            // Otherwise, apply proper case
            return str.replace(/\w\S*/g, function (txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
        }

        function formatWithDots(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function addItem() {
            const data = {
                code: document.getElementById('item_code').value,
                category: document.getElementById('item_category').value,
                process: document.getElementById('item_process').value,
                name: document.getElementById('item_name').value,
                brand_spec: document.getElementById('item_brand').value,
                app_process: document.getElementById('item_app_process').value,
                qty: parseFloat(document.getElementById('item_qty').value),
                price: parseFloat(document.getElementById('item_price').value),
                condition_status: document.getElementById('item_condition_status').value,
                condition_notes: document.getElementById('item_condition_notes').value,
                target_schedule: document.getElementById('item_schedule').value,
                year: document.querySelector('[name="fiscal_year"]').value,
                breakdown: [],
                has_breakdown: document.getElementById('has_breakdown').checked
            };

            if (data.has_breakdown) {
                document.querySelectorAll('#breakdown_table_body tr').forEach(row => {
                    data.breakdown.push({
                        name: row.querySelector('.bd-name').value,
                        brand: row.querySelector('.bd-brand').value,
                        app_process: row.querySelector('.bd-process').value,
                        qty: parseFloat(row.querySelector('.bd-qty').value),
                        price: parseFloat(row.querySelector('.bd-price').value)
                    });
                });

                if (data.breakdown.length === 0) {
                    Swal.fire('Warning', 'Please add at least one breakdown component', 'warning');
                    return;
                }
            } else if (!data.name || data.qty <= 0 || data.price <= 0) {
                Swal.fire('Warning', 'Please enter Name, Qty, and Price', 'warning');
                return;
            }

            items.push(data);
            renderTable();
            resetForm();
        }

        function editItem(index) {
            const item = items[index];
            editingindex = index;

            document.getElementById('item_code').value = item.code;
            document.getElementById('item_category').value = item.category;
            document.getElementById('item_process').value = item.process;
            document.getElementById('item_name').value = item.name;
            document.getElementById('item_brand').value = item.brand_spec;
            document.getElementById('item_app_process').value = item.app_process;
            document.getElementById('item_qty').value = item.qty;
            document.getElementById('item_price').value = item.price;
            document.getElementById('item_price_display').value = formatWithDots(item.price);
            document.getElementById('item_condition_status').value = item.condition_status;
            document.getElementById('item_condition_notes').value = item.condition_notes;
            document.getElementById('item_schedule').value = item.target_schedule;

            // Handle Breakdown
            if (item.has_breakdown && item.breakdown) {
                document.getElementById('has_breakdown').checked = true;
                toggleBreakdown();
                document.getElementById('breakdown_table_body').innerHTML = '';
                item.breakdown.forEach(bd => addBreakdownRow(bd));
            } else {
                document.getElementById('has_breakdown').checked = false;
                toggleBreakdown();
                document.getElementById('breakdown_table_body').innerHTML = '';
            }

            // Toggle buttons
            document.getElementById('addItemBtn').classList.add('d-none');
            document.getElementById('updateItemBtn').classList.remove('d-none');
            document.getElementById('cancelEditBtn').classList.remove('d-none');

            // Scroll to form
            document.querySelector('.border.rounded-4').scrollIntoView({ behavior: 'smooth' });
        }

        function updateItem() {
            if (editingindex === -1) return;

            const data = {
                code: document.getElementById('item_code').value,
                category: document.getElementById('item_category').value,
                process: document.getElementById('item_process').value,
                name: document.getElementById('item_name').value,
                brand_spec: document.getElementById('item_brand').value,
                app_process: document.getElementById('item_app_process').value,
                qty: parseFloat(document.getElementById('item_qty').value),
                price: parseFloat(document.getElementById('item_price').value),
                condition_status: document.getElementById('item_condition_status').value,
                condition_notes: document.getElementById('item_condition_notes').value,
                target_schedule: document.getElementById('item_schedule').value,
                target_schedule: document.getElementById('item_schedule').value,
                year: document.querySelector('[name="fiscal_year"]').value,
                breakdown: [],
                has_breakdown: document.getElementById('has_breakdown').checked
            };

            if (data.has_breakdown) {
                document.querySelectorAll('#breakdown_table_body tr').forEach(row => {
                    data.breakdown.push({
                        name: row.querySelector('.bd-name').value,
                        brand: row.querySelector('.bd-brand').value,
                        app_process: row.querySelector('.bd-process').value,
                        qty: parseFloat(row.querySelector('.bd-qty').value),
                        price: parseFloat(row.querySelector('.bd-price').value)
                    });
                });
                if (data.breakdown.length === 0) {
                    Swal.fire('Warning', 'Please add at least one breakdown component', 'warning');
                    return;
                }
            } else if (!data.name || data.qty <= 0 || data.price <= 0) {
                Swal.fire('Warning', 'Please enter Name, Qty, and Price', 'warning');
                return;
            }

            items[editingindex] = data;
            renderTable();
            cancelEdit();
        }

        function cancelEdit() {
            resetForm();
            editingindex = -1;
            document.getElementById('addItemBtn').classList.remove('d-none');
            document.getElementById('updateItemBtn').classList.add('d-none');
            document.getElementById('cancelEditBtn').classList.add('d-none');
        }

        function resetForm() {
            document.getElementById('item_code').value = '';
            document.getElementById('item_name').value = '';
            document.getElementById('item_brand').value = '';
            document.getElementById('item_app_process').value = '';
            document.getElementById('item_qty').value = '1';
            document.getElementById('item_price').value = '0';
            document.getElementById('item_price_display').value = '0';
            document.getElementById('item_condition_status').value = 'Ready';
            document.getElementById('item_condition_notes').value = '';
            document.getElementById('item_schedule').value = '';

            // Sub-item reset
            document.getElementById('has_breakdown').checked = false;
            toggleBreakdown();
            document.getElementById('breakdown_table_body').innerHTML = '';
        }
        function removeItem(index) {
            items.splice(index, 1);
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('itemsTable');
            const footer = document.getElementById('tableFooter');
            const form = document.getElementById('budgetForm');

            // Find or create itemsContainer inside the form
            let container = document.getElementById('itemsContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'itemsContainer';
                form.appendChild(container);
                console.log('itemsContainer created dynamically');
            }

            if (items.length === 0) {
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="9" class="text-center py-4 text-muted small"><img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="48" class="opacity-25 mb-2"><br>No items added yet.</td></tr>';
                footer.style.display = 'none';
                container.innerHTML = '';
                return;
            }

            tbody.innerHTML = '';
            container.innerHTML = '';
            footer.style.display = 'table-footer-group';

            let grandTotal = 0;

            // Group items for display
            const grouped = {};
            items.forEach((item, index) => {
                const key = item.code
                    ? `${item.code}. ${item.category} - ${item.process}`
                    : `${item.category} - ${item.process}`;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push({ ...item, originalIndex: index });
            });

            let grandTotal = 0;
            for (const key in grouped) {
                let originalIndexCounter = 1;
                // Category/Process Header Row
                const headerRow = document.createElement('tr');
                headerRow.className = 'table-light fw-bold';
                headerRow.innerHTML = `<td colspan="9" class="ps-3 text-indigo"><i class="fas fa-folder-open me-2"></i>${key}</td>`;
                tbody.appendChild(headerRow);

                grouped[key].forEach((item) => {
                    const total = item.qty * item.price;
                    grandTotal += total;
                    const index = item.originalIndex;

                    const rowClass = item.has_breakdown ? 'fw-bold bg-white' : '';
                    const tr = document.createElement('tr');
                    tr.className = `align-middle ${rowClass}`;
                    tr.innerHTML = `
                                                                            <td class="text-center">${originalIndexCounter++}</td>
                                                                                <td>${item.name}
                                                                            ${item.has_breakdown ? '<div class="small text-muted fst-italic mt-1">Include:</div>' : ''}
                                                                        </td>
                                                                        <!-- Removed Model Column -->
                                                                        <td>${item.brand_spec || '-'}</td>
                                                                                <td class="text-center">${item.qty} ${item.uom || 'Unit'}</td>
                                                                                <td>${item.app_process || '-'}</td>
                                                                                <td>
                                                                                    <span class="badge ${item.condition_status === 'Ready' ? 'bg-success' : 'bg-warning text-dark'}">
                                                                                        ${item.condition_status}
                                                                                    </span>
                                                                                    ${item.condition_notes ? `<div class="small text-muted mt-1">${item.condition_notes}</div>` : ''}
                                                                                </td>
                                                                                <td class="text-end fw-bold">IDR ${formatWithDots(item.qty * item.price)}</td>
                                                                                <td class="text-center">${item.target_schedule || '-'}</td>
                                                                                <td class="text-center">
                                                                                    <button type="button" class="btn btn-sm btn-outline-warning border-0 rounded-circle me-1" onclick="editItem(${index})">
                                                                                        <i class="fas fa-pencil-alt"></i>
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="removeItem(${index})">
                                                                                        <i class="fas fa-trash-alt"></i>
                                                                                    </button>
                                                                                </td>
                                                                            `;
                    tbody.appendChild(tr);

                    // Hidden Inputs - placed in itemsContainer (inside form but outside table)
                    let html = `
                                                                                <input type="hidden" name="items[${index}][code]" value="${item.code || ''}">
                                                                                <input type="hidden" name="items[${index}][category]" value="${item.category || ''}">
                                                                                <input type="hidden" name="items[${index}][process]" value="${item.process || ''}">
                                                                                <input type="hidden" name="items[${index}][name]" value="${item.name}">
                                                                        <input type="hidden" name="items[${index}][brand_spec]" value="${item.brand_spec || ''}">
                                                                                <input type="hidden" name="items[${index}][application_process]" value="${item.app_process || ''}">
                                                                                <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                                                                                <input type="hidden" name="items[${index}][price]" value="${item.price}">
                                                                                <input type="hidden" name="items[${index}][condition_status]" value="${item.condition_status}">
                                                                                <input type="hidden" name="items[${index}][condition_notes]" value="${item.condition_notes || ''}">
                                                                                <input type="hidden" name="items[${index}][target_schedule]" value="${item.target_schedule || ''}">
                                                                                <input type="hidden" name="items[${index}][year]" value="${item.year || ''}">
                                                                            `;

                    // Breakdown hidden inputs
                    if (item.has_breakdown && item.breakdown) {
                        item.breakdown.forEach((bd, i) => {
                            html += `
                                                                                        <input type="hidden" name="items[${index}][breakdown][${i}][name]" value="${bd.name}">
                                                                                        <input type="hidden" name="items[${index}][breakdown][${i}][brand]" value="${bd.brand || ''}">
                                                                                        <input type="hidden" name="items[${index}][breakdown][${i}][app_process]" value="${bd.app_process || ''}">
                                                                                        <input type="hidden" name="items[${index}][breakdown][${i}][qty]" value="${bd.qty}">
                                                                                        <input type="hidden" name="items[${index}][breakdown][${i}][price]" value="${bd.price}">
                                                                                    `;
                        });
                    }

                    container.insertAdjacentHTML('beforeend', html);

                    // Render Sub-items Rows
                    if (item.has_breakdown && item.breakdown) {
                        item.breakdown.forEach(sub => {
                            const subTr = document.createElement('tr');
                            subTr.className = 'align-middle small text-muted';
                            subTr.innerHTML = `
                                                                                        <td></td>
                                                                                        <td class="ps-4">- ${sub.name}</td>
                                                                                        <td>${sub.brand || '-'}</td>
                                                                                        <td class="text-center">${sub.qty} Unit</td>
                                                                                        <td>${sub.app_process || '-'}</td>
                                                                                        <td><span class="badge bg-secondary">Detail</span></td>
                                                                                        <td class="text-end">IDR ${formatWithDots(sub.qty * sub.price)}</td>
                                                                                        <td></td>
                                                                                        <td></td>
                                                                                    `;
                            tbody.appendChild(subTr);
                        });
                    }
                });
            }

            document.getElementById('grandTotalDisplay').textContent = 'IDR ' + formatWithDots(grandTotal);
        }

        // Form submit handler: inject all item hidden inputs from JS array into form
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('budgetForm');
            if (form) {
                // Track which button was clicked
                let clickedAction = 'draft';
                form.querySelectorAll('button[name="action"]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        clickedAction = this.value;
                    });
                });
                // Also handle external buttons with form="budgetForm"
                document.querySelectorAll('button[form="budgetForm"][name="action"]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        clickedAction = this.value;
                    });
                });

                form.addEventListener('submit', function (e) {
                    // Inject action as hidden input to ensure it reaches the server
                    let actionInput = form.querySelector('input[name="action"]');
                    if (!actionInput) {
                        actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        form.appendChild(actionInput);
                    }
                    actionInput.value = clickedAction;

                    // Remove any existing item inputs first
                    form.querySelectorAll('input[name^="items["]').forEach(el => el.remove());

                    // Create hidden inputs from JS items array
                    items.forEach((item, index) => {
                        const fields = {
                            'code': item.code || '',
                            'category': item.category || '',
                            'process': item.process || '',
                            'name': item.name || '',
                            'brand_spec': item.brand_spec || '',
                            'application_process': item.app_process || '',
                            'qty': item.qty || 1,
                            'price': item.price || 0,
                            'condition_status': item.condition_status || '',
                            'condition_notes': item.condition_notes || '',
                            'target_schedule': item.target_schedule || '',
                            'year': item.year || ''
                        };

                        for (const [key, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `items[${index}][${key}]`;
                            input.value = value;
                            form.appendChild(input);
                        }

                        // Add breakdown sub-items
                        if (item.has_breakdown && item.breakdown) {
                            item.breakdown.forEach((bd, i) => {
                                const bdFields = {
                                    'name': bd.name || '',
                                    'brand': bd.brand || '',
                                    'app_process': bd.app_process || '',
                                    'qty': bd.qty || 1,
                                    'price': bd.price || 0
                                };
                                for (const [key, value] of Object.entries(bdFields)) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = `items[${index}][breakdown][${i}][${key}]`;
                                    input.value = value;
                                    form.appendChild(input);
                                }
                            });
                        }
                    });

                    console.log('Injected ' + items.length + ' items into form');
                });
            }
        });

    </script>
    <style>
        .text-indigo {
            color: #1e3a5f;
        }

        .btn-indigo {
            background: #1e3a5f;
            color: white;
            border: none;
        }

        .btn-indigo:hover {
            background: #162c4a;
            color: white;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
        }

        .bg-primary {
            background-color: #1e3a5f !important;
        }

        .table thead th {
            border: none;
        }
    </style>
@endpush