<?php

namespace mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Exception;

use InvalidArgumentException;

class CouldNotValidateResponseException extends InvalidArgumentException
{
    public static function thatEmptyResponse(): self
    {
        return new self('Could not validate response that is empty');
    }

    public static function thatFieldIamTokenEmpty(): self
    {
        return new self('Could not validate response that field "iamToken" is empty');
    }
}
