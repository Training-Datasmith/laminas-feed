<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Http;

use Psr\Http\Message\Response_Interface as Psr7ResponseInterface;
/**
 * ResponseInterface wrapper for a PSR-7 response.
 */
class Psr7response_Decorator implements Header_Aware_Response_Interface
{
    public function __construct(private readonly Psr7response_Interface $decorated_response)
    {
    }
    /**
     * Return the original PSR-7 response being decorated.
     */
    public function get_decorated_response(): \Psr\Http\Message\Response_Interface
    {
        return $this->decorated_response;
    }
    /**
     * {@inheritDoc}
     */
    public function get_body(): string
    {
        return (string) $this->decorated_response->get_body();
    }
    /**
     * {@inheritDoc}
     */
    public function get_status_code()
    {
        return $this->decorated_response->get_status_code();
    }
    /**
     * {@inheritDoc}
     */
    public function get_header_line($name, $default = null)
    {
        if (!$this->decorated_response->has_header($name)) {
            return $default;
        }
        return $this->decorated_response->get_header_line($name);
    }
}