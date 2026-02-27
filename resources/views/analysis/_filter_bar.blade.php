{{-- Shared Analysis Filter Bar - matches Dashboard style (horizontal row) --}}
<style>
    /* Ensure filter stays horizontal on desktop */
    .analysis-filter-form {
        flex-wrap: nowrap !important;
    }

    /* Fix dropdown text truncation */
    .analysis-filter-form .form-select {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        padding-right: 2rem !important;
    }

    /* === TABLET: make filters scrollable === */
    @media (max-width: 991.98px) {
        .analysis-header {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.75rem;
        }

        .analysis-filter-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .analysis-filter-form {
            flex-wrap: nowrap !important;
            width: max-content;
            gap: 0.5rem !important;
        }

        .analysis-filter-form .form-select,
        .analysis-filter-form .btn,
        .analysis-filter-form a {
            font-size: 0.8rem;
        }
    }
</style>

<div class="analysis-filter-container">
    <form action="{{ $filterAction }}" method="GET" class="d-flex gap-2 analysis-filter-form">
        <a href="{{ $filterAction }}"
            class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 d-flex align-items-center justify-content-center"
            style="background-color: #fff; height: 38px; white-space: nowrap; flex-shrink: 0;" title="Reset Filters">
            <i class="fas fa-sync-alt me-2 text-primary"></i> Default
        </a>

        <select name="year" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
            style="background-color: #fff; cursor: pointer; height: 38px; min-width: 100px; max-width: 130px;"
            onchange="this.form.submit()">
            <option value="">All Years</option>
            @foreach($years as $year)
                <option value="{{ $year }}" {{ ($selectedYear ?? '') == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>

        <select name="customer" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
            style="background-color: #fff; cursor: pointer; height: 38px; min-width: 130px; max-width: 180px;"
            onchange="this.form.submit()">
            <option value="">All Customers</option>
            @foreach($customers as $cust)
                <option value="{{ $cust }}" {{ ($selectedCustomer ?? '') == $cust ? 'selected' : '' }}>
                    {{ $cust }}
                </option>
            @endforeach
        </select>

        <select name="category" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
            style="background-color: #fff; cursor: pointer; height: 38px; min-width: 160px; max-width: 250px;"
            onchange="this.form.submit()">
            @if($canSeeAllCategories ?? true)
                <option value="">All Business Categories</option>
            @endif
            @foreach($businessCategories as $cat)
                <option value="{{ $cat->category_name }}" {{ ($selectedCategory ?? '') == $cat->category_name ? 'selected' : '' }}>
                    {{ $cat->category_name }}
                </option>
            @endforeach
        </select>

        @if($showProjectFilter ?? false)
            <select name="project" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
                style="background-color: #fff; cursor: pointer; height: 38px; min-width: 140px; max-width: 220px;"
                onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects ?? [] as $proj)
                    <option value="{{ $proj->id }}" {{ ($selectedProject ?? '') == $proj->id ? 'selected' : '' }}>
                        {{ $proj->project_name }}
                    </option>
                @endforeach
            </select>
        @endif

        {{-- Preserve extra params like year_a, year_b for Year Comparison --}}
        @if(request()->has('year_a'))
            <input type="hidden" name="year_a" value="{{ request('year_a') }}">
        @endif
        @if(request()->has('year_b'))
            <input type="hidden" name="year_b" value="{{ request('year_b') }}">
        @endif
    </form>
</div>