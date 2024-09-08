<?php

namespace mar4ehk0\OCRBundle;

use mar4ehk0\OCRBundle\Exception\CouldNotRecognizeTextException;

interface RecognizerClientInterface
{
    /**
     * @throws CouldNotRecognizeTextException
     */
    public function sendRequest(string $filePath): string;
}
