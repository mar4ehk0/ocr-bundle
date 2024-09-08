<?php

namespace mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Service;

use JsonException;
use mar4ehk0\OCRBundle\Yandex\Entity\IAMToken;
use mar4ehk0\OCRBundle\Yandex\Factory\IAMTokenFactory;
use mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Exception\CouldNotSendRequestForIAMTokenException;
use mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\Exception\CouldNotValidateResponseException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class IAMTokenClient
{
    public function __construct(
        private HttpClientInterface $client,
        private IAMTokenFactory $IAMTokenFactory,
        private readonly string $yandexIamTokenV1Url
    ) {
    }

    public function getAIMToken(string $jwt): IAMToken
    {
        try {
            $body = json_encode(['jwt' => $jwt], JSON_THROW_ON_ERROR);

            $response = $this->client->request(
                'POST',
                $this->yandexIamTokenV1Url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => '*/*',
                    ],
                    'body' => $body,
                ]
            );

            $this->validateResponse($response);

            $value = $response->toArray()['iamToken'];

            return $this->IAMTokenFactory->create($value);
        } catch (JsonException $e) {
            throw CouldNotSendRequestForIAMTokenException::withJwt($jwt);
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            // it does not work with my vpn
            throw CouldNotSendRequestForIAMTokenException::withBody($body);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        $content = $response->toArray();

        if (empty($content)) {
            throw CouldNotValidateResponseException::thatEmptyResponse();
        }

        if (empty($content['iamToken'])) {
            throw CouldNotValidateResponseException::thatFieldIamTokenEmpty();
        }
    }
}
