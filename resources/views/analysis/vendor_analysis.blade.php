@extends('layouts.app')

@php
    function formatRupiahVendor($amount)
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

        .rank-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.7rem;
            color: white;
        }

        .share-bar {
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .share-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 1.2s ease;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Vendor Analysis</h4>
            <p class="text-muted mb-0 small">Analisis performa dan distribusi belanja ke vendor.</p>
        </div>
        @include('analysis._filter_bar', ['filterAction' => route('analysis.vendor_analysis')])
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Total Spending</div>
                <div class="text-value" style="color: #1e3a5f;">{{ formatRupiahVendor($totalSpending) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Total Orders</div>
                <div class="text-value">{{ $totalOrders }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Active Vendors</div>
                <div class="text-value">{{ $totalVendors }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Pie Chart --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Vendor Share Distribution</h6>
                    <div style="height: 320px; position: relative;">
                        <canvas id="vendorPie"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Vendors Table --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <h6 class="fw-bold px-4 pt-3 pb-2 mb-0" style="color: #1e293b;">Top Vendors Ranking</h6>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Vendor</th>
                                    <th class="text-end">Total Belanja</th>
                                    <th class="text-center">Orders</th>
                                    <th style="min-width: 120px;">Share</th>
                                    <th class="text-end">Avg/Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topVendors as $i => $vendor)
                                    @php
                                        $rankBg = match ($i) { 0 => '#e6a817', 1 => '#94a3b8', 2 => '#cd7c32', default => '#e2e8f0'};
                                        $rankColor = $i < 3 ? 'white' : '#64748b';
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="rank-badge"
                                                style="background: {{ $rankBg }}; color: {{ $rankColor }};">{{ $i + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold" style="color: #1e3a5f;">{{ $vendor->vendor_name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">{{ $vendor->vendor_code }}</div>
                                        </td>
                                        <td class="text-end fw-bold text-nowrap">Rp
                                            {{ number_format($vendor->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"
                                                style="font-size: 0.75rem;">{{ $vendor->total_orders }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="share-bar flex-grow-1">
                                                    <div class="share-fill"
                                                        style="width: {{ $vendor->share_percentage }}%; background: #3b82f6;">
                                                    </div>
                                                </div>
                                                <span class="fw-bold"
                                                    style="font-size: 0.7rem; min-width: 35px;">{{ number_format($vendor->share_percentage, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-end text-muted text-nowrap">Rp
                                            {{ number_format($vendor->avg_order_value, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No vendor data available</td>
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
            const labels = {!! json_encode($chartLabels) !!};
            const values = {!! json_encode($chartValues) !!};
            const colors = ['#3b82f6', '#0d6832', '#e67e22', '#e74c3c', '#8b5cf6', '#ec4899', '#06b6d4', '#1a3a6c', '#ea580c', '#84cc16'];

            new Chart(document.getElementById('vendorPie'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors.slice(0, labels.length),
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
        });
    </script>
@endpush