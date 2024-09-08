<?php

namespace mar4ehk0\OCRBundle\Yandex\Factory;

use DateInterval;
use DateTimeImmutable;
use mar4ehk0\OCRBundle\Yandex\Entity\IAMToken;
use Symfony\Component\Uid\Factory\UlidFactory;

class IAMTokenFactory
{
    public function __construct(
        private UlidFactory $ulidFactory
    ) {
    }

    public function create(string $value, DateInterval $interval = new DateInterval('PT1H')): IAMToken
    {
        $id = $this->ulidFactory->create();
        $now = new DateTimeImmutable();
        $expired = $now->add($interval);

        return new IAMToken($id, $value, $expired);
    }
}
