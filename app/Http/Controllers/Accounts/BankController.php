<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankRequest;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string)$request->string('search'));

        $banks = Bank::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderByDesc('created_at')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('dashboards.accounts.banks.index', compact('banks', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboards.accounts.banks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BankRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool)($validated['is_active'] ?? false);

        Bank::create($validated);

        return redirect()
            ->route('accounts.banks.index')
            ->with('success', 'Bank created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bank $bank)
    {
        return redirect()->route('accounts.banks.edit', $bank);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank)
    {
        return view('dashboards.accounts.banks.edit', compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankRequest $request, Bank $bank)
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool)($validated['is_active'] ?? false);

        $bank->update($validated);

        return redirect()
            ->route('accounts.banks.index')
            ->with('success', 'Bank updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()
            ->route('accounts.banks.index')
            ->with('success', 'Bank deleted successfully.');
    }
}
