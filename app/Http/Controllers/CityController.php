<?php

namespace App\Http\Controllers;

use App\Http\Requests\Flights\CityIndexRequest;
use App\Services\Duffel\DuffelException;
use App\Services\Duffel\PlaceDirectory;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Cities with an airport for the chosen country (used by the search form).
     *
     * @param CityIndexRequest $request
     * @param PlaceDirectory $places
     * @return JsonResponse
     */
    public function index(CityIndexRequest $request, PlaceDirectory $places): JsonResponse
    {
        try {
            $cities = $places->cities($request->validated('country'));
        } catch (DuffelException $e) {
            return response()->json(['message' => $e->userMessage()], 503);
        }

        return response()->json(['data' => $cities])
            ->setPrivate()
            ->setMaxAge(3600);
    }
}
