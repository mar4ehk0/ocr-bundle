<?php

namespace mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Service;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;

class GeneratorJWTToken
{
    public function __construct(private string $pathToYandexAuthorizedKey)
    {
    }

    public function generate(): string
    {
        $keyData = json_decode(file_get_contents($this->pathToYandexAuthorizedKey), true, 512, JSON_THROW_ON_ERROR);

        $privateKeyPem = $keyData['private_key'];
        $keyId = $keyData['id'];
        $serviceAccountId = $keyData['service_account_id'];

        if (str_starts_with($privateKeyPem, 'PLEASE DO NOT REMOVE THIS LINE!')) {
            $privateKeyPem = substr($privateKeyPem, strpos($privateKeyPem, "\n") + 1);
        }

        $jwk = JWKFactory::createFromKey(
            $privateKeyPem,
            null,
            [
                'alg' => 'PS256',
                'use' => 'sig',
                'kid' => $keyId,
            ]
        );

        $algorithmManager = new AlgorithmManager([new PS256()]);
        $jwsBuilder = new JWSBuilder($algorithmManager);

        $payload = json_encode([
            'iss' => $serviceAccountId,
            'aud' => 'https://iam.api.cloud.yandex.net/iam/v1/tokens',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 3600,
        ], JSON_THROW_ON_ERROR);

        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($jwk, ['alg' => 'PS256', 'typ' => 'JWT', 'kid' => $keyId])
            ->build();

        $serializer = new CompactSerializer();

        return $serializer->serialize($jws, 0);
    }
}
