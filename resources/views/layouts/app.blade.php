<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <title>Budget & Investment System</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html {
            zoom: 0.67;
        }
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #0f172a;
            --sidebar-color: #e2e8f0;
            --active-bg: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%);
            --hover-bg: rgba(255, 255, 255, 0.05);
            --transition-speed: 0.3s;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            /* Prevent horizontal scroll */
        }

        /* Sidebar Container */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-color);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            transition: transform var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: var(--active-bg);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .sidebar-title {
            color: white;
            font-weight: 600;
            font-size: 1rem;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }

        .sidebar-title small {
            display: block;
            font-weight: 400;
            color: #94a3b8;
            font-size: 0.75rem;
            margin-top: 2px;
        }

        /* Navigation Links */
        .nav-link {
            color: #94a3b8;
            padding: 0.85rem 1.25rem;
            margin: 0.25rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .nav-link:hover {
            color: white;
            background: var(--hover-bg);
            transform: translateX(4px);
        }

        .nav-link.active {
            background: var(--active-bg);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .menu-label {
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            padding: 1.5rem 1.25rem 0.5rem;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left var(--transition-speed) ease;
        }

        /* Overlay for Mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-speed) ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive Breakpoint (Tablet/Mobile) */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
                padding-top: 70px;
                /* Space for sticky navbar */
            }

            .top-navbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1030;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(8px);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/logo.svg') }}" alt="BISS Logo" class="rounded"
                style="width: 40px; height: 40px; object-fit: contain;">
            <div class="sidebar-title">
                <span style="font-size: 1.1rem; letter-spacing: -0.5px;">BIS</span>
                <small class="opacity-75" style="font-size: 0.75rem; margin-top: 2px;">Budget & Investment
                    System</small>
            </div>
        </div>

        <!-- MAIN MENU -->
        <div class="menu-section">
            <div class="menu-label">Main Menu</div>
            <div class="nav flex-column">
                @if(Auth::user() && Auth::user()->hasPermission('Menu: Dashboard'))
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i>Dashboard
                    </a>
                @endif

                @if(Auth::user() && Auth::user()->hasPermission('Menu: Budget Plan'))
                    <a href="{{ route('budget.index') }}"
                        class="nav-link {{ request()->routeIs('budget.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet"></i>Budget Plan
                    </a>
                @endif

                @if(Auth::user() && Auth::user()->hasPermission('Menu: Purchase Request'))
                    <a href="{{ route('pr.index') }}" class="nav-link {{ request()->routeIs('pr.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>Purchase Request
                    </a>
                @endif

                @if(Auth::user() && Auth::user()->hasPermission('Menu: Purchase Order'))
                    <a href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>Purchase Order
                    </a>
                @endif

                @if(Auth::user() && Auth::user()->hasPermission('Menu: Projects'))
                    <a href="{{ route('projects.index') }}"
                        class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <i class="fas fa-briefcase"></i>Projects
                    </a>
                @endif
            </div>
        </div>

        <!-- ANALYSIS - Permission-based -->
        @if(Auth::user() && Auth::user()->hasPermission('Menu: Analysis'))
            <div class="menu-section">
                <div class="menu-label">Analysis</div>
                <div class="nav flex-column">
                    <a href="{{ route('analysis.budget_evaluation') }}"
                        class="nav-link {{ request()->routeIs('analysis.budget_evaluation') ? 'active' : '' }}">
                        <i class="fas fa-check-square"></i>Budget Evaluation
                    </a>
                    <a href="{{ route('analysis.budget_absorption') }}"
                        class="nav-link {{ request()->routeIs('analysis.budget_absorption') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>Budget Absorption
                    </a>
                    <a href="{{ route('analysis.monthly_trend') }}"
                        class="nav-link {{ request()->routeIs('analysis.monthly_trend') ? 'active' : '' }}">
                        <i class="fas fa-chart-area"></i>Monthly Trend
                    </a>
                    <a href="{{ route('analysis.vendor_analysis') }}"
                        class="nav-link {{ request()->routeIs('analysis.vendor_analysis') ? 'active' : '' }}">
                        <i class="fas fa-truck-loading"></i>Vendor Analysis
                    </a>
                    <a href="{{ route('analysis.approval_pipeline') }}"
                        class="nav-link {{ request()->routeIs('analysis.approval_pipeline') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i>Approval Pipeline
                    </a>
                    <a href="{{ route('analysis.year_comparison') }}"
                        class="nav-link {{ request()->routeIs('analysis.year_comparison') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>Year Comparison
                    </a>
                    <a href="{{ route('analysis.category_investment') }}"
                        class="nav-link {{ request()->routeIs('analysis.category_investment') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie"></i>Category Investment
                    </a>
                    <a href="{{ route('analysis.berita_acara') }}"
                        class="nav-link {{ request()->routeIs('analysis.berita_acara') ? 'active' : '' }}">
                        <i class="fas fa-file-pdf"></i>Berita Acara
                    </a>
                </div>
            </div>
        @endif

        <!-- SYSTEM - Only Super Admin -->
        @if(Auth::user() && Auth::user()->role === 'Super Admin')
            <div class="menu-section">
                <div class="menu-label">System</div>
                <div class="nav flex-column">
                    <a href="{{ route('rbac.index') }}" class="nav-link {{ request()->routeIs('rbac.*') ? 'active' : '' }}">
                        <i class="fas fa-user-shield"></i>Access Control
                    </a>
                    <a href="{{ route('admin.index') }}"
                        class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="fas fa-database"></i>Master Data
                    </a>
                    <a href="{{ route('vendors.index') }}"
                        class="nav-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                        <i class="fas fa-truck"></i>Master Vendors
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex justify-content-between align-items-center px-4 py-3">
            <button class="btn btn-link d-lg-none p-0 text-dark" id="sidebarToggle">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <div class="d-flex align-items-center gap-3 ms-auto">
                @php
                    $userRole = Auth::user()->role ?? '';
                    $isAdmin = in_array($userRole, ['Admin', 'Super Admin']);
                    $lastRead = Auth::user()->last_notification_read_at ?? now()->subYears(10);

                    if ($isAdmin) {
                        // Admin/Super Admin: Show recent workflow activities from all users (after last read)
                        $recentActivities = \App\Models\PrWorkflowHistory::with('actor')
                            ->where('actor_id', '!=', Auth::id())
                            ->where('created_at', '>', $lastRead)
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();
                        $notifCount = $recentActivities->count();
                        $notifItems = $recentActivities;
                    } else {
                        // Regular users: Show PRs waiting for their approval
                        $prWaitingList = \App\Models\PurchaseRequest::where('status', 'Submitted')
                            ->where('current_approver_role', $userRole)
                            ->select('pr_number', \DB::raw('MAX(id) as id'), \DB::raw('MAX(purpose) as purpose'), \DB::raw('SUM(qty_req * estimated_price) as total'), \DB::raw('"PR" as type'), \DB::raw('MAX(created_at) as created_at'))
                            ->groupBy('pr_number')
                            ->orderBy('id', 'desc')
                            ->limit(5)
                            ->get();

                        // Regular users: Show Budget Plans waiting for their approval
                        $budgetWaitingList = \App\Models\BudgetPlan::where('status', 'Submitted')
                            ->where('current_approver_role', $userRole)
                            ->select('id', 'department', 'fiscal_year', 'created_by', 'created_at', \DB::raw('"Budget" as type'))
                            ->orderBy('id', 'desc')
                            ->limit(5)
                            ->get();

                        // Merge and sort by newest
                        $combinedNotifs = $prWaitingList->concat($budgetWaitingList)->sortByDesc('created_at')->take(10);

                        $notifCount = $combinedNotifs->count();
                        $notifItems = $combinedNotifs;
                    }
                @endphp

                <!-- Notification Dropdown -->
                <div class="dropdown me-2">
                    <a href="#" class="position-relative text-decoration-none" data-bs-toggle="dropdown"
                        aria-expanded="false" title="Notifikasi" id="notifBell">
                        <i class="fas fa-bell {{ $notifCount > 0 ? 'text-secondary' : 'text-muted' }}"
                            style="font-size: 1.25rem; {{ $notifCount == 0 ? 'opacity: 0.4;' : '' }}"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size: 0.65rem; {{ $notifCount == 0 ? 'display:none;' : '' }}" id="notifBadge">
                            {{ $notifCount > 9 ? '9+' : $notifCount }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg"
                        style="min-width: 350px; border: none; border-radius: 12px; max-height: 400px; overflow-y: auto;"
                        id="notifDropdown">
                        <li class="px-3 py-2 border-bottom sticky-top bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Notifikasi</span>
                                <div class="d-flex gap-1" id="notifButtons">
                                    @if($notifCount > 0)
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            style="font-size: 0.6rem; padding: 0.15rem 0.4rem;"
                                            onclick="markNotificationsRead()" title="Tandai sudah dibaca">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            style="font-size: 0.6rem; padding: 0.15rem 0.4rem;"
                                            onclick="clearNotifications()" title="Bersihkan semua">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </li>

                        <div id="notifContent">
                            @if($isAdmin)
                                {{-- Admin/Super Admin: Show activities --}}
                                @forelse($recentActivities as $activity)
                                    @php
                                        $actionColor = 'secondary';
                                        $actionIcon = 'fa-info-circle';
                                        if ($activity->action == 'Created') {
                                            $actionColor = 'primary';
                                            $actionIcon = 'fa-plus-circle';
                                        } elseif ($activity->action == 'Edited') {
                                            $actionColor = 'info';
                                            $actionIcon = 'fa-edit';
                                        } elseif ($activity->action == 'Approved') {
                                            $actionColor = 'success';
                                            $actionIcon = 'fa-check-circle';
                                        } elseif ($activity->action == 'Rejected') {
                                            $actionColor = 'danger';
                                            $actionIcon = 'fa-times-circle';
                                        }
                                    @endphp
                                    <li class="notif-item">
                                        <a class="dropdown-item py-2 px-3"
                                            href="{{ route('pr.show', ['pr' => 1]) }}?pr_number={{ $activity->pr_number }}">
                                            <div class="d-flex align-items-start">
                                                <div class="rounded-circle bg-{{ $actionColor }} bg-opacity-10 p-2 me-2">
                                                    <i class="fas {{ $actionIcon }} text-{{ $actionColor }}"
                                                        style="font-size: 0.8rem;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold text-dark small">{{ $activity->pr_number }} -
                                                        {{ $activity->action }}
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        {{ Str::limit($activity->notes, 40) }}
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.65rem;">
                                                        <i
                                                            class="far fa-user me-1"></i>{{ $activity->actor->full_name ?? 'System' }}
                                                        <span class="mx-1">•</span>
                                                        {{ $activity->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li class="px-3 py-4 text-center empty-msg">
                                        <i class="fas fa-check-circle text-success fa-2x mb-2 opacity-50"></i>
                                        <p class="text-muted mb-0 small">Tidak ada notifikasi baru</p>
                                    </li>
                                @endforelse
                            @else
                                {{-- Regular users: Show PRs and Budgets waiting approval --}}
                                @forelse($notifItems as $item)
                                    @if($item->type == 'PR')
                                        <li class="notif-item">
                                            <a class="dropdown-item py-2 px-3"
                                                href="{{ route('pr.show', ['pr' => $item->id]) }}?pr_number={{ $item->pr_number }}">
                                                <div class="d-flex align-items-start">
                                                    <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                                        <i class="fas fa-file-alt text-warning" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold text-dark small">{{ $item->pr_number }}</div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            {{ Str::limit($item->purpose ?? 'Menunggu approval Anda', 30) }}
                                                        </div>
                                                        <div class="text-primary" style="font-size: 0.7rem;">Rp
                                                            {{ number_format($item->total ?? 0, 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @else
                                        {{-- Budget Notification --}}
                                        <li class="notif-item">
                                            <a class="dropdown-item py-2 px-3" href="{{ route('budget.show', $item->id) }}">
                                                <div class="d-flex align-items-start">
                                                    <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                                        <i class="fas fa-wallet text-success" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold text-dark small">Budget Plan
                                                            {{ $item->fiscal_year }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">Dept:
                                                            {{ $item->department }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.65rem;">
                                                            <i
                                                                class="far fa-user me-1"></i>{{ $item->creator->full_name ?? 'User' }}
                                                            <span class="mx-1">•</span> Waiting Approval
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @endif
                                @empty
                                    <li class="px-3 py-4 text-center empty-msg">
                                        <i class="fas fa-check-circle text-success fa-2x mb-2 opacity-50"></i>
                                        <p class="text-muted mb-0 small">Tidak ada item menunggu approval</p>
                                    </li>
                                @endforelse
                            @endif
                        </div>

                        <li class="border-top sticky-bottom bg-white">
                            <a class="dropdown-item text-center py-2 text-primary small fw-semibold"
                                href="{{ route('pr.index') }}">
                                Lihat Semua PR <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <script>
                    function markNotificationsRead() {
                        fetch('{{ route("notifications.markRead") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Only hide badge, keep notifications visible
                                    var badge = document.getElementById('notifBadge');
                                    if (badge) badge.style.display = 'none';
                                }
                            });
                    }

                    function clearNotifications() {
                        fetch('{{ route("notifications.clear") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Hide badge
                                    var badge = document.getElementById('notifBadge');
                                    if (badge) badge.style.display = 'none';

                                    // Hide all notification items
                                    document.querySelectorAll('.notif-item').forEach(el => el.style.display = 'none');

                                    // Hide buttons
                                    var btns = document.getElementById('notifButtons');
                                    if (btns) btns.innerHTML = '';

                                    // Show empty message
                                    var content = document.getElementById('notifContent');
                                    if (content && !content.querySelector('.empty-msg-shown')) {
                                        var emptyLi = document.createElement('li');
                                        emptyLi.className = 'px-3 py-4 text-center empty-msg-shown';
                                        emptyLi.innerHTML = '<i class="fas fa-check-circle text-success fa-2x mb-2 opacity-50"></i><p class="text-muted mb-0 small">Tidak ada notifikasi baru</p>';
                                        content.appendChild(emptyLi);
                                    }
                                }
                            });
                    }
                </script>

                <!-- User Profile Dropdown -->
                <div class="dropdown ms-3">
                    <div class="d-flex align-items-center dropdown-toggle no-arrow cursor-pointer" id="userDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <div class="text-end me-3 d-none d-md-block">
                            <div class="fw-bold text-dark small mb-0" style="line-height: 1.2;">
                                {{ Auth::user()->full_name ?? 'User' }}
                            </div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ Auth::user()->role ?? 'User' }}</div>
                        </div>
                        <div class="avatar-circle shadow-sm">
                            {{ Str::upper(substr(Auth::user()->full_name ?? 'U', 0, 2)) }}
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2" aria-labelledby="userDropdown"
                        style="border-radius: 12px; min-width: 200px; z-index: 1050;">
                        <!-- Mobile User Info Header -->
                        <li class="d-md-none px-4 py-3 border-bottom bg-light rounded-top">
                            <div class="fw-bold text-dark">{{ Auth::user()->full_name ?? 'User' }}</div>
                            <div class="text-muted small">{{ Auth::user()->role ?? 'User' }}</div>
                        </li>

                        <li class="py-1">
                            <h6 class="dropdown-header text-uppercase small fw-bold text-muted my-1">Account</h6>
                        </li>
                        <li><a class="dropdown-item py-2 px-4" href="{{ route('profile.edit') }}"><i
                                    class="fas fa-user-circle me-3 text-secondary"></i>Profile</a></li>
                        <li>
                            <hr class="dropdown-divider mx-2">
                        </li>

                        <li>
                            <a class="dropdown-item py-2 px-4 text-danger fw-medium" href="#"
                                onclick="event.preventDefault(); confirmLogout();">
                                <i class="fas fa-sign-out-alt me-3"></i>Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>

                <style>
                    .cursor-pointer {
                        cursor: pointer;
                    }

                    .no-arrow::after {
                        display: none !important;
                    }

                    .avatar-circle {
                        width: 40px;
                        height: 40px;
                        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
                        color: white;
                        border-radius: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: 600;
                        font-size: 0.9rem;
                        transition: transform 0.2s;
                    }

                    .avatar-circle:hover {
                        transform: scale(1.05);
                    }

                    .dropdown-item:hover {
                        background-color: #f8fafc;
                        color: #4f46e5;
                    }

                    .dropdown-item:active {
                        background-color: #e0e7ff;
                    }
                </style>
            </div>
        </nav>

        <!-- Content -->
        <div class="p-4" style="flex-grow: 1;">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="mt-auto py-3 px-4" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center">
                <span style="color: #3b82f6; font-weight: 500; font-size: 0.875rem;">BIS System</span>
                <span class="text-muted" style="font-size: 0.8rem;">© 2025 v1.1 (Admin Panel)</span>
                <span class="text-muted" style="font-size: 0.8rem;">Dibuat dengan <span style="color: #ef4444;">❤</span>
                    untuk Perusahaan</span>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: "Apakah Anda yakin ingin keluar dari sistem?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fas fa-sign-out-alt me-2"></i> Ya, Keluar!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        // Global Confirmation Helper for Links
        function swalRedirect(url, title = 'Konfirmasi', text = 'Apakah Anda yakin?', icon = 'question') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebarToggle = document.getElementById('sidebarToggle');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    toggleSidebar();
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function () {
                    toggleSidebar();
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>