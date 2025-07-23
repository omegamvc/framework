<?php

/**
 * Part of Omega - Tests\Testing Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Time;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Omega\Time\now;
use function time;

/**
 * Class HelperFunctionTest
 *
 * This test verifies the availability and correctness of global time-related helper functions,
 * specifically the `now()` helper, which is expected to return an instance of Omega\Time\Now
 * synchronized with the current system time.
 *
 * The test ensures that:
 * - The `now()` helper returns a valid object
 * - The returned timestamp matches the result of PHP's native `time()` function
 *
 * This provides a quick smoke test to confirm that the helper function is properly registered
 * and behaves as expected.
 *
 * @category  Omega\Tests
 * @package   Time
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversNothing]
class HelperFunctionTest extends TestCase
{
    /**
     * Test it can use function helper.
     *
     * @return void
     */
    public function testItCanUseFunctionHelper(): void
    {
        $this->assertEquals(time(), now()->timestamp);
    }
}
