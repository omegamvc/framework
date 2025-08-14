<?php

declare(strict_types=1);

namespace Omega\Support;

use Closure;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\Request;
use Omega\Http\Upload\UploadFile;
use Omega\Validator\Validator;

class AddonServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        Request::macro(
            'validate',
            fn (?Closure $rule = null, ?Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
        );

        Request::macro(
            'upload',
            function ($file_name) {
                $files = $this->{'getFile'}();

                return new UploadFile($files[$file_name]);
            }
        );
    }
}
