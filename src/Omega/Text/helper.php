<?php

declare(strict_types=1);

namespace Omega\Text;

if (!function_exists('string')) {
    /**
     * String manipulation.
     *
     * @param string $text Text string
     * @return Text
     */
    function string(string $text): Text
    {
        return new Text($text);
    }
}

if (!function_exists('text')) {
    /**
     * String manipulation.
     *
     * @param string $text Text string
     * @return Text
     */
    function text(string $text): Text
    {
        return new Text($text);
    }
}
