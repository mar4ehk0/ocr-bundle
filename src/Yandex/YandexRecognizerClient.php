<?php

namespace mar4ehk0\OCRBundle\Yandex;

use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JsonException;
use mar4ehk0\OCRBundle\Exception\CouldNotRecognizeTextException;
use mar4ehk0\OCRBundle\RecognizerClientInterface;
use mar4ehk0\OCRBundle\Yandex\Exception\CouldNotValidateYandexOCRResponseException;
use mar4ehk0\OCRBundle\Yandex\Repository\IAMTokenRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class YandexRecognizerClient implements RecognizerClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private IAMTokenRepository $IAMTokenRepository,
        private readonly string $yandexXFolderId,
        private readonly string $yandexRecognizeTextV1Url,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws CouldNotRecognizeTextException
     */
    public function sendRequest(string $filePath): string
    {
        try {
            $body = $this->createBody($filePath);

            $token = $this->IAMTokenRepository->getActiveToken(new DateTimeImmutable());

            $response = $this->client->request(
                'POST',
                $this->yandexRecognizeTextV1Url,
                [
                    'auth_bearer' => $token->getValue(),
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => '*/*',
                        'x-folder-id' => $this->yandexXFolderId,
                    ],
                    'body' => $body,
                ]
            );

            $this->validateResponse($response);

            return $response->toArray()['result']['textAnnotation']['fullText'];
        } catch (JsonException $e) {
            $this->logger->error(
                sprintf(
                    'Could not json encode data for sending response to Yandex OCR. Exception: %s. File: %s',
                    JsonException::class,
                    $filePath
                ),
                ['exception' => $e]
            );

            throw CouldNotRecognizeTextException::withFilePath($filePath, $e);
        } catch (NonUniqueResultException|NoResultException $e) {
            $this->logger->error(
                sprintf(
                    'Check Token for Yandex OCR. It does not exit or there are several!. File: %s',
                    $filePath
                ),
                ['exception' => $e]
            );

            throw CouldNotRecognizeTextException::withFilePathThatWrongToken($filePath, $e);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                sprintf(
                    'Could not send Request to Yandex OCR that client has exception. File: %s',
                    $filePath
                ),
                ['exception' => $e]
            );

            throw CouldNotRecognizeTextException::withFilePathThatClientHasException($filePath, $e);
        } catch (CouldNotValidateYandexOCRResponseException $e) {
            $this->logger->error(
                sprintf('Could not validate Response from Yandex OCR. File: %s', $filePath),
                ['exception' => $e]
            );

            throw CouldNotRecognizeTextException::withFilePathThatWasNotValid($filePath, $e);
        }
    }

    /**
     * @throws JsonException
     */
    private function createBody(string $filePath): string
    {
        $content = file_get_contents($filePath);
        $encodedContent = base64_encode($content);

        return json_encode(
            [
                // это надо вытаскивать в настройки
                'mimeType' => 'JPEG',
                'languageCodes' => ['*'],
                'model' => 'page',
                'content' => $encodedContent,
            ],
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @throws CouldNotValidateYandexOCRResponseException
     */
    private function validateResponse(ResponseInterface $response): void
    {
        try {
            $content = $response->toArray();
        } catch (
            ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e
        ) {
            throw CouldNotValidateYandexOCRResponseException::hasException($e);
        }

        if (empty($content)) {
            throw CouldNotValidateYandexOCRResponseException::thatEmptyResponse();
        }

        if (empty($content['result'])) {
            throw CouldNotValidateYandexOCRResponseException::thatWrongField('result', 'is empty');
        }

        if (empty($content['result']['textAnnotation'])) {
            throw CouldNotValidateYandexOCRResponseException::thatWrongField('result->textAnnotation', 'is empty');
        }

        if (empty($content['result']['textAnnotation']['fullText'])) {
            throw CouldNotValidateYandexOCRResponseException::thatWrongField('result->textAnnotation->fullText', 'is empty');
        }
    }
}
