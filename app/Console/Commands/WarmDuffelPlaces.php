<?php

namespace App\Console\Commands;

use App\Services\Duffel\DuffelException;
use App\Services\Duffel\PlaceDirectory;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('duffel:warm-places')]
#[Description('Load countries and cities from Duffel into the cache')]
class WarmDuffelPlaces extends Command
{
    /**
     * @param PlaceDirectory $places
     * @return int
     */
    public function handle(PlaceDirectory $places): int
    {
        try {
            $data = $places->refresh();
        } catch (DuffelException $e) {
            $this->error('Duffel error: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Cached %d countries and %d cities.',
            count($data['countries']),
            array_sum(array_map('count', $data['cities'])),
        ));

        return self::SUCCESS;
    }
}
