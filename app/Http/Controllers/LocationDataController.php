<?php

namespace App\Http\Controllers;

use App\Models\GeopoliticalZone;
use App\Models\Lga;
use App\Models\State;
use Illuminate\Http\Request;

class LocationDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Single page: manage Geopolitical Zones, States, and LGAs (related).
     */
    public function index()
    {
        $zones = GeopoliticalZone::with(['states' => function ($q) {
            $q->orderBy('sort_order')->orderBy('name');
        }, 'states.lgas' => function ($q) {
            $q->orderBy('sort_order')->orderBy('name');
        }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboards.hrd.location-data.index', compact('zones'));
    }

    public function storeZone(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        GeopoliticalZone::create([
            'name' => $request->name,
            'sort_order' => (int) $request->get('sort_order', 0),
            'is_active' => true,
        ]);

        return redirect()->route('hrd.location-data.index')
            ->with('success', 'Geopolitical zone added.');
    }

    public function updateZone(Request $request, GeopoliticalZone $zone)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $zone->update([
            'name' => $request->name,
            'sort_order' => (int) $request->get('sort_order', $zone->sort_order),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('hrd.location-data.index')
            ->with('success', 'Zone updated.');
    }

    public function destroyZone(GeopoliticalZone $zone)
    {
        if ($zone->states()->exists()) {
            return redirect()->route('hrd.location-data.index')
                ->with('error', 'Cannot delete zone that has states. Remove or reassign states first.');
        }
        $zone->delete();
        return redirect()->route('hrd.location-data.index')
            ->with('success', 'Zone deleted.');
    }

    public function storeState(Request $request)
    {
        $request->validate([
            'geopolitical_zone_id' => 'required|exists:geopolitical_zones,id',
            'name' => 'required|string|max:255',
        ]);

        State::create([
            'geopolitical_zone_id' => $request->geopolitical_zone_id,
            'name' => $request->name,
            'sort_order' => (int) $request->get('sort_order', 0),
            'is_active' => true,
        ]);

        return redirect()->route('hrd.location-data.index')
            ->with('success', 'State added.')
            ->with('selected_zone_id', $request->geopolitical_zone_id);
    }

    public function updateState(Request $request, State $state)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'geopolitical_zone_id' => 'required|exists:geopolitical_zones,id',
            'is_active' => 'nullable|boolean',
        ]);

        $state->update([
            'geopolitical_zone_id' => $request->geopolitical_zone_id,
            'name' => $request->name,
            'sort_order' => (int) $request->get('sort_order', $state->sort_order),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('hrd.location-data.index')
            ->with('success', 'State updated.')
            ->with('selected_zone_id', $state->geopolitical_zone_id);
    }

    public function destroyState(State $state)
    {
        if ($state->lgas()->exists()) {
            return redirect()->route('hrd.location-data.index')
                ->with('error', 'Cannot delete state that has LGAs. Remove or reassign LGAs first.');
        }
        $zoneId = $state->geopolitical_zone_id;
        $state->delete();
        return redirect()->route('hrd.location-data.index')
            ->with('success', 'State deleted.')
            ->with('selected_zone_id', $zoneId);
    }

    public function storeLga(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
        ]);

        Lga::create([
            'state_id' => $request->state_id,
            'name' => $request->name,
            'sort_order' => (int) $request->get('sort_order', 0),
            'is_active' => true,
        ]);

        $state = State::find($request->state_id);

        return redirect()->route('hrd.location-data.index')
            ->with('success', 'LGA added.')
            ->with('selected_zone_id', $state ? $state->geopolitical_zone_id : null)
            ->with('selected_state_id', $request->state_id);
    }

    public function updateLga(Request $request, Lga $lga)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'is_active' => 'nullable|boolean',
        ]);

        $lga->update([
            'state_id' => $request->state_id,
            'name' => $request->name,
            'sort_order' => (int) $request->get('sort_order', $lga->sort_order),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $state = State::find($lga->state_id);

        return redirect()->route('hrd.location-data.index')
            ->with('success', 'LGA updated.')
            ->with('selected_zone_id', $state ? $state->geopolitical_zone_id : null)
            ->with('selected_state_id', $lga->state_id);
    }

    public function destroyLga(Lga $lga)
    {
        $state = $lga->state;
        $zoneId = $state ? $state->geopolitical_zone_id : null;
        $stateId = $lga->state_id;
        $lga->delete();
        return redirect()->route('hrd.location-data.index')
            ->with('success', 'LGA deleted.')
            ->with('selected_zone_id', $zoneId)
            ->with('selected_state_id', $stateId);
    }
}
