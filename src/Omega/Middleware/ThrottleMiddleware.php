<?php

/** @noinspection PhpUnusedParameterInspection */

declare(strict_types=1);

namespace Omega\Middleware;

use Closure;
use Omega\Http\Request;
use Omega\Http\Response;
use Omega\RateLimiter\RateLimiterInterface;

class ThrottleMiddleware
{
    public function __construct(protected RateLimiterInterface $limiter)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestKey($request);
        if ($this->limiter->isBlocked($key, $maxAttempts = 60, $decayMinutes = 1)) {
            return $this->rateLimitedResponse(
                key: $key,
                maxAttempts: $maxAttempts,
                remainingAfter: $this->calculateRemainingAttempts(
                    key: $key,
                    maxAttempts: $maxAttempts,
                    retryAfter: $this->limiter->getRetryAfter($key)
                )
            );
        }

        $this->limiter->consume($key, $decayMinutes);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->add(
            $this->rateLimitedHeader(
                maxAttempts: $maxAttempts,
                remainingAfter: $this->calculateRemainingAttempts(
                    key: $key,
                    maxAttempts: $maxAttempts,
                    retryAfter: null
                )
            )
        );

        return $response;
    }

    protected function resolveRequestKey(Request $request): string
    {
        $key = $request->getRemoteAddress();

        return sha1($key);
    }

    protected function rateLimitedResponse(
        string $key,
        int $maxAttempts,
        int $remainingAfter,
        ?int $retryAfter = null
    ): Response {
        return new Response(
            'Too Many Requests',
            429,
            $this->rateLimitedHeader(
                $maxAttempts,
                $remainingAfter,
                $remainingAfter
            )
        );
    }

    /**
     * @return array<string, string>
     */
    protected function rateLimitedHeader(int $maxAttempts, int $remainingAfter, ?int $retryAfter = null): array
    {
        $header = [
            'X-RateLimit-Limit'     => (string) $maxAttempts,
            'X-RateLimit-Remaining' => (string) $remainingAfter,
        ];

        if ($retryAfter !== null) {
            $header['Retry-After'] = (string) $retryAfter;
        }

        return $header;
    }

    public function calculateRemainingAttempts(string $key, int $maxAttempts, ?int $retryAfter): int
    {
        if (null !== $retryAfter) {
            return 0;
        }

        return $this->limiter->remaining($key, $maxAttempts);
    }
}
