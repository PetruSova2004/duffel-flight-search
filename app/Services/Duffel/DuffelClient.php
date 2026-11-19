<?php

namespace App\Services\Duffel;

use Generator;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Throwable;

/**
 * Thin HTTP wrapper around Duffel API v2. The token never leaves the server.
 */
class DuffelClient
{
    private const int PAGE_LIMIT = 200;

    /** Safety net against a cursor that never ends (≈ 46 pages of airports today) */
    private const int MAX_PAGES = 200;

    /**
     * @param string|null $token
     * @param string $baseUrl
     * @param string $version
     * @param int $timeout
     * @param int $connectTimeout
     */
    public function __construct(
        #[Config('services.duffel.token'), \SensitiveParameter] private readonly ?string $token,
        #[Config('services.duffel.base_url')] private readonly string $baseUrl,
        #[Config('services.duffel.version')] private readonly string $version,
        #[Config('services.duffel.timeout')] private readonly int $timeout,
        #[Config('services.duffel.connect_timeout')] private readonly int $connectTimeout,
    ) {}

    /**
     * @param string $uri
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     * @throws DuffelException
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->send('GET', $uri, $query);
    }

    /**
     * @param string $uri
     * @param array<string, mixed> $data Payload placed under the "data" key
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     * @throws DuffelException
     */
    public function post(string $uri, array $data, array $query = []): array
    {
        return $this->send('POST', $uri, $query, ['data' => $data]);
    }

    /**
     * Walks a cursor-paginated list endpoint lazily, page by page.
     *
     * @param string $uri
     * @param array<string, mixed> $query
     * @return LazyCollection<int, array<string, mixed>>
     * @throws DuffelException
     */
    public function paginate(string $uri, array $query = []): LazyCollection
    {
        return new LazyCollection(fn () => $this->pages($uri, $query));
    }

    /**
     * @param string $uri
     * @param array<string, mixed> $query
     * @return Generator<int, array<string, mixed>>
     * @throws DuffelException
     */
    private function pages(string $uri, array $query): Generator
    {
        $after = null;
        $pages = 0;

        do {
            $page = $this->get($uri, array_filter([...$query, 'limit' => self::PAGE_LIMIT, 'after' => $after]));

            foreach ($page['data'] ?? [] as $item) {
                yield $item;
            }

            $next = $page['meta']['after'] ?? null;

            if ($next !== null && ($next === $after || ++$pages >= self::MAX_PAGES)) {
                Log::error('Duffel pagination stopped', ['uri' => $uri, 'pages' => $pages]);

                throw new DuffelException(DuffelErrorType::Unknown, 'Pagination did not finish');
            }

            $after = $next;
        } while ($after !== null);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     * @throws DuffelException
     */
    private function send(string $method, string $uri, array $query = [], ?array $body = null): array
    {
        try {
            $response = $this->pendingRequest($method === 'GET')->send($method, $uri, array_filter([
                'query' => $query ?: null,
                'json' => $body,
            ]));
        } catch (ConnectionException $e) {
            Log::error('Duffel connection failed', ['method' => $method, 'uri' => $uri, 'error' => $e->getMessage()]);

            throw new DuffelException(DuffelErrorType::Connection, $e->getMessage(), null, $e);
        }

        if ($response->failed()) {
            throw $this->failure($method, $uri, $response);
        }

        $json = $response->json();

        if (! is_array($json)) {
            Log::error('Duffel returned a non-JSON body', ['method' => $method, 'uri' => $uri, 'status' => $response->status()]);

            throw new DuffelException(DuffelErrorType::Unknown, 'Unexpected response body');
        }

        return $json;
    }

    /**
     * @param bool $retryable Only idempotent GETs are retried
     * @return PendingRequest
     * @throws DuffelException
     */
    private function pendingRequest(bool $retryable): PendingRequest
    {
        if ($this->token === null || $this->token === '') {
            Log::error('Duffel token is not configured (DUFFEL_TOKEN).');

            throw new DuffelException(DuffelErrorType::Misconfigured, 'Duffel token is missing');
        }

        $request = Http::baseUrl($this->baseUrl)
            ->withToken($this->token)
            ->withHeaders([
                'Duffel-Version' => $this->version,
                'Accept-Encoding' => 'gzip',
            ])
            ->acceptJson()
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout);

        if ($retryable) {
            $request->retry([300, 1000], when: fn (Throwable $e) => $e instanceof ConnectionException
                || ($e instanceof RequestException && $e->response->serverError()), throw: false);
        }

        return $request;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param Response $response
     * @return DuffelException
     */
    private function failure(string $method, string $uri, Response $response): DuffelException
    {
        $type = DuffelErrorType::fromStatus($response->status());
        $errors = array_map(
            fn (mixed $error) => array_intersect_key((array) $error, array_flip(['code', 'title', 'message', 'source'])),
            (array) $response->json('errors', []),
        );

        Log::log($type === DuffelErrorType::Validation ? 'warning' : 'error', 'Duffel request failed', [
            'method' => $method,
            'uri' => $uri,
            'status' => $response->status(),
            'request_id' => $response->header('x-request-id'),
            'errors' => $errors,
        ]);

        return new DuffelException($type, $errors[0]['message'] ?? 'Duffel error', $response->status());
    }
}
