<?php

namespace Tests\Feature\Duffel;

use App\Services\Duffel\DuffelClient;
use App\Services\Duffel\DuffelErrorType;
use App\Services\Duffel\DuffelException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DuffelClientTest extends TestCase
{
    public function test_it_sends_required_headers(): void
    {
        Http::fake(['api.duffel.com/*' => Http::response(['data' => []])]);

        app(DuffelClient::class)->post('/air/offer_requests', ['cabin_class' => 'economy'], ['return_offers' => 'true']);

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://api.duffel.com/air/offer_requests?return_offers=true'
            && $request->hasHeader('Authorization', 'Bearer duffel_test_fake')
            && $request->hasHeader('Duffel-Version', 'v2')
            && $request->hasHeader('Accept', 'application/json')
            && $request->hasHeader('Accept-Encoding', 'gzip')
            && $request['data'] === ['cabin_class' => 'economy']);
    }

    public function test_it_walks_all_pages(): void
    {
        Http::fake(['api.duffel.com/air/airports*' => Http::sequence()
            ->push(['data' => [['id' => 1], ['id' => 2]], 'meta' => ['after' => 'cursor-2']])
            ->push(['data' => [['id' => 3]], 'meta' => ['after' => null]]),
        ]);

        $ids = app(DuffelClient::class)->paginate('/air/airports')->pluck('id')->all();

        $this->assertSame([1, 2, 3], $ids);
        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'after=cursor-2'));
    }

    /**
     * @return array<string, array{int, DuffelErrorType}>
     */
    public static function errorStatuses(): array
    {
        return [
            'bad token' => [401, DuffelErrorType::Unauthorized],
            'validation' => [422, DuffelErrorType::Validation],
            'rate limit' => [429, DuffelErrorType::RateLimited],
            'server error' => [503, DuffelErrorType::Unavailable],
        ];
    }

    #[DataProvider('errorStatuses')]
    public function test_it_maps_http_errors(int $status, DuffelErrorType $type): void
    {
        Http::fake(['api.duffel.com/*' => Http::response([
            'errors' => [['code' => 'some_code', 'title' => 'Error', 'message' => 'Details']],
        ], $status)]);

        try {
            app(DuffelClient::class)->post('/air/offer_requests', []);
            $this->fail('Exception was not thrown');
        } catch (DuffelException $e) {
            $this->assertSame($type, $e->type);
            $this->assertSame($status, $e->status);
            $this->assertNotEmpty($e->userMessage());
        }
    }

    public function test_it_maps_connection_errors(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        try {
            app(DuffelClient::class)->post('/air/offer_requests', []);
            $this->fail('Exception was not thrown');
        } catch (DuffelException $e) {
            $this->assertSame(DuffelErrorType::Connection, $e->type);
        }
    }

    public function test_it_retries_get_requests_on_server_errors(): void
    {
        Sleep::fake();
        Http::fake(['api.duffel.com/*' => Http::sequence()
            ->push([], 502)
            ->push(['data' => ['ok' => true]]),
        ]);

        $this->assertSame(['data' => ['ok' => true]], app(DuffelClient::class)->get('/air/airports'));
        Http::assertSentCount(2);
    }

    public function test_it_fails_fast_without_token(): void
    {
        config(['services.duffel.token' => null]);
        Http::fake();

        try {
            app(DuffelClient::class)->get('/air/airports');
            $this->fail('Exception was not thrown');
        } catch (DuffelException $e) {
            $this->assertSame(DuffelErrorType::Misconfigured, $e->type);
        }

        Http::assertNothingSent();
    }
}
