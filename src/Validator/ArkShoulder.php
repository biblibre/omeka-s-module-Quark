<?php

namespace Quark\Validator;

use Laminas\Validator\AbstractValidator;

class ArkShoulder extends AbstractValidator
{
    const STRING = 'string';
    const EMPTY = 'empty';
    const BETANUMERIC = 'betanumeric';
    const END_DIGIT = 'end_digit';
    const START_LETTER = 'start_letter';

    protected $messageTemplates = [
        self::STRING => 'value is not a string', // @translate
        self::EMPTY => 'value is empty', // @translate
        self::BETANUMERIC => "'%value%' contains non-betanumeric characters", // @translate
        self::END_DIGIT => "'%value%' does not end with a digit", // @translate
        self::START_LETTER => "'%value%' does not start with a letter", // @translate
    ];

    public function isValid($value)
    {
        $this->setValue($value);

        if (!is_string($value)) {
            $this->error(self::STRING);
            return false;
        }

        if ($value === '') {
            $this->error(self::EMPTY);
            return false;
        }

        if (!preg_match('/^[0123456789bcdfghjkmnpqrstvwxz]+$/', $value)) {
            $this->error(self::BETANUMERIC);
            return false;
        }

        if (!preg_match('/[0-9]$/', $value)) {
            $this->error(self::END_DIGIT);
            return false;
        }

        if (!preg_match('/^[bcdfghjkmnpqrstvwxz]/', $value)) {
            $this->error(self::START_LETTER);
            return false;
        }

        return true;
    }
}
