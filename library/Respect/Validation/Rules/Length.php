<?php

namespace Respect\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Exceptions\LengthException;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NotNumericException;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\InvalidException;

class Length extends AbstractRule
{

    protected $min;
    protected $max;
    protected $inclusive;

    public function __construct($min=null, $max=null, $inclusive=true)
    {
        $this->min = $min;
        $this->max = $max;
        $this->inclusive = $inclusive;
        $paramValidator = new OneOf(new Numeric, new NullValue);
        if (!$paramValidator->validate($min))
            throw new ComponentException(
                sprintf('%s is not a valid numeric length', $min)
            );

        if (!$paramValidator->validate($max))
            throw new ComponentException(
                sprintf('%s is not a valid numeric length', $max)
            );

        if (!is_null($min) && !is_null($max) && $min > $max) {
            throw new ComponentException(
                sprintf('%s cannot be less than %s for validation', $min, $max)
            );
        }
    }

    protected function extractLength($input)
    {
        if (is_string($input))
            return mb_strlen($input);
        elseif (is_array($input))
            return count($input);
        else
            return false;
    }

    public function validateMin($input)
    {
        $length = $this->extractLength($input);
        if (is_null($this->min))
            return true;
        if ($this->inclusive)
            return $length >= $this->min;
        else
            return $length > $this->min;
    }

    public function validateMax($input)
    {
        $length = $this->extractLength($input);
        if (is_null($this->max))
            return true;
        if ($this->inclusive)
            return $length <= $this->max;
        else
            return $length < $this->max;
    }

    public function validate($input)
    {
        return $this->validateMin($input) && $this->validateMax($input);
    }

    public function assert($input)
    {
        if (!$this->validate($input))
            throw $this->getException() ? : $this->createException()
                    ->configure(
                        $input, $this->min, $this->max
                    );
        return true;
    }

}