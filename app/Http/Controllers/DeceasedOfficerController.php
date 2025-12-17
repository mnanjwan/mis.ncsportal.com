<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeceasedOfficerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('dashboards.accounts.deceased-officers-list');
    }

    public function create()
    {
        return view('forms.deceased-officer.create');
    }

    public function show($id)
    {
        return view('dashboards.welfare.deceased-officer-show', compact('id'));
    }
}


