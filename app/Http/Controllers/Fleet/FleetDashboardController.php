<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;

class FleetDashboardController extends Controller
{
    public function ccTl()
    {
        return view('dashboards.fleet.dashboard', [
            'title' => 'CC T&L Dashboard',
            'roleName' => 'CC T&L',
        ]);
    }

    public function dcgFats()
    {
        return view('dashboards.fleet.dashboard', [
            'title' => 'DCG FATS Dashboard',
            'roleName' => 'DCG FATS',
        ]);
    }

    public function acgTs()
    {
        return view('dashboards.fleet.dashboard', [
            'title' => 'ACG TS Dashboard',
            'roleName' => 'ACG TS',
        ]);
    }

    public function cd()
    {
        return view('dashboards.fleet.dashboard', [
            'title' => 'CD Dashboard',
            'roleName' => 'CD',
        ]);
    }

    public function ocTl()
    {
        return view('dashboards.fleet.dashboard', [
            'title' => 'O/C T&L Dashboard',
            'roleName' => 'O/C T&L',
        ]);
    }

    public function storeReceiver()
    {
        return view('dashboards.fleet.dashboard', [
            'title' => 'Transport Store/Receiver Dashboard',
            'roleName' => 'Transport Store/Receiver',
        ]);
    }
}

