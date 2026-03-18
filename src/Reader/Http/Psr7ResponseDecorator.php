<?php

declare(strict_types=1);

namespace Laminas\Feed\Reader\Http;

use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

/**
 * ResponseInterface wrapper for a PSR-7 response.
 */
class Psr7ResponseDecorator implements HeaderAwareResponseInterface
{
    public function __construct(private readonly Psr7ResponseInterface $decoratedResponse)
    {
    }

    /**
     * Return the original PSR-7 response being decorated.
     */
    public function getDecoratedResponse(): \Psr\Http\Message\ResponseInterface
    {
        return $this->decoratedResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): string
    {
        return (string) $this->decoratedResponse->getBody();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->decoratedResponse->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name, $default = null)
    {
        if (! $this->decoratedResponse->hasHeader($name)) {
            return $default;
        }
        return $this->decoratedResponse->getHeaderLine($name);
    }
}
