<?php

namespace mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken;

use Doctrine\ORM\EntityManagerInterface;
use mar4ehk0\BaseEntityBundle\Doctrine\Flusher;
use mar4ehk0\OCRBundle\Yandex\Repository\IAMTokenRepository;
use mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Service\GeneratorJWTToken;
use mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Service\IAMTokenClient;
use Throwable;

class CreatorIAMToken
{
    public function __construct(
        private GeneratorJWTToken $generatorJWTToken,
        private IAMTokenRepository $tokenRepository,
        private IAMTokenClient $client,
        private EntityManagerInterface $entityManager,
        private Flusher $flusher
    ) {
    }

    public function createIAMToken(): void
    {
        $this->entityManager->beginTransaction();

        try {
            $jwt = $this->generatorJWTToken->generate();
            $iamToken = $this->client->getAIMToken($jwt);

            $this->tokenRepository->add($iamToken);

            $this->clearOldTokens();

            $this->entityManager->commit();
            $this->flusher->flush();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }
    }

    private function clearOldTokens(): void
    {
        $tokens = $this->tokenRepository->findAll();

        foreach ($tokens as $token) {
            $this->tokenRepository->remove($token);
        }
    }
}
