@extends('layouts.app')

@php
    function formatRupiahCatInv($amount)
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

        .cat-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .cat-strip {
            height: 4px;
        }

        .progress-custom {
            height: 6px;
            border-radius: 3px;
            background-color: #e2e8f0;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Category Investment Breakdown</h4>
            <p class="text-muted mb-0 small">Proporsi investasi per kategori (Machine, Tooling, Facility, Building).</p>
        </div>
        @include('analysis._filter_bar', ['filterAction' => route('analysis.category_investment')])
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Total Investment</div>
                <div class="text-value" style="color: #1e3a5f;">{{ formatRupiahCatInv($grandBudget) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Total Terealisasi</div>
                <div class="text-value" style="color: #0d6832;">{{ formatRupiahCatInv($grandRealization) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Kategori Aktif</div>
                <div class="text-value">{{ $byCategory->count() }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Donut Chart --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Investment Distribution</h6>
                    <div style="height: 320px; position: relative;">
                        <canvas id="catPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Category Cards --}}
        <div class="col-lg-7">
            <div class="row g-3">
                @php
                    $catColors = ['#3b82f6', '#0d6832', '#e67e22', '#dc2626', '#8b5cf6', '#1a3a6c'];
                    $catBgs = ['#eff6ff', '#f0fdf4', '#fffbeb', '#fef2f2', '#f5f3ff', '#eef2ff'];
                @endphp
                @foreach($byCategory as $i => $cat)
                    @php
                        $color = $catColors[$i % count($catColors)];
                        $bgColor = $catBgs[$i % count($catBgs)];
                        $share = $grandBudget > 0 ? ($cat->total_budget / $grandBudget) * 100 : 0;
                        $barColor = $cat->absorption_rate > 100 ? '#dc2626' : ($cat->absorption_rate > 75 ? '#e67e22' : $color);
                    @endphp
                    <div class="col-md-6">
                        <div class="cat-card h-100">
                            <div class="cat-strip" style="background: {{ $color }};"></div>
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0" style="font-size: 0.82rem; color: #1e3a5f;">
                                            {{ $cat->category ?: 'Uncategorized' }}
                                        </h6>
                                        <span class="text-muted" style="font-size: 0.68rem;">{{ $cat->item_count }} items •
                                            {{ number_format($share, 1) }}% share</span>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <div class="rounded-2 p-2 text-center" style="background: {{ $bgColor }};">
                                            <div class="text-muted fw-semibold"
                                                style="font-size: 0.6rem; text-transform: uppercase;">Budget</div>
                                            <div class="fw-bold" style="font-size: 0.8rem; color: {{ $color }};">
                                                {{ formatRupiahCatInv($cat->total_budget) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="rounded-2 p-2 text-center" style="background: #f0fdf4;">
                                            <div class="text-muted fw-semibold"
                                                style="font-size: 0.6rem; text-transform: uppercase;">Realisasi</div>
                                            <div class="fw-bold" style="font-size: 0.8rem; color: #0d6832;">
                                                {{ formatRupiahCatInv($cat->total_realization) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted" style="font-size: 0.68rem;">Absorption</span>
                                    <span class="fw-bold"
                                        style="font-size: 0.7rem; color: {{ $barColor }};">{{ number_format($cat->absorption_rate, 1) }}%</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar"
                                        style="width: {{ min(100, $cat->absorption_rate) }}%; background: {{ $barColor }}; border-radius: 3px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Bar Chart --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Budget vs Realisasi per Category</h6>
                    <div style="height: 300px; position: relative;">
                        <canvas id="catBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Year Trend --}}
        @if(!$selectedYear && $yearTrend->count() > 0)
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3" style="color: #1e293b;">Investment Trend by Year & Category</h6>
                        <div style="height: 300px; position: relative;">
                            <canvas id="yearTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const catData = {!! json_encode($byCategory->values()) !!};
            const catLabels = catData.map(c => c.category || 'N/A');
            const catBudgets = catData.map(c => Number(c.total_budget));
            const catReals = catData.map(c => Number(c.total_realization));
            const colors = ['#3b82f6', '#0d6832', '#e67e22', '#dc2626', '#8b5cf6', '#1a3a6c'];

            new Chart(document.getElementById('catPieChart'), {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{
                        data: catBudgets,
                        backgroundColor: colors.slice(0, catLabels.length),
                        borderWidth: 0,
                        hoverOffset: 10,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { size: 10 } } },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                    return ctx.label + ': Rp ' + ctx.raw.toLocaleString('id-ID') + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('catBarChart'), {
                type: 'bar',
                data: {
                    labels: catLabels,
                    datasets: [
                        { label: 'Budget', data: catBudgets, backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.6 },
                        { label: 'Realisasi', data: catReals, backgroundColor: '#0d6832', borderRadius: 4, barPercentage: 0.6 }
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
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                        y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt', font: { size: 10 } } }
                    }
                }
            });

            @if(!$selectedYear && $yearTrend->count() > 0)
                const yearTrendData = {!! json_encode($yearTrend) !!};
                const trendYears = Object.keys(yearTrendData);
                const allCats = new Set();
                Object.values(yearTrendData).forEach(items => items.forEach(item => allCats.add(item.category || 'N/A')));
                const catList = Array.from(allCats);

                const trendDatasets = catList.map((cat, idx) => ({
                    label: cat,
                    data: trendYears.map(year => {
                        const item = yearTrendData[year].find(i => (i.category || 'N/A') === cat);
                        return item ? Number(item.total_budget) : 0;
                    }),
                    backgroundColor: colors[idx % colors.length],
                    borderRadius: 4,
                    barPercentage: 0.7
                }));

                new Chart(document.getElementById('yearTrendChart'), {
                    type: 'bar',
                    data: { labels: trendYears, datasets: trendDatasets },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, font: { size: 10 } } },
                            tooltip: {
                                callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.raw.toLocaleString('id-ID') }
                            }
                        },
                        scales: {
                            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 12 } } },
                            y: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt', font: { size: 10 } } }
                        }
                    }
                });
            @endif
                });
    </script>
@endpush