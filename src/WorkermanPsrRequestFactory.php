<?php

declare(strict_types=1);

namespace Chiron\Workerman;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Workerman\Protocols\Http\Request as WorkermanRequest;

final class WorkermanPsrRequestFactory
{
    /**
     * @var ServerRequestFactoryInterface
     */
    private $serverRequestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var UploadedFileFactoryInterface
     */
    private $uploadedFileFactory;

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory,
        StreamFactoryInterface $streamFactory,
        UploadedFileFactoryInterface $uploadedFileFactory
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->streamFactory = $streamFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
    }

    public function toPsrRequest(WorkermanRequest $workermanRequest): ServerRequestInterface
    {
        $request = $this->serverRequestFactory->createServerRequest(
            $workermanRequest->method(),
            $workermanRequest->uri()
        );

        foreach ($workermanRequest->header() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $request = $request->withCookieParams($workermanRequest->cookie());
        $request = $request->withQueryParams($workermanRequest->get());
        $request = $request->withParsedBody($workermanRequest->post());
        $request = $request->withUploadedFiles($this->uploadedFiles($workermanRequest->file()));

        $request->getBody()->write($workermanRequest->rawBody());

        return $request;
    }

    /**
     * @param array<string, array<string, int|string>> $files
     *
     * @return array<string, UploadedFileInterface>
     */
    private function uploadedFiles(array $files): array
    {
        $uploadedFiles = [];
        foreach ($files as $key => $file) {
            if (isset($file['tmp_name'])) {
                $uploadedFiles[$key] = $this->createUploadedFile($file);
            } else {
                $uploadedFiles[$key] = $this->uploadedFiles($file);
            }
        }

        return $uploadedFiles;
    }

    /**
     * @param array<string, int|string> $file
     */
    private function createUploadedFile(array $file): UploadedFileInterface
    {
        try {
            $stream = $this->streamFactory->createStreamFromFile($file['tmp_name']);
        } catch (\RuntimeException $exception) {
            $stream = $this->streamFactory->createStream();
        }

        return $this->uploadedFileFactory->createUploadedFile(
            $stream,
            $file['size'],
            $file['error'],
            $file['name'],
            $file['type']
        );
    }

}
