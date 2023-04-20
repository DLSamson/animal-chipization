<?php

namespace Api\Core\Validation\Constraints;

use Api\Core\Validation\Validators\NotEmptyStringValidator;
use Symfony\Component\Validator\Constraint;

class NotEmptyString extends Constraint
{
    public $message = 'String is empty';

    public function validatedBy()
    {
        return NotEmptyStringValidator::class;
    }
}