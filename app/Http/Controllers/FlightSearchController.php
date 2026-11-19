<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FlightSearchController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        return view('flights.index');
    }
}
