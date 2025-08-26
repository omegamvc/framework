<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use Exception;
use Omega\Console\Style\Alert;
use Omega\Console\Style\Style;
use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;

trait ValidateCommandTrait
{
    protected Validator $validate;

    /**
     * @param array<string, string|bool|int|null> $inputs
     */
    protected function initValidate(array $inputs): void
    {
        $this->validate = new Validator($inputs);
        $this->validate->validation(
            fn (ValidPool $rules) => $this->validateRule($rules)
        );
    }

    /**
     * @param ValidPool $rules
     * @return void
     */
    protected function validateRule(ValidPool $rules): void
    {
    }

    /**
     * @return bool
     */
    protected function isValid(): bool
    {
        return $this->validate->isValid();
    }

    /**
     * @param Style $style
     * @return Style
     * @throws Exception
     */
    protected function getValidateMessage(Style $style): Style
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($this->validate->getError() as $input => $message) {
            $style->tap(
                Alert::render()->warn($message)
            );
        }

        return $style;
    }
}
