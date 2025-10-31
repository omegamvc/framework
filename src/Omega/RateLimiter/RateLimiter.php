<?php

/**
 * Part of Omega - RateLimiter Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\RateLimiter;

use DateInterval;
use DateInvalidTimeZoneException;
use DateMalformedStringException;
use Omega\RateLimiter\Policy\PolicyInterface;

use function max;
use function Omega\Time\now;

/**
 * Class RateLimiter
 *
 * Implements the RateLimiterInterface to provide
 * rate-limiting functionality for keys (e.g., user IDs, IP addresses).
 *
 * It delegates the actual rate limiting logic to a PolicyInterface
 * instance, allowing different rate-limiting strategies.
 *
 * @category  Omega
 * @package   RateLimiter
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
readonly class RateLimiter implements RateLimiterInterface
{
    /**
     * RateLimiter constructor.
     *
     * @param PolicyInterface $Limiter The underlying rate-limiter policy instance
     *                                 that handles the storage and logic for limits.
     * @return void
     */
    public function __construct(private PolicyInterface $Limiter)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isBlocked(string $key, int $maxAttempts, int|DateInterval $decay): bool
    {
        return $this->Limiter->peek($key)->isBlocked();
    }

    /**
     * {@inheritdoc}
     */
    public function consume(string $key, int $decayMinutes = 1): int
    {
        return $this->Limiter->consume($key, 1)->getConsumed();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(string $key): int
    {
        return $this->Limiter->peek($key)->getConsumed();
    }

    /**
     * {@inheritdoc}
     *
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function getRetryAfter(string $key): int
    {
        $result = $this->Limiter->peek($key);
        if (null === $result->getRetryAfter()) {
            return 0;
        }

        return max(0, $result->getRetryAfter()->getTimestamp() - now()->timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function remaining(string $key, int $maxAttempts): int
    {
        return $this->Limiter->peek($key)->getRemaining();
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $key): void
    {
        $this->Limiter->reset($key);
    }
}
