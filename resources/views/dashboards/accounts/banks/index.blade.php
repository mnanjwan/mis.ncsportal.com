@extends('layouts.app')

@section('title', 'Banks')
@section('page-title', 'Banks')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <span class="text-primary">Banks</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Banks</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('accounts.banks.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Add Bank
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <form id="banks-search-form" method="GET" action="{{ route('accounts.banks.index') }}" class="mb-4">
                <div class="flex flex-row items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                        <input
                            id="banks-search-input"
                            name="search"
                            type="text"
                            class="kt-input w-full"
                            placeholder="Search bank name..."
                            value="{{ $search ?? request('search') }}"
                            autocomplete="off"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                        @if(($search ?? request('search')) !== null && trim((string)($search ?? request('search'))) !== '')
                            <a href="{{ route('accounts.banks.index') }}" class="kt-btn kt-btn-outline">
                                <i class="ki-filled ki-cross"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div id="banks-table-wrap">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Account Digits</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Active</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banks as $bank)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-foreground">{{ $bank->name }}</td>
                                    <td class="py-3 px-4 text-sm text-foreground font-mono">{{ $bank->account_number_digits }}</td>
                                    <td class="py-3 px-4 text-sm">
                                        @if($bank->is_active)
                                            <span class="text-success font-medium">Yes</span>
                                        @else
                                            <span class="text-danger font-medium">No</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('accounts.banks.edit', $bank) }}" class="kt-btn kt-btn-sm kt-btn-ghost" title="Edit">
                                                <i class="ki-filled ki-notepad-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('accounts.banks.destroy', $bank) }}"
                                                  onsubmit="return confirm('Delete this bank? This will not change existing officer records (they store bank name as text), but it will remove it from future selections.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-danger" title="Delete">
                                                    <i class="ki-filled ki-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-4 text-center text-secondary-foreground">
                                        No banks found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($banks, 'links'))
                <div id="banks-pagination-wrap">
                    <x-pagination :paginator="$banks" itemName="banks" />
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('banks-search-form');
    const input = document.getElementById('banks-search-input');
    const tableWrap = document.getElementById('banks-table-wrap');
    const paginationWrap = document.getElementById('banks-pagination-wrap');
    if (!form || !input || !tableWrap) return;

    let lastRequestId = 0;
    let debounceTimer = null;

    function setLoading(isLoading) {
        tableWrap.style.opacity = isLoading ? '0.6' : '1';
        tableWrap.style.pointerEvents = isLoading ? 'none' : 'auto';
        if (paginationWrap) {
            paginationWrap.style.opacity = isLoading ? '0.6' : '1';
            paginationWrap.style.pointerEvents = isLoading ? 'none' : 'auto';
        }
    }

    async function loadUrl(url, { push = false } = {}) {
        const requestId = ++lastRequestId;
        setLoading(true);

        try {
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            });

            const html = await res.text();
            if (requestId !== lastRequestId) return;

            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newTableWrap = doc.getElementById('banks-table-wrap');
            const newPaginationWrap = doc.getElementById('banks-pagination-wrap');

            if (newTableWrap) tableWrap.innerHTML = newTableWrap.innerHTML;
            if (paginationWrap && newPaginationWrap) paginationWrap.innerHTML = newPaginationWrap.innerHTML;

            if (push) {
                history.pushState({}, '', url);
            } else {
                history.replaceState({}, '', url);
            }
        } catch (e) {
            console.error('Failed to load banks list', e);
        } finally {
            if (requestId === lastRequestId) setLoading(false);
        }
    }

    function buildUrlWithSearch(searchValue) {
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(window.location.search);

        // keep any existing query params except page (reset)
        params.delete('page');
        params.set('search', searchValue);

        // remove empty search to keep URL clean
        if (!searchValue || searchValue.trim() === '') {
            params.delete('search');
        }

        url.search = params.toString();
        return url.toString();
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        loadUrl(buildUrlWithSearch(input.value), { push: true });
    });

    input.addEventListener('input', () => {
        if (debounceTimer) window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => {
            loadUrl(buildUrlWithSearch(input.value), { push: false });
        }, 350);
    });

    if (paginationWrap) {
        paginationWrap.addEventListener('click', (e) => {
            const a = e.target.closest('a');
            if (!a) return;
            e.preventDefault();
            loadUrl(a.href, { push: true });
        });
    }

    window.addEventListener('popstate', () => {
        loadUrl(window.location.href, { push: false });
    });
})();
</script>
@endpush

