@extends('layouts.app')

@section('content')
    <style>
        .form-header-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 0.35rem;
            letter-spacing: 0.5px;
        }

        .pr-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            border-left: 4px solid #10b981;
            padding-left: 10px;
            margin-bottom: 20px;
        }

        .form-control-sm,
        .form-select-sm {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
            border-color: #e2e8f0;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .btn-dark-custom {
            background-color: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }

        .btn-dark-custom:hover {
            background-color: #0f172a;
            border-color: #0f172a;
        }

        .table-custom th {
            background-color: #f8fafc;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom td {
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .sidebar-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .footer-action-bar {
            position: fixed;
            bottom: 0;
            left: 280px;
            width: calc(100% - 280px);
            z-index: 1050;
            transition: all 0.3s ease-in-out;
        }

        @media (max-width: 991.98px) {
            .footer-action-bar {
                left: 0;
                width: 100%;
            }
        }
    </style>

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-1" style="color: #1e293b;">New Purchase Request</h4>
            <div class="text-primary small fw-semibold">PR-{{ date('Y') }}-XXXX <span
                    class="text-muted fw-normal ms-2">(Auto-generated after save)</span></div>
        </div>
        <div class="col-auto">
            <button type="button" class="btn-close" onclick="history.back()"></button>
        </div>
    </div>

    <form action="{{ route('pr.store') }}" method="POST" id="formCreatePR" class="pb-5">
        @csrf

        <div class="row">
            <!-- LEFT COLUMN: FORM -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 8px;">

                    <!-- General Info Section -->
                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <label class="form-header-label">Department <span class="text-danger">*</span></label>
                            <select name="department" class="form-select form-select-sm" required>
                                <option value="">Select Dept</option>
                                @foreach($depts as $d)
                                    <option value="{{ $d->dept_name }}">{{ $d->dept_code }} | {{ $d->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-header-label">IO Number <span class="text-danger">*</span></label>
                            <select name="io_number" class="form-select form-select-sm" required>
                                <option value="">Select IO</option>
                                @foreach($ios as $io)
                                    <option value="{{ $io->io_number }}">{{ $io->io_number }} - {{ $io->project_name }} (Sisa:
                                        Rp {{ number_format($io->remaining, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-header-label">Cost Center <span class="text-danger">*</span></label>
                            <select name="cost_center" class="form-select form-select-sm" required>
                                <option value="">Select Cost Center</option>
                                @foreach($ccs as $cc)
                                    <option value="{{ $cc->cc_code }}">{{ $cc->cc_code }} - {{ $cc->cc_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-header-label">Business Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">Select Category</option>
                                @foreach($cats as $c)
                                    <option value="{{ $c->category_name }}">{{ $c->category_code }} | {{ $c->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-header-label">Plant <span class="text-danger">*</span></label>
                            <select name="plant" class="form-select form-select-sm" required>
                                <option value="">Select Plant</option>
                                @foreach($plants as $p)
                                    <option value="{{ $p->plant_code }}">{{ $p->plant_code }} - {{ $p->plant_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-header-label">Storage Location</label>
                            <select name="storage_location" class="form-select form-select-sm">
                                <option value="">Select S.Loc</option>
                                @foreach($slocs as $sloc)
                                    <option value="{{ $sloc->sloc }}">{{ $sloc->sloc }} | {{ $sloc->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-header-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control form-control-sm">
                        </div>
                    </div>

                    <!-- PR Items Section -->
                    <div class="d-flex align-items-center mb-3 mt-4">
                        <div
                            style="width: 4px; height: 20px; background-color: #10b981; margin-right: 10px; border-radius: 2px;">
                        </div>
                        <h6 class="fw-bold m-0" style="color: #0f172a;">PR Items</h6>
                    </div>

                    <!-- Add Item Form -->
                    <div class="card p-4 mb-4"
                        style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div class="d-flex flex-nowrap overflow-auto gap-2 pb-3 align-items-end" style="width: 100%;">
                            <div style="min-width: 350px;">
                                <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Budget
                                    Link</label>
                                <select id="newItemBudget" class="form-select form-select-sm">
                                    <option value="">-- No Link --</option>
                                    @foreach($budget_items as $b)
                                        <option value="{{ $b->id }}" data-io="{{ $b->io_number }}"
                                            data-price="{{ $b->estimated_price }}" data-remaining="{{ $b->remaining }}">
                                            {{ $b->io_number }} - Poin {{ $b->item_code ?? '-' }} ({{ $b->item_name }})
                                            @if($b->model) | {{ $b->model }} @endif | Sisa: Rp
                                            {{ number_format($b->remaining, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="min-width: 150px;">
                                <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">G/L
                                    Account</label>
                                <input type="text" id="newItemGl" class="form-control form-control-sm" placeholder="G/L">
                            </div>
                            <div style="min-width: 150px;">
                                <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Asset No
                                    (AUC)</label>
                                <input type="text" id="newItemAuc" class="form-control form-control-sm" placeholder="AUC">
                            </div>
                            <div style="min-width: 200px;">
                                <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Item ID
                                    (Master)</label>
                                <select id="newItemMaster" class="form-select form-select-sm">
                                    <option value="">-- Master Item --</option>
                                    @foreach($m_items as $m)
                                        <option value="{{ $m->item_code }}" data-name="{{ $m->item_name }}">{{ $m->item_code }}
                                            - {{ $m->item_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="min-width: 250px;">
                                <label class="form-header-label mb-1 text-muted"
                                    style="font-size: 0.65rem;">Description</label>
                                <input type="text" id="newItemDesc" class="form-control form-control-sm"
                                    placeholder="Description">
                            </div>
                            <div style="min-width: 80px;">
                                <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Qty</label>
                                <input type="number" id="newItemQty" class="form-control form-control-sm" value="1" min="1">
                            </div>
                            <div style="min-width: 80px;">
                                <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">UoM</label>
                                <input type="text" id="newItemUom" class="form-control form-control-sm" value="Unit">
                            </div>
                            <div style="min-width: 200px;">
                                <label class="form-header-label mb-1 text-muted"
                                    style="font-size: 0.65rem;">Cost/Unit</label>
                                <div class="input-group input-group-sm">
                                    <select id="newItemCurr" class="form-select" style="width: 80px; flex: 0 0 auto;">
                                        <option value="IDR">IDR</option>
                                        <option value="USD">USD</option>
                                    </select>
                                    <input type="text" id="newItemCost" class="form-control" placeholder="0"
                                        oninput="formatPriceInput(this)">
                                </div>
                            </div>
                            <div style="min-width: 200px;">
                                <label class="form-header-label mb-1 text-muted"
                                    style="font-size: 0.65rem;">Peruntukan</label>
                                <input type="text" id="newItemPurpose" class="form-control form-control-sm"
                                    placeholder="Purpose of this item">
                            </div>
                            <div style="min-width: 150px;">
                                <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">PIC</label>
                                <input type="text" id="newItemPic" class="form-control form-control-sm"
                                    placeholder="Person In Charge">
                            </div>
                            <div style="min-width: 120px;">
                                <button type="button" class="btn btn-dark-custom btn-sm w-100" onclick="addItem()"
                                    style="background-color: #1e293b; color: white; height: 31px;">
                                    <i class="fas fa-plus me-1"></i> Add Item
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive bg-white rounded border" style="border-color: #f1f5f9;">
                        <table class="table table-custom mb-0" id="tableItems">
                            <thead>
                                <tr>
                                    <th class="ps-4">A11 Link</th>
                                    <th>G/L Account</th>
                                    <th>AUC</th>
                                    <th>Item ID</th>
                                    <th>Description</th>
                                    <th>Peruntukan</th>
                                    <th>PIC</th>
                                    <th>Qty</th>
                                    <th>UoM</th>
                                    <th>Cost/Unit</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="emptyRow">
                                    <td colspan="12" class="text-center py-5 text-muted fst-italic">
                                        No items added yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN: SIDEBAR -->
            <div class="col-lg-12">
                <div class="sidebar-card p-4 mb-4">
                    <h6 class="fw-bold mb-4" style="color: #1e3a5f;"><i class="fas fa-tasks me-2"></i>Approval History &
                        Status</h6>

                    <div class="position-relative">
                        <div class="position-absolute start-0 w-100"
                            style="height: 2px; background: #e2e8f0; top: 12px; z-index: 0;"></div>
                        <div class="row position-relative" style="z-index: 1;">
                            <div class="col text-center" style="min-width: 150px;">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                    style="width: 24px; height: 24px;">
                                    <i class="fas fa-pencil-alt" style="font-size: 12px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Draft</h6>
                                <div class="badge bg-secondary mb-2">Draft</div>
                                <p class="small text-muted mb-0"><i class="fas fa-user me-1"></i> - </p>
                            </div>
                            <div class="col text-center" style="min-width: 150px;">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                    style="width: 24px; height: 24px;">
                                    <i class="fas fa-circle" style="font-size: 12px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Dept Head</h6>
                                <div class="badge bg-light text-muted border mb-2">Pending</div>
                                <p class="small text-muted mb-0"><i class="fas fa-user me-1"></i> - </p>
                            </div>
                            <div class="col text-center" style="min-width: 150px;">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                    style="width: 24px; height: 24px;">
                                    <i class="fas fa-circle" style="font-size: 12px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Finance</h6>
                                <div class="badge bg-light text-muted border mb-2">Pending</div>
                                <p class="small text-muted mb-0"><i class="fas fa-user me-1"></i> - </p>
                            </div>
                            <div class="col text-center" style="min-width: 150px;">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                    style="width: 24px; height: 24px;">
                                    <i class="fas fa-circle" style="font-size: 12px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Division Head</h6>
                                <div class="badge bg-light text-muted border mb-2">Pending</div>
                                <p class="small text-muted mb-0"><i class="fas fa-user me-1"></i> - </p>
                            </div>
                            <div class="col text-center" style="min-width: 150px;">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                    style="width: 24px; height: 24px;">
                                    <i class="fas fa-circle" style="font-size: 12px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Purchasing</h6>
                                <div class="badge bg-light text-muted border mb-2">Pending</div>
                                <p class="small text-muted mb-0"><i class="fas fa-user me-1"></i> - </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Action Bar -->
        <div class="bg-white border-top p-3 shadow-lg footer-action-bar">
            <div class="d-flex justify-content-between align-items-center px-4">
                <span class="text-danger small fw-semibold">
                    <i class="fas fa-info-circle me-1"></i> * Fill all required fields before saving.
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light fw-bold text-secondary border px-4"
                        onclick="history.back()"><i class="fas fa-times me-1"></i> Close</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Create
                        Request</button>
                </div>
            </div>
        </div>

        <!-- Spacer for fixed footer -->
        <div style="height: 80px;"></div>

    </form>
@endsection

@push('scripts')
    <script>
        const tableBody = document.querySelector('#tableItems tbody');
        const emptyRow = document.getElementById('emptyRow');
        let itemCount = 0;

        function addItem() {
            const mMaster = document.getElementById('newItemMaster');
            const mBudget = document.getElementById('newItemBudget');
            const desc = document.getElementById('newItemDesc');
            const purpose = document.getElementById('newItemPurpose');
            const pic = document.getElementById('newItemPic');
            const glAccount = document.getElementById('newItemGl');
            const aucField = document.getElementById('newItemAuc');
            const qty = document.getElementById('newItemQty');
            const uom = document.getElementById('newItemUom');
            const cost = document.getElementById('newItemCost');

            if (!desc.value && mMaster.value === '') {
                alert("Please select a Master Item or enter a Description.");
                return;
            }
            if (qty.value <= 0) {
                alert("Qty must be greater than 0.");
                return;
            }

            if (emptyRow) emptyRow.style.display = 'none';

            let displayDesc = desc.value;
            if (mMaster.value) {
                const masterText = mMaster.options[mMaster.selectedIndex].dataset.name;
                if (!displayDesc) displayDesc = masterText;
            }

            let itemCode = mMaster.value || '-';
            let budgetId = mBudget.value || 0;
            let dispPurpose = purpose.value || '-';
            let dispPic = pic.value || '-';
            let dispGl = glAccount.value || '';
            let dispAuc = aucField.value || '';
            let q = parseFloat(qty.value);
            let c = parseFloat(cost.value.replace(/\./g, '').replace(/,/g, '.')) || 0;
            let t = q * c;

            itemCount++;
            const tr = document.createElement('tr');
            tr.id = `row-${itemCount}`;
            tr.innerHTML = `
                                                    <td class="ps-4">
                                                        <div class="text-dark small fw-bold">${mBudget.value ? mBudget.options[mBudget.selectedIndex].text.split(' | ')[0] : '-'}</div>
                                                    </td>
                                                    <td>
                                                        <div class="text-dark small">${dispGl || '-'}</div>
                                                        <input type="hidden" name="items[${itemCount}][gl_account]" value="${dispGl}">
                                                    </td>
                                                    <td>
                                                        <div class="text-dark small">${dispAuc || '-'}</div>
                                                        <input type="hidden" name="items[${itemCount}][asset_no]" value="${dispAuc}">
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-dark small">${itemCode}</span>
                                                        <input type="hidden" name="items[${itemCount}][master_item_code]" value="${itemCode}">
                                                        <input type="hidden" name="items[${itemCount}][budget_item_id]" value="${budgetId}">
                                                    </td>
                                                    <td>
                                                        <div class="text-dark small">${displayDesc}</div>
                                                        ${budgetId ? '<div class="text-success small fst-italic mt-1"><i class="fas fa-check-circle me-1" style="font-size: 10px;"></i>A11 Linked</div>' : ''}
                                                        <input type="hidden" name="items[${itemCount}][description]" value="${displayDesc}">
                                                    </td>
                                                    <td>
                                                        <div class="text-dark small">${dispPurpose}</div>
                                                        <input type="hidden" name="items[${itemCount}][purpose]" value="${dispPurpose}">
                                                    </td>
                                                     <td>
                                                        <div class="text-dark small">${dispPic}</div>
                                                        <input type="hidden" name="items[${itemCount}][pic]" value="${dispPic}">
                                                    </td>
                                                    <td>
                                                        <div class="text-dark small">${q}</div>
                                                        <input type="hidden" name="items[${itemCount}][qty]" value="${q}">
                                                    </td>
                                                    <td>
                                                        <div class="text-dark small">${uom.value}</div>
                                                        <input type="hidden" name="items[${itemCount}][uom]" value="${uom.value}">
                                                    </td>
                                                    <td>
                                                        <span class="small text-muted">${formatMoney(c)}</span>
                                                        <input type="hidden" name="items[${itemCount}][estimated_price]" value="${c}">
                                                    </td>
                                                    <td class="fw-bold text-dark small">${formatMoney(t)}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(${itemCount})" title="Remove"><i class="fas fa-trash-alt"></i></button>
                                                    </td>
                                                `;

            tableBody.appendChild(tr);
            desc.value = '';
            purpose.value = '';
            pic.value = '';
            glAccount.value = '';
            aucField.value = '';
            qty.value = 1;
            cost.value = '';
            mMaster.value = '';
            mBudget.value = '';
        }

        function removeRow(id) {
            document.getElementById(`row-${id}`).remove();
            // Check if only emptyRow is left or if table is empty
            const rows = tableBody.querySelectorAll('tr:not(#emptyRow)');
            if (rows.length === 0) {
                if (emptyRow) emptyRow.style.display = 'table-row';
            }
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        }

        function formatPriceInput(input) {
            // Strip non-numeric chars
            let value = input.value.replace(/\D/g, '');
            if (value === "") {
                input.value = "";
                return;
            }
            // Format as 15.000.000
            input.value = new Intl.NumberFormat('id-ID').format(value);
        }

        document.getElementById('newItemBudget').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const p = selectedOption ? selectedOption.dataset.price : '';
            if (p) {
                // Format initial value
                document.getElementById('newItemCost').value = new Intl.NumberFormat('id-ID').format(p);
            } else {
                document.getElementById('newItemCost').value = '';
            }
        });

        // Filter budget links by selected IO Number
        const ioNumberSelect = document.querySelector('select[name="io_number"]');
        if (ioNumberSelect) {
            ioNumberSelect.addEventListener('change', function () {
                const selectedIo = this.value;
                const budgetSelect = document.getElementById('newItemBudget');

                // Reset selection
                budgetSelect.value = "";
                document.getElementById('newItemCost').value = "";

                Array.from(budgetSelect.options).forEach(opt => {
                    if (opt.value === "") {
                        // Keep 'No Link' visible
                        opt.hidden = false;
                        opt.disabled = false;
                    } else if (opt.dataset.io === selectedIo) {
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        opt.hidden = true;
                        opt.disabled = true;
                    }
                });
            });
            // Initial trigger
            ioNumberSelect.dispatchEvent(new Event('change'));
        }
    </script>
@endpush