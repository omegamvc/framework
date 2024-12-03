<?php

/**
 * Part of Omega CMS - Validation Package.
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Validation\ServiceProvider;

/*
 * @use
 */
use Omega\Application\Application;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Validation\Validation;
use Omega\Validation\Rule\EmailRule;
use Omega\Validation\Rule\IntegerRule;
use Omega\Validation\Rule\MinRule;
use Omega\Validation\Rule\RequiredRule;

/**
 * Validation service provider class.
 *
 * The `ValidationServiceProvider` class binds validation-related components to the
 * application container. It registers validation rules and a ValidationManager instance
 * for handling validation.
 *
 * @category    Omega
 * @package     Validation
 * @subpackage  ServiceProvider
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class ValidationServiceProvider implements ServiceProviderInterface
{
    /**
     * Bind all method.
     *
     * @param Application $application Holds an instance of Application.
     *
     * @return void
     */
    public function bind(Application $application): void
    {
        $application->alias('validator', function ($application) {
            $validation = new Validation();

            $this->bindRules($application, $validation);

            return $validation;
        });
    }

    /**
     * Bind rules.
     *
     * Registers predefined validation rules in the ValidationManager.
     *
     * @param Application $application Holds an instance of Application.
     * @param Validation  $validation  Holds an instance of ValidationManager.
     *
     * @return void
     */
    private function bindRules(Application $application, Validation $validation): void
    {
        // Create rule instances directly
        $requiredRule = new RequiredRule();
        $emailRule    = new EmailRule();
        $minRule      = new MinRule();
        $integerRule  = new IntegerRule();

        // Add rules to the validation instance
        $validation->addRule('required', $requiredRule);
        $validation->addRule('email', $emailRule);
        $validation->addRule('min', $minRule);
        $validation->addRule('integer', $integerRule);
    }
}
