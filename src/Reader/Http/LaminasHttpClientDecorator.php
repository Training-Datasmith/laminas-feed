<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Http;

use function implode;
use function is_array;
use function is_numeric;
use function is_string;
use Laminas\Feed\Reader\Exception;
use Laminas\Http\Client as LaminasHttpClient;
use Laminas\Http\Headers;
use function sprintf;
class Laminas_Http_Client_Decorator implements Header_Aware_Client_Interface
{
    public function __construct(private readonly Laminas_Http_Client $client)
    {
    }
    public function get_decorated_client(): \Laminas\Http\Client
    {
        return $this->client;
    }
    /**
     * {@inheritDoc}
     */
    public function get($uri, array $headers = []): \Laminas\Feed\Reader\Http\Response
    {
        $this->client->reset_parameters();
        $this->client->set_method('GET');
        $this->client->set_headers(new Headers());
        $this->client->set_uri($uri);
        if (!empty($headers)) {
            $this->inject_headers($headers);
        }
        $response = $this->client->send();
        return new Response($response->get_status_code(), $response->get_body(), $this->prepare_response_headers($response->get_headers()));
    }
    /**
     * Inject header values into the client.
     */
    private function inject_headers(array $header_values): void
    {
        $headers = $this->client->get_request()->get_headers();
        foreach ($header_values as $name => $values) {
            if (!is_string($name) || is_numeric($name) || empty($name)) {
                throw new Exception\InvalidArgumentException(sprintf('Header names provided to %s::get must be non-empty, non-numeric strings; received %s', self::class, $name));
            }
            if (!is_array($values)) {
                throw new Exception\InvalidArgumentException(sprintf('Header values provided to %s::get must be arrays of values; received %s', self::class, get_debug_type($values)));
            }
            foreach ($values as $value) {
                if (!is_string($value) && !is_numeric($value)) {
                    throw new Exception\InvalidArgumentException(sprintf('Individual header values provided to %s::get must be strings or numbers; ' . 'received %s for header %s', self::class, get_debug_type($value), $name));
                }
                $headers->add_header_line($name, $value);
            }
        }
    }
    /**
     * Normalize headers to use with HeaderAwareResponseInterface.
     *
     * Ensures multi-value headers are represented as a single string, via
     * comma concatenation.
     */
    private function prepare_response_headers(Headers $headers): array
    {
        $normalized = [];
        foreach ($headers->to_array() as $name => $value) {
            $normalized[$name] = is_array($value) ? implode(', ', $value) : $value;
        }
        return $normalized;
    }
}