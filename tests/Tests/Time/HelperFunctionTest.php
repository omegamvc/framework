<?php

/**
 * Part of Omega - Tests\Time Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Time;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

use function Omega\Time\now;
use function time;

#[CoversFunction('now')]
final class HelperFunctionTest extends TestCase
{
    /**
     * Test it can use function helper.
     *
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testItCanUseFunctionHelper(): void
    {
        $this->assertEquals(time(), now()->timestamp);
    }
}
