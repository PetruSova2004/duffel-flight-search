<?php

namespace App\Http\Controllers;

use App\Http\Requests\Flights\FlightSearchRequest;
use App\Services\Duffel\DuffelException;
use App\Services\Duffel\PlaceDirectory;
use App\Services\Flights\FlightSearchService;
use Illuminate\View\View;

class FlightSearchController extends Controller
{
    /**
     * Search form, plus results when the form was submitted.
     *
     * @param FlightSearchRequest $request
     * @param FlightSearchService $search
     * @param PlaceDirectory $places
     * @return View
     */
    public function index(FlightSearchRequest $request, FlightSearchService $search, PlaceDirectory $places): View
    {
        try {
            $countries = $places->countries();
        } catch (DuffelException $e) {
            return view('flights.index', ['formError' => $e->userMessage()]);
        }

        $data = [
            'minDate' => FlightSearchRequest::minDate()->toDateString(),
            'maxDate' => FlightSearchRequest::maxDate()->toDateString(),
            'countries' => array_map(
                fn (string $code, string $name) => ['code' => $code, 'name' => $name],
                array_keys($countries),
                $countries,
            ),
        ];

        if (! $request->isSearch()) {
            return view('flights.index', $data);
        }

        try {
            $data['result'] = $search->search($request->criteria());
        } catch (DuffelException $e) {
            $data['searchError'] = $e->userMessage();
        }

        return view('flights.index', $data);
    }
}
