<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyExpiredDrugRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyQuarantineController extends Controller
{
    /**
     * Display a listing of quarantined items.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'QUARANTINED');
        
        $records = PharmacyExpiredDrugRecord::with(['drug', 'command'])
            ->where('status', $status)
            ->orderBy('expiry_date', 'asc')
            ->paginate(15);

        return view('pharmacy.quarantine.index', compact('records', 'status'));
    }

    /**
     * Update the status of a quarantined item (Action).
     */
    public function act(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:DESTROYED,QUARANTINED,NAFDAC',
            'action_notes' => 'nullable|string',
        ]);

        $record = PharmacyExpiredDrugRecord::findOrFail($id);

        $record->update([
            'status' => $request->status,
            'action_notes' => $request->action_notes,
            'acted_by' => Auth::id(),
            'acted_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Quarantine action recorded successfully.');
    }
}
