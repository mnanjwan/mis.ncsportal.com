<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuarterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('dashboards.building.quarters');
    }

    public function create()
    {
        return view('forms.quarter.create');
    }

    public function allocate()
    {
        return view('forms.quarter.allocate');
    }

    public function officers()
    {
        return view('dashboards.building.officers');
    }

    /**
     * Show quarter requests management page (Building Unit)
     */
    public function requests()
    {
        return view('dashboards.building.requests');
    }

    /**
     * Show officer's own quarter requests
     */
    public function myRequests()
    {
        return view('officer.quarter-requests.index');
    }

    /**
     * Show create quarter request form (Officer)
     */
    public function createRequest()
    {
        return view('officer.quarter-requests.create');
    }
}


