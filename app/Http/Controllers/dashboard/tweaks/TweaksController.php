<?php

namespace App\Http\Controllers\dashboard\tweaks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TweaksController extends Controller
{
    public function index()
    {
        return view('dashboard.tweaks.index');
    }

    public function create()
    {
        return view('dashboard.tweaks.create');
    }

    public function store(Request $request)
    {

    }
}
