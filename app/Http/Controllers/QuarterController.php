<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuarterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Building Unit');
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
}


