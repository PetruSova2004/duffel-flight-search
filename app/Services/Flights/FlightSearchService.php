<?php

namespace App\Services\Flights;

use App\Data\Flights\Offer;
use App\Data\Flights\SearchResult;
use App\Services\Duffel\DuffelClient;
use App\Services\Duffel\DuffelErrorType;
use App\Services\Duffel\DuffelException;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

class FlightSearchService
{
    /**
     * @param DuffelClient $client
     * @param OfferMapper $mapper
     * @param int $supplierTimeout Milliseconds Duffel waits for airlines
     */
    public function __construct(
        private readonly DuffelClient $client,
        private readonly OfferMapper $mapper,
        #[Config('services.duffel.supplier_timeout')] private readonly int $supplierTimeout,
    ) {}

    /**
     * One-way search for a single adult in economy.
     *
     * @param SearchCriteria $criteria
     * @return SearchResult
     * @throws DuffelException
     */
    public function search(SearchCriteria $criteria): SearchResult
    {
        $response = $this->client->post('/air/offer_requests', [
            'slices' => [[
                'origin' => $criteria->origin,
                'destination' => $criteria->destination,
                'departure_date' => $criteria->departureDate->toDateString(),
            ]],
            'passengers' => [['type' => 'adult']],
            'cabin_class' => 'economy',
        ], [
            'return_offers' => 'true',
            'supplier_timeout' => $this->supplierTimeout,
        ]);

        $data = $response['data'] ?? [];
        $slice = $data['slices'][0] ?? [];
        $raw = $data['offers'] ?? [];
        $offers = $this->offers($raw);

        // Every offer broken means the format changed, not "no flights"
        if ($raw !== [] && $offers === []) {
            throw new DuffelException(DuffelErrorType::Unknown, 'No offer could be mapped');
        }

        return new SearchResult(
            offers: $offers,
            departureDate: $criteria->departureDate,
            originName: $slice['origin']['name'] ?? $criteria->origin,
            destinationName: $slice['destination']['name'] ?? $criteria->destination,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rawOffers
     * @return list<Offer>
     */
    private function offers(array $rawOffers): array
    {
        $offers = [];

        foreach ($rawOffers as $raw) {
            try {
                $offers[] = $this->mapper->map($raw);
            } catch (Throwable $e) {
                // One broken offer should not hide the rest
                Log::warning('Skipped malformed Duffel offer', ['offer_id' => $raw['id'] ?? null, 'error' => $e->getMessage()]);
            }
        }

        usort($offers, fn (Offer $a, Offer $b) => (float) $a->price->amount <=> (float) $b->price->amount);

        return $offers;
    }
}
