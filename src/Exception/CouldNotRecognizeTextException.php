<?php

namespace mar4ehk0\OCRBundle\Exception;

use Exception;
use Throwable;

class CouldNotRecognizeTextException extends Exception
{
    private const MESSAGE = 'Could not send request to OCR service for file path: %s ';

    private function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, $code = 0, $previous);
    }

    public static function withFilePath(string $filePath, ?Throwable $previous = null): self
    {
        $msg = sprintf(self::MESSAGE, $filePath);

        return new self($msg, $previous);
    }

    public static function withFilePathThatWrongToken(string $filePath, ?Throwable $previous = null): self
    {
        $msg = sprintf(self::MESSAGE . 'that wrong token.', $filePath);

        return new self($msg, $previous);
    }

    public static function withFilePathThatClientHasException(string $filePath, ?Throwable $previous = null): self
    {
        $msg = sprintf(self::MESSAGE . 'that client has exception .', $filePath);

        return new self($msg, $previous);
    }

    public static function withFilePathThatWasNotValid(string $filePath, ?Throwable $previous = null): self
    {
        $msg = sprintf('Could not validate Response from OCR service for File: %s.', $filePath);

        return new self($msg, $previous);
    }
}
