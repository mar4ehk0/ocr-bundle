<?php

namespace mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Exception;

use RuntimeException;

class CouldNotSendRequestForIAMTokenException extends RuntimeException
{
    public static function withBody(string $body): self
    {
        $msg = sprintf('Could not send request for IAM Token with BODY: %s', $body);

        return new self($msg);
    }

    public static function withJwt(string $jwt): self
    {
        $msg = sprintf('Could not send request for IAM Token with JWT: %s', $jwt);

        return new self($msg);
    }
}
