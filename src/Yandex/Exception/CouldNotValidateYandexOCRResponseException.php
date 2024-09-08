<?php

namespace mar4ehk0\OCRBundle\Yandex\Exception;

use Exception;
use Throwable;

class CouldNotValidateYandexOCRResponseException extends Exception
{
    private function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, $code = 0, $previous);
    }

    public static function thatEmptyResponse(): self
    {
        return new self('Could not validate response from Yandex OCR that is empty.');
    }

    public static function thatWrongField(string $fieldName, string $description): self
    {
        return new self(sprintf('Could not validate response from Yandex OCR that field: %s, error description: %s', $fieldName, $description));
    }

    public static function hasException(Exception $e): self
    {
        return new self('Could not validate response from Yandex OCR that has exception', $e);
    }
}
