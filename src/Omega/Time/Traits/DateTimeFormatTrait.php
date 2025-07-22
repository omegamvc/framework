<?php

/**
 * Part of Omega - Time Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Time\Traits;

use DateTimeInterface;

/**
 * Trait DateTimeFormatTrait
 *
 * Provides convenience methods for formatting date and time using various standardized
 * representations defined by the {@see DateTimeInterface}. This trait is intended to
 * be used in date-related classes (such as Omega\Time\Now) to simplify outputting
 * date strings in formats like ATOM, RFC, W3C, and others.
 *
 * Each method returns the formatted string representation of the current DateTime
 * instance using a predefined constant from the DateTimeInterface.
 *
 * @category   Omega
 * @package    Time
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait DateTimeFormatTrait
{
    /**
     * Format the date using the ATOM standard (Y-m-d\TH:i:sP).
     *
     * @return string The formatted date string.
     */
    public function formatATOM(): string
    {
        return $this->format(DateTimeInterface::ATOM);
    }

    /**
     * Format the date using the COOKIE format (l, d-M-Y H:i:s T).
     *
     * @return string The formatted date string.
     */
    public function formatCOOKIE(): string
    {
        return $this->format(DateTimeInterface::COOKIE);
    }

    /**
     * Format the date using the RFC 822 format (D, d M y H:i:s O).
     *
     * @return string The formatted date string.
     */
    public function formatRFC822(): string
    {
        return $this->format(DateTimeInterface::RFC822);
    }

    /**
     * Format the date using the RFC 850 format (l, d-M-y H:i:s T).
     * Note: Internally uses RFC 822 format due to PHP limitations.
     *
     * @return string The formatted date string.
     */
    public function formatRFC850(): string
    {
        return $this->format(DateTimeInterface::RFC822);
    }

    /**
     * Format the date using the RFC 1036 format (D, d M y H:i:s O).
     * Note: Internally uses RFC 822 format due to PHP limitations.
     *
     * @return string The formatted date string.
     */
    public function formatRFC1036(): string
    {
        return $this->format(DateTimeInterface::RFC822);
    }

    /**
     * Format the date using the RFC 1123 format (D, d M Y H:i:s O).
     *
     * @return string The formatted date string.
     */
    public function formatRFC1123(): string
    {
        return $this->format(DateTimeInterface::RFC1123);
    }

    /**
     * Format the date using the RFC 7231 format (D, d M Y H:i:s GMT).
     *
     * @return string The formatted date string.
     */
    public function formatRFC7231(): string
    {
        return $this->format(DateTimeInterface::RFC7231);
    }

    /**
     * Format the date using the RFC 2822 format (D, d M Y H:i:s O).
     *
     * @return string The formatted date string.
     */
    public function formatRFC2822(): string
    {
        return $this->format(DateTimeInterface::RFC2822);
    }

    /**
     * Format the date using the RFC 3339 format.
     *
     * If $expanded is true, uses the extended version (Y-m-d\TH:i:s.vP),
     * otherwise the default (Y-m-d\TH:i:sP).
     *
     * @param bool $expanded Whether to use the extended RFC 3339 format.
     * @return string The formatted date string.
     */
    public function formatRFC3339(bool $expanded = false): string
    {
        return $this->format($expanded ? DateTimeInterface::RFC3339_EXTENDED : DateTimeInterface::RFC3339);
    }

    /**
     * Format the date using the RSS format (D, d M Y H:i:s O).
     *
     * @return string The formatted date string.
     */
    public function formatRSS(): string
    {
        return $this->format(DateTimeInterface::RSS);
    }

    /**
     * Format the date using the W3C format (Y-m-d\TH:i:sP).
     *
     * @return string The formatted date string.
     */
    public function formatW3C(): string
    {
        return $this->format(DateTimeInterface::W3C);
    }
}
