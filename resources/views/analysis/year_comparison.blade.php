@extends('layouts.app')

@php
    function formatRupiahYoy($amount)
    {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
        } elseif ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' jt';
        } else {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
    }
@endphp

@section('content')
    <style>
        .card-stat {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .text-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .text-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 5px;
        }

        .table-custom thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem;
        }

        .table-custom tbody td {
            padding: 1rem;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:hover {
            background: #f8fafc;
        }

        .growth-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .year-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            padding: 1.5rem;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Year-over-Year Comparison</h4>
            <p class="text-muted mb-0 small">Perbandingan budget & realisasi antar tahun.</p>
        </div>
        @include('analysis._filter_bar', ['filterAction' => route('analysis.year_comparison')])
    </div>

    {{-- Year A vs Year B selector --}}
    <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
        <span class="text-muted fw-bold small">Compare:</span>
        <form action="{{ route('analysis.year_comparison') }}" method="GET" class="d-flex align-items-center gap-2">
            @if($selectedCustomer)<input type="hidden" name="customer" value="{{ $selectedCustomer }}">@endif
            @if($selectedCategory)<input type="hidden" name="category" value="{{ $selectedCategory }}">@endif
            <select name="year_a" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
                style="background-color: #fff; cursor: pointer; height: 38px; min-width: 100px;" onchange="this.form.submit()">
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $yearA == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <span class="text-muted fw-bold">vs</span>
            <select name="year_b" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
                style="background-color: #fff; cursor: pointer; height: 38px; min-width: 100px;" onchange="this.form.submit()">
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $yearB == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Year Comparison Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="year-card" style="border-color: #3b82f6;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-label">Budget {{ $yearA }}</div>
                        <div class="text-value" style="color: #1e3a5f;">{{ formatRupiahYoy($dataA['total_budget']) }}</div>
                        <div class="text-muted small mt-1">Realisasi: {{ formatRupiahYoy($dataA['total_realization']) }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold" style="font-size: 2rem; color: #3b82f6;">
                            {{ number_format($dataA['absorption'], 1) }}%</div>
                        <div class="text-muted small">Absorption</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 d-flex align-items-center justify-content-center">
            <div class="text-center">
                @php
                    $growthColor = $budgetGrowth > 0 ? '#0d6832' : ($budgetGrowth < 0 ? '#dc2626' : '#64748b');
                    $growthBg = $budgetGrowth > 0 ? '#f0fdf4' : ($budgetGrowth < 0 ? '#fef2f2' : '#f1f5f9');
                    $growthIcon = $budgetGrowth > 0 ? '▲' : ($budgetGrowth < 0 ? '▼' : '—');
                    $realColor = $realizationGrowth > 0 ? '#0d6832' : ($realizationGrowth < 0 ? '#dc2626' : '#64748b');
                    $realBg = $realizationGrowth > 0 ? '#f0fdf4' : ($realizationGrowth < 0 ? '#fef2f2' : '#f1f5f9');
                    $realIcon = $realizationGrowth > 0 ? '▲' : ($realizationGrowth < 0 ? '▼' : '—');
                @endphp
                <div class="growth-badge mb-2" style="background: {{ $growthBg }}; color: {{ $growthColor }};">
                    {{ $growthIcon }} {{ number_format(abs($budgetGrowth), 1) }}%
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">Budget Growth</div>
                <div class="my-2 text-muted">⇅</div>
                <div class="growth-badge mb-1" style="background: {{ $realBg }}; color: {{ $realColor }};">
                    {{ $realIcon }} {{ number_format(abs($realizationGrowth), 1) }}%
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">Realisasi Growth</div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="year-card" style="border-color: #0d6832;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-label">Budget {{ $yearB }}</div>
                        <div class="text-value" style="color: #1e3a5f;">{{ formatRupiahYoy($dataB['total_budget']) }}</div>
                        <div class="text-muted small mt-1">Realisasi: {{ formatRupiahYoy($dataB['total_realization']) }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold" style="font-size: 2rem; color: #0d6832;">
                            {{ number_format($dataB['absorption'], 1) }}%</div>
                        <div class="text-muted small">Absorption</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-stat text-center">
                <div class="text-label">Projects {{ $yearA }}</div>
                <div class="fw-bold" style="font-size: 1.5rem; color: #3b82f6;">{{ $dataA['project_count'] }}</div>
                <div class="text-muted small">vs {{ $dataB['project_count'] }} ({{ $yearB }})</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat text-center">
                <div class="text-label">Budget Plans {{ $yearA }}</div>
                <div class="fw-bold" style="font-size: 1.5rem; color: #0d6832;">{{ $dataA['plan_count'] }}</div>
                <div class="text-muted small">vs {{ $dataB['plan_count'] }} ({{ $yearB }})</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat text-center">
                <div class="text-label">Total Budget {{ $yearA }}</div>
                <div class="fw-bold" style="font-size: 1.2rem; color: #1e3a5f;">
                    {{ formatRupiahYoy($dataA['total_budget']) }}</div>
                <div class="text-muted small">vs {{ formatRupiahYoy($dataB['total_budget']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat text-center">
                <div class="text-label">Realisasi {{ $yearA }}</div>
                <div class="fw-bold" style="font-size: 1.2rem; color: #e67e22;">
                    {{ formatRupiahYoy($dataA['total_realization']) }}</div>
                <div class="text-muted small">vs {{ formatRupiahYoy($dataB['total_realization']) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Bar Chart --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Budget & Realisasi Comparison</h6>
                    <div style="height: 320px; position: relative;">
                        <canvas id="yoyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Category Breakdown Tables --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <h6 class="fw-bold px-4 pt-3 pb-2 mb-0" style="color: #1e293b;">Category Breakdown {{ $yearA }}</h6>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Budget</th>
                                    <th class="text-end">Realisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataA['by_category'] as $cat)
                                    <tr>
                                        <td class="fw-bold">{{ $cat->category ?: '-' }}</td>
                                        <td class="text-end text-nowrap">Rp {{ number_format($cat->total_budget, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end text-nowrap" style="color: #0d6832;">Rp
                                            {{ number_format($cat->total_realization, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <h6 class="fw-bold px-4 pt-3 pb-2 mb-0" style="color: #1e293b;">Category Breakdown {{ $yearB }}</h6>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Budget</th>
                                    <th class="text-end">Realisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataB['by_category'] as $cat)
                                    <tr>
                                        <td class="fw-bold">{{ $cat->category ?: '-' }}</td>
                                        <td class="text-end text-nowrap">Rp {{ number_format($cat->total_budget, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end text-nowrap" style="color: #0d6832;">Rp
                                            {{ number_format($cat->total_realization, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('yoyChart'), {
                type: 'bar',
                data: {
                    labels: ['Budget', 'Realisasi', 'Sisa Budget'],
                    datasets: [
                        {
                            label: '{{ $yearA }}',
                            data: [{{ $dataA['total_budget'] }}, {{ $dataA['total_realization'] }}, {{ $dataA['total_budget'] - $dataA['total_realization'] }}],
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                            barPercentage: 0.5
                        },
                        {
                            label: '{{ $yearB }}',
                            data: [{{ $dataB['total_budget'] }}, {{ $dataB['total_realization'] }}, {{ $dataB['total_budget'] - $dataB['total_realization'] }}],
                            backgroundColor: '#0d6832',
                            borderRadius: 4,
                            barPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } },
                        tooltip: {
                            callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.raw.toLocaleString('id-ID') }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 12 } } },
                        y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt', font: { size: 10 } } }
                    }
                }
            });
        });
    </script>
@endpush