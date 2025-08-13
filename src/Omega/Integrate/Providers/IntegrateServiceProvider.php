<?php

declare(strict_types=1);

namespace Omega\Integrate\Providers;

use Omega\Http\Upload\UploadFile;
use Omega\Http\Request;
use Omega\Integrate\ServiceProvider;
use Validator\Validator;

class IntegrateServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        Request::macro(
            'validate',
            fn (?\Closure $rule = null, ?\Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
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
