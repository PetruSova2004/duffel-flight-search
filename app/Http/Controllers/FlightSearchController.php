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
     * @param PlaceDirectory $places
     */
    public function __construct(private readonly PlaceDirectory $places) {}

    /**
     * Empty search form.
     *
     * @return View
     */
    public function index(): View
    {
        return $this->page();
    }

    /**
     * @param FlightSearchRequest $request
     * @param FlightSearchService $search
     * @return View
     */
    public function search(FlightSearchRequest $request, FlightSearchService $search): View
    {
        try {
            return $this->page(['result' => $search->search($request->criteria())]);
        } catch (DuffelException $e) {
            return $this->page(['searchError' => $e->userMessage()]);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return View
     */
    private function page(array $data = []): View
    {
        try {
            $countries = $this->places->countries();
        } catch (DuffelException $e) {
            return view('flights.index', ['countries' => [], 'formError' => $e->userMessage()]);
        }

        return view('flights.index', [
            'minDate' => FlightSearchRequest::minDate()->toDateString(),
            'maxDate' => FlightSearchRequest::maxDate()->toDateString(),
            'countries' => array_map(
                fn (string $code, string $name) => ['code' => $code, 'name' => $name],
                array_keys($countries),
                $countries,
            ),
            ...$data,
        ]);
    }
}
