<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use Omega\Console\Style\Alert;
use Omega\Console\Style\Decorate;
use Omega\Console\Style\Style;

trait AlertTrait
{
    /** @var int margin left */
    protected int $marginLeft = 0;

    /**
     * Set margin left.
     *
     * @param int $marginLeft
     * @return AlertTrait|Alert
     */
    public function marginLeft(int $marginLeft): self
    {
        $this->marginLeft = $marginLeft;

        return $this;
    }

    /**
     * Render alert info.
     *
     * @param string $info
     * @return Style
     */
    public function info(string $info): Style
    {
        return new Style()
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' info ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgBlue()
            ->push(' ')
            ->push($info)
            ->newLines(2);
    }

    /**
     * Render alert warning.
     *
     * @param string $warn
     * @return Style
     */
    public function warn(string $warn): Style
    {
        return new Style()
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' warn ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgYellow()
            ->push(' ')
            ->push($warn)
            ->newLines(2);
    }

    /**
     * Render alert fail.
     *
     * @param string $fail
     * @return Style
     */
    public function fail(string $fail): Style
    {
        return new Style()
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' fail ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgRed()
            ->push(' ')
            ->push($fail)
            ->newLines(2);
    }

    /**
     * Render alert ok (similar with success).
     *
     * @param string $ok
     * @return Style
     */
    public function ok(string $ok): Style
    {
        return new Style()
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' ok ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgGreen()
            ->push(' ')
            ->push($ok)
            ->newLines(2);
    }
}
