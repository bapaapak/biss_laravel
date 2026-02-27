@extends('layouts.app')

@section('content')
    <style>
        .tab-pills .nav-link {
            border-radius: 20px;
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
            background: #f1f5f9;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .tab-pills .nav-link.active {
            background: #0d6efd;
            color: #fff;
        }

        .tab-pills .nav-link .badge {
            font-size: 0.65rem;
            margin-left: 5px;
        }

        .table-admin th {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            background: #f8fafc;
        }

        .table-admin td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .badge-admin {
            background-color: #dc3545;
            color: white;
        }

        .badge-user {
            background-color: #0dcaf0;
            color: #0c4a6e;
        }
    </style>

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">System Administration</h3>
        <p class="text-muted mb-0">Manage users, roles, and master data configurations.</p>
    </div>



    <!-- Tab Pills Navigation -->
    <div class="mb-4">
        <ul class="nav tab-pills flex-wrap" id="adminTabs" role="tablist">
            <!-- Users Tab Removed (Moved to RBAC) -->
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#items"><i class="fas fa-box me-1"></i> Master Items
                    <span class="badge bg-secondary">{{ count($items) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#depts"><i class="fas fa-building me-1"></i> Departments
                    <span class="badge bg-secondary">{{ count($depts) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#cats"><i class="fas fa-tags me-1"></i> Categories <span
                        class="badge bg-secondary">{{ count($cats) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#ios"><i class="fas fa-hashtag me-1"></i> IO Numbers <span
                        class="badge bg-secondary">{{ count($ios) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#ccs"><i class="fas fa-sitemap me-1"></i> Cost Centers <span
                        class="badge bg-secondary">{{ count($ccs) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#projects"><i class="fas fa-folder me-1"></i> Projects <span
                        class="badge bg-secondary">{{ count($projects) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#plants"><i class="fas fa-industry me-1"></i> Plants <span
                        class="badge bg-secondary">{{ count($plants) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#suppliers"><i class="fas fa-truck me-1"></i> Suppliers <span
                        class="badge bg-secondary">{{ count($suppliers) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#customers"><i class="fas fa-user-tie me-1"></i> Customers
                    <span class="badge bg-secondary">{{ count($customers) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#currencies"><i class="fas fa-dollar-sign me-1"></i>
                    Currencies <span class="badge bg-secondary">{{ count($currencies) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#assets"><i class="fas fa-barcode me-1"></i> Assets (AUC)
                    <span class="badge bg-secondary">{{ count($assets) }}</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#slocs"><i class="fas fa-warehouse me-1"></i> S.Loc <span
                        class="badge bg-secondary">{{ count($storageLocations) }}</span></a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- USERS TAB REMOVED -->

        <!-- MASTER ITEMS TAB -->
        <div class="tab-pane fade show active" id="items">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Master Items</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal"><i
                            class="fas fa-plus me-1"></i> Add Item</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Item Code</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $i)
                                <tr>
                                    <td class="ps-4 fw-bold font-monospace">{{ $i->item_code }}</td>
                                    <td>{{ $i->item_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $i->category }}</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editItem({{ $i->id }}, '{{ $i->item_code }}', '{{ addslashes($i->item_name) }}', '{{ addslashes($i->category) }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form action="{{ route('admin.master.destroy', ['type' => 'item', 'id' => $i->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DEPARTMENTS TAB -->
        <div class="tab-pane fade" id="depts">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Departments</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Dept Code</th>
                                <th>Department Name</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($depts as $d)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $d->dept_code }}</td>
                                    <td>{{ $d->dept_name }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editDept({{ $d->id }}, '{{ $d->dept_code }}', '{{ addslashes($d->dept_name) }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form
                                                action="{{ route('admin.master.destroy', ['type' => 'department', 'id' => $d->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CATEGORIES TAB -->
        <div class="tab-pane fade" id="cats">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Categories</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCatModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cats as $c)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $c->category_code }}</td>
                                    <td>{{ $c->category_name }}</td>
                                    <td>{{ $c->description ?? '-' }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editCat({{ $c->id }}, '{{ $c->category_code }}', '{{ addslashes($c->category_name) }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form
                                                action="{{ route('admin.master.destroy', ['type' => 'category', 'id' => $c->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- IO NUMBERS TAB -->
        <div class="tab-pane fade" id="ios">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">IO Numbers</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addIOModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">IO Number</th>
                                <th>Project</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ios as $io)
                                <tr>
                                    <td class="ps-4 fw-bold font-monospace">{{ $io->io_number }}</td>
                                    <td>{{ $io->project ?? '-' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $io->category }}</span></td>
                                    <td>{{ $io->description }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editIO({{ $io->id }}, '{{ $io->io_number }}', '{{ addslashes($io->description) }}', '{{ addslashes($io->category) }}', '{{ $io->project ?? '' }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form action="{{ route('admin.master.destroy', ['type' => 'io', 'id' => $io->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- COST CENTERS TAB -->
        <div class="tab-pane fade" id="ccs">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Cost Centers</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCCModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">CC Code</th>
                                <th>CC Name</th>
                                <th>Department</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ccs as $cc)
                                <tr>
                                    <td class="ps-4 fw-bold font-monospace">{{ $cc->cc_code }}</td>
                                    <td>{{ $cc->cc_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $cc->department }}</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editCC({{ $cc->id }}, '{{ $cc->cc_code }}', '{{ addslashes($cc->cc_name) }}', '{{ $cc->department }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form action="{{ route('admin.master.destroy', ['type' => 'cc', 'id' => $cc->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PROJECTS TAB -->
        <div class="tab-pane fade" id="projects">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Projects</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Code</th>
                                <th>Name</th>
                                <th>Categories</th>
                                <th>Description</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $p)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $p->project_code }}</td>
                                    <td>{{ $p->project_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $p->category ?? '-' }}</span></td>
                                    <td>{{ $p->description ?? '-' }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editProject({{ $p->id }}, '{{ $p->project_code }}', '{{ addslashes($p->project_name) }}', '{{ addslashes($p->category ?? '') }}', '{{ addslashes($p->description ?? '') }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form
                                                action="{{ route('admin.master.destroy', ['type' => 'project', 'id' => $p->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PLANTS TAB -->
        <div class="tab-pane fade" id="plants">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Plants</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPlantModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Plant Code</th>
                                <th>Plant Name</th>
                                <th>Location</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plants as $p)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $p->plant_code }}</td>
                                    <td>{{ $p->plant_name }}</td>
                                    <td>{{ $p->location }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editPlant({{ $p->id }}, '{{ $p->plant_code }}', '{{ addslashes($p->plant_name) }}', '{{ addslashes($p->location ?? '') }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form action="{{ route('admin.master.destroy', ['type' => 'plant', 'id' => $p->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SUPPLIERS TAB -->
        <div class="tab-pane fade" id="suppliers">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Suppliers</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Code</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $s)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $s->supplier_code ?? '-' }}</td>
                                    <td>{{ $s->supplier_name }}</td>
                                    <td>{{ $s->contact ?? '-' }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editSupplier({{ $s->id }}, '{{ $s->supplier_code }}', '{{ addslashes($s->supplier_name) }}', '{{ addslashes($s->contact ?? '') }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form
                                                action="{{ route('admin.master.destroy', ['type' => 'supplier', 'id' => $s->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No suppliers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CUSTOMERS TAB -->
        <div class="tab-pane fade" id="customers">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Customers</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Code</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $c)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $c->customer_code ?? '-' }}</td>
                                    <td>{{ $c->customer_name }}</td>
                                    <td>{{ $c->contact ?? '-' }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editCustomer({{ $c->id }}, '{{ $c->customer_code }}', '{{ addslashes($c->customer_name) }}', '{{ addslashes($c->contact ?? '') }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form
                                                action="{{ route('admin.master.destroy', ['type' => 'customer', 'id' => $c->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No customers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CURRENCIES TAB -->
        <div class="tab-pane fade" id="currencies">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Currencies</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCurrencyModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Code</th>
                                <th>Name</th>
                                <th>Symbol</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currencies as $c)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $c->currency_code }}</td>
                                    <td>{{ $c->currency_name ?? '-' }}</td>
                                    <td>{{ $c->symbol ?? '-' }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editCurrency({{ $c->id }}, '{{ $c->currency_code }}', '{{ addslashes($c->currency_name) }}', '{{ $c->symbol }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form
                                                action="{{ route('admin.master.destroy', ['type' => 'currency', 'id' => $c->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No currencies found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ASSETS TAB -->
        <div class="tab-pane fade" id="assets">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Assets (AUC)</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal"><i
                            class="fas fa-plus me-1"></i> Add</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Asset No</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $a)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $a->asset_no }}</td>
                                    <td>{{ $a->asset_name ?? '-' }}</td>
                                    <td>{{ $a->description ?? '-' }}</td>
                                    <td><span
                                            class="badge bg-{{ $a->status == 'Active' ? 'success' : 'secondary' }}">{{ $a->status }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editAsset({{ $a->id }}, '{{ $a->asset_no }}', '{{ addslashes($a->asset_name) }}', '{{ addslashes($a->description) }}', '{{ $a->status }}')"><i
                                                class="fas fa-pen"></i></button>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <form action="{{ route('admin.master.destroy', ['type' => 'asset', 'id' => $a->id]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No assets found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SLOCS TAB -->
    <div class="tab-pane fade" id="slocs">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">Storage Locations</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSlocModal"><i
                        class="fas fa-plus me-1"></i> Add</button>
            </div>
            <div class="table-responsive">
                <table class="table table-admin table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">S.Loc</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($storageLocations as $sloc)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $sloc->sloc }}</td>
                                <td>{{ $sloc->description ?? '-' }}</td>
                                <td><span
                                        class="badge bg-{{ $sloc->status == 'Active' ? 'success' : 'secondary' }}">{{ $sloc->status }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                        onclick="editSloc({{ $sloc->id }}, '{{ $sloc->sloc }}', '{{ addslashes($sloc->description) }}', '{{ $sloc->status }}')"><i
                                            class="fas fa-pen"></i></button>
                                    @if(auth()->user()->role === 'Super Admin')
                                        <form
                                            action="{{ route('admin.master.destroy', ['type' => 'storage_location', 'id' => $sloc->id]) }}"
                                            method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No storage locations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CREATE MODALS -->

    <!-- Add User Modal Removed -->

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'item') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Master Item</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Item Code</label><input type="text" name="item_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Item Name</label><input type="text" name="item_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Category</label><input type="text" name="category"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Dept Modal -->
    <div class="modal fade" id="addDeptModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'department') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Department</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Dept Code</label><input type="text" name="dept_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Dept Name</label><input type="text" name="dept_name"
                                class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCatModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'category') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" name="category_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="category_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add IO Modal -->
    <div class="modal fade" id="addIOModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'io') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add IO Number</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">IO Number</label>
                            <input type="text" name="io_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" id="addIODesc" name="description" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project</label>
                            <select id="addIOProject" name="project" class="form-select">
                                <option value="">Select Project</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->project_code }}">P-{{ date('Y') }}-{{ $p->id }} -
                                        {{ $p->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select id="addIOCat" name="category" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($cats as $cat)
                                    <option value="{{ $cat->category_code }}">{{ $cat->category_code }} -
                                        {{ $cat->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add CC Modal -->
    <div class="modal fade" id="addCCModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'cc') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Cost Center</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">CC Code</label><input type="text" name="cc_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">CC Name</label><input type="text" name="cc_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Department</label><select name="department"
                                class="form-select">@foreach($depts as $d)<option value="{{ $d->dept_name }}">
                                    {{ $d->dept_code }} | {{ $d->dept_name }}
                                </option>@endforeach</select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'project') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Project</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Project Code</label><input type="text"
                                name="project_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Project Name</label><input type="text"
                                name="project_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Category</label><select name="category"
                                class="form-select">
                                <option value="">Select Category</option>@foreach($cats as $cat)<option
                                    value="{{ $cat->category_code }}">{{ $cat->category_code }} - {{ $cat->category_name }}
                                </option>@endforeach
                            </select></div>
                        <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Plant Modal -->
    <div class="modal fade" id="addPlantModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'plant') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Plant</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Plant Code</label><input type="text" name="plant_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Plant Name</label><input type="text" name="plant_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Location</label><input type="text" name="location"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'supplier') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Supplier</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" name="supplier_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="supplier_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Contact</label><input type="text" name="contact"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'customer') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Customer</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" name="customer_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="customer_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Contact</label><input type="text" name="contact"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Currency Modal -->
    <div class="modal fade" id="addCurrencyModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'currency') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Currency</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" name="currency_code"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="currency_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Symbol</label><input type="text" name="symbol"
                                class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Asset Modal -->
    <div class="modal fade" id="addAssetModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'asset') }}" method="POST">@csrf<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Asset (AUC)</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Asset No</label><input type="text" name="asset_no"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Asset Name</label><input type="text" name="asset_name"
                                class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description"
                                class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add S.Loc Modal -->
    <div class="modal fade" id="addSlocModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.master.store', 'storage_location') }}" method="POST">@csrf<div
                    class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Storage Location</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">S.Loc</label><input type="text" name="sloc"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description"
                                class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </div>
            </form>
        </div>
    </div>


    <!-- EDIT MODALS -->

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editUserForm" method="POST" onsubmit="return validateUsername('editUserUser')">@csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Full Name</label><input type="text" id="editUserName"
                                name="full_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Username</label><input type="text" id="editUserUser"
                                name="username" class="form-control" required minlength="3" pattern="[A-Za-z0-9_-]+"
                                title="Minimal 3 karakter, tanpa spasi" oninput="checkUsernameSpaces(this)"></div>
                        <div class="mb-3"><label class="form-label">Role</label><select id="editUserRole" name="role"
                                class="form-select">@foreach(\App\Models\User::ROLES as $role)<option value="{{ $role }}">
                                    {{ $role }}
                                </option>@endforeach</select></div>
                        <div class="mb-3"><label class="form-label">Department</label><select id="editUserDept"
                                name="department" class="form-select">@foreach($depts as $d)<option
                                    value="{{ $d->dept_name }}">{{ $d->dept_code }} | {{ $d->dept_name }}</option>
                                @endforeach</select></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" id="editUserEmail"
                                name="email" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">New Password</label><input type="password"
                                name="password" class="form-control" placeholder="Optional"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editItemForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Item</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editItemCode"
                                name="item_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editItemName"
                                name="item_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Category</label><input type="text" id="editItemCat"
                                name="category" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Dept Modal -->
    <div class="modal fade" id="editDeptModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editDeptForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Department</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Dept Code</label><input type="text" id="editDeptCode"
                                name="dept_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Dept Name</label><input type="text" id="editDeptName"
                                name="dept_name" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCatModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editCatForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editCatCode"
                                name="category_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editCatName"
                                name="category_name" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit IO Modal -->
    <div class="modal fade" id="editIOModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editIOForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit IO Number</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">IO Number</label>
                            <input type="text" id="editIONumber" name="io_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" id="editIODesc" name="description" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project</label>
                            <select id="editIOProject" name="project" class="form-select">
                                <option value="">Select Project</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->project_code }}">P-{{ date('Y') }}-{{ $p->id }} -
                                        {{ $p->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select id="editIOCat" name="category" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($cats as $cat)
                                    <option value="{{ $cat->category_code }}">{{ $cat->category_code }} -
                                        {{ $cat->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit CC Modal -->
    <div class="modal fade" id="editCCModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editCCForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Cost Center</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editCCCode"
                                name="cc_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editCCName"
                                name="cc_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Department</label><select id="editCCDept"
                                name="department" class="form-select">@foreach($depts as $d)<option
                                    value="{{ $d->dept_name }}">{{ $d->dept_code }} | {{ $d->dept_name }}</option>
                                @endforeach</select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editProjectForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Project</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editProjectCode"
                                name="project_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editProjectName"
                                name="project_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Category</label><select id="editProjectCat"
                                name="category" class="form-select">
                                <option value="">Select Category</option>@foreach($cats as $cat)<option
                                    value="{{ $cat->category_code }}">{{ $cat->category_code }} - {{ $cat->category_name }}
                                </option>@endforeach
                            </select></div>
                        <div class="mb-3"><label class="form-label">Description</label><input type="text"
                                id="editProjectDesc" name="description" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Plant Modal -->
    <div class="modal fade" id="editPlantModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editPlantForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Plant</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editPlantCode"
                                name="plant_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editPlantName"
                                name="plant_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Location</label><input type="text" id="editPlantLoc"
                                name="location" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editSupplierForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Supplier</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editSupplierCode"
                                name="supplier_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editSupplierName"
                                name="supplier_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Contact</label><input type="text"
                                id="editSupplierContact" name="contact" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editCustomerForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Customer</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editCustomerCode"
                                name="customer_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editCustomerName"
                                name="customer_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Contact</label><input type="text"
                                id="editCustomerContact" name="contact" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Currency Modal -->
    <div class="modal fade" id="editCurrencyModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editCurrencyForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Currency</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Code</label><input type="text" id="editCurrencyCode"
                                name="currency_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" id="editCurrencyName"
                                name="currency_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Symbol</label><input type="text" id="editCurrencySymbol"
                                name="symbol" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Asset Modal -->
    <div class="modal fade" id="editAssetModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editAssetForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Asset (AUC)</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Asset No</label><input type="text" id="editAssetNo"
                                name="asset_no" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Asset Name</label><input type="text" id="editAssetName"
                                name="asset_name" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Description</label><input type="text" id="editAssetDesc"
                                name="description" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Status</label><select id="editAssetStatus" name="status"
                                class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit S.Loc Modal -->
    <div class="modal fade" id="editSlocModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editSlocForm" method="POST">@csrf @method('PUT')<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Storage Location</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">S.Loc</label><input type="text" id="editSlocCode"
                                name="sloc" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Description</label><input type="text" id="editSlocDesc"
                                name="description" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Status</label><select id="editSlocStatus" name="status"
                                class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Project Metadata mapping
        const projectMetadata = {
            @foreach($projects as $p)
                                            "{{ $p->project_code }}": {
                    "category": "{{ $p->category }}",
                    "name": "{{ addslashes($p->project_name) }}"
                },
            @endforeach
                        };

        // Auto-populate Category & Description based on Project
        document.addEventListener('DOMContentLoaded', function () {
            const addProj = document.getElementById('addIOProject');
            const addCat = document.getElementById('addIOCat');
            const addDesc = document.getElementById('addIODesc');

            const editProj = document.getElementById('editIOProject');
            const editCat = document.getElementById('editIOCat');
            const editDesc = document.getElementById('editIODesc');

            function syncProjectData(projEl, catEl, descEl) {
                if (!projEl) return;
                projEl.addEventListener('change', function () {
                    const selectedProj = this.value;
                    const meta = projectMetadata[selectedProj];
                    if (meta) {
                        if (catEl) catEl.value = meta.category;
                        if (descEl) descEl.value = meta.name;
                    }
                });
            }

            syncProjectData(addProj, addCat, addDesc);
            syncProjectData(editProj, editCat, editDesc);
        });

        // EDIT USER - REMOVED (Moved to RBAC)

        // EDIT ITEM
        function editItem(id, code, name, category) {
            let form = document.getElementById('editItemForm');
            let url = "{{ route('admin.master.update', ['type' => 'item', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editItemCode').value = code;
            document.getElementById('editItemName').value = name;
            document.getElementById('editItemCat').value = category;
            new bootstrap.Modal(document.getElementById('editItemModal')).show();
        }

        // EDIT DEPT
        function editDept(id, code, name) {
            let form = document.getElementById('editDeptForm');
            let url = "{{ route('admin.master.update', ['type' => 'department', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editDeptCode').value = code;
            document.getElementById('editDeptName').value = name;
            new bootstrap.Modal(document.getElementById('editDeptModal')).show();
        }

        // EDIT CATEGORY
        function editCat(id, code, name) {
            let form = document.getElementById('editCatForm');
            let url = "{{ route('admin.master.update', ['type' => 'category', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editCatCode').value = code;
            document.getElementById('editCatName').value = name;
            new bootstrap.Modal(document.getElementById('editCatModal')).show();
        }

        // EDIT IO
        function editIO(id, number, desc, cat, project) {
            let form = document.getElementById('editIOForm');
            let url = "{{ route('admin.master.update', ['type' => 'io', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editIONumber').value = number;
            document.getElementById('editIODesc').value = desc;
            document.getElementById('editIOCat').value = cat;
            // Handle project select if needed
            let projSelect = document.getElementById('editIOProject');
            if (projSelect) projSelect.value = project || "";
            new bootstrap.Modal(document.getElementById('editIOModal')).show();
        }

        // EDIT CC
        function editCC(id, code, name, dept) {
            let form = document.getElementById('editCCForm');
            let url = "{{ route('admin.master.update', ['type' => 'cc', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editCCCode').value = code;
            document.getElementById('editCCName').value = name;
            document.getElementById('editCCDept').value = dept;
            new bootstrap.Modal(document.getElementById('editCCModal')).show();
        }

        // EDIT PROJECT
        function editProject(id, code, name, cat, desc) {
            let form = document.getElementById('editProjectForm');
            let url = "{{ route('admin.master.update', ['type' => 'project', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editProjectCode').value = code;
            document.getElementById('editProjectName').value = name;
            document.getElementById('editProjectCat').value = cat;
            document.getElementById('editProjectDesc').value = desc;
            new bootstrap.Modal(document.getElementById('editProjectModal')).show();
        }

        // EDIT PLANT
        function editPlant(id, code, name, location) {
            let form = document.getElementById('editPlantForm');
            let url = "{{ route('admin.master.update', ['type' => 'plant', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editPlantCode').value = code;
            document.getElementById('editPlantName').value = name;
            document.getElementById('editPlantLoc').value = location;
            new bootstrap.Modal(document.getElementById('editPlantModal')).show();
        }

        // EDIT SUPPLIER
        function editSupplier(id, code, name, contact) {
            let form = document.getElementById('editSupplierForm');
            let url = "{{ route('admin.master.update', ['type' => 'supplier', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editSupplierCode').value = code;
            document.getElementById('editSupplierName').value = name;
            document.getElementById('editSupplierContact').value = contact;
            new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
        }

        // EDIT CUSTOMER
        function editCustomer(id, code, name, contact) {
            let form = document.getElementById('editCustomerForm');
            let url = "{{ route('admin.master.update', ['type' => 'customer', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editCustomerCode').value = code;
            document.getElementById('editCustomerName').value = name;
            document.getElementById('editCustomerContact').value = contact;
            new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
        }

        // EDIT CURRENCY
        function editCurrency(id, code, name, symbol) {
            let form = document.getElementById('editCurrencyForm');
            let url = "{{ route('admin.master.update', ['type' => 'currency', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editCurrencyCode').value = code;
            document.getElementById('editCurrencyName').value = name;
            document.getElementById('editCurrencySymbol').value = symbol;
            new bootstrap.Modal(document.getElementById('editCurrencyModal')).show();
        }

        // EDIT ASSET
        function editAsset(id, assetNo, assetName, desc, status) {
            let form = document.getElementById('editAssetForm');
            let url = "{{ route('admin.master.update', ['type' => 'asset', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editAssetNo').value = assetNo;
            document.getElementById('editAssetName').value = assetName;
            document.getElementById('editAssetDesc').value = desc;
            document.getElementById('editAssetStatus').value = status;
            new bootstrap.Modal(document.getElementById('editAssetModal')).show();
        }

        // EDIT SLOC
        function editSloc(id, code, desc, status) {
            let form = document.getElementById('editSlocForm');
            let url = "{{ route('admin.master.update', ['type' => 'storage_location', 'id' => ':id']) }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editSlocCode').value = code;
            document.getElementById('editSlocDesc').value = desc;
            document.getElementById('editSlocStatus').value = status;
            new bootstrap.Modal(document.getElementById('editSlocModal')).show();
        }

        // Activate tab from URL parameter
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                // Find the nav-link for this tab and click it
                const tabElement = document.querySelector(`a[href="#${tab}"]`);
                if (tabElement) {
                    tabElement.click();
                }
            }
        });

        // Username validation functions
        // Username validation functions - REMOVED (Moved to RBAC)
    </script>
@endpush