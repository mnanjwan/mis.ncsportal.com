<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\PfaRequest;
use App\Models\Pfa;
use Illuminate\Http\Request;

class PfaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string)$request->string('search'));

        $pfas = Pfa::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('rsa_prefix', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('dashboards.accounts.pfas.index', compact('pfas', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboards.accounts.pfas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PfaRequest $request)
    {
        $validated = $request->validated();
        $validated['rsa_prefix'] = strtoupper($validated['rsa_prefix']);
        $validated['is_active'] = (bool)($validated['is_active'] ?? false);

        Pfa::create($validated);

        return redirect()
            ->route('accounts.pfas.index')
            ->with('success', 'PFA created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pfa $pfa)
    {
        return redirect()->route('accounts.pfas.edit', $pfa);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pfa $pfa)
    {
        return view('dashboards.accounts.pfas.edit', compact('pfa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PfaRequest $request, Pfa $pfa)
    {
        $validated = $request->validated();
        $validated['rsa_prefix'] = strtoupper($validated['rsa_prefix']);
        $validated['is_active'] = (bool)($validated['is_active'] ?? false);

        $pfa->update($validated);

        return redirect()
            ->route('accounts.pfas.index')
            ->with('success', 'PFA updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pfa $pfa)
    {
        $pfa->delete();

        return redirect()
            ->route('accounts.pfas.index')
            ->with('success', 'PFA deleted successfully.');
    }
}
