<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Http;

use function get_debug_type;
use function intval;
use function is_numeric;
use function is_object;
use function is_string;
use Laminas\Feed\Reader\Exception;
use function method_exists;
use function sprintf;
use function strtolower;
use function trim;
class Response implements Header_Aware_Response_Interface
{
    private readonly string $body;
    private array $headers;
    private readonly int $status_code;
    /**
     * @param  int $statusCode
     * @param  object|string $body
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($status_code, $body = '', array $headers = [])
    {
        $this->validate_status_code($status_code);
        $this->validate_body($body);
        $this->validate_headers($headers);
        $this->status_code = (int) $status_code;
        $this->body = (string) $body;
        $this->headers = $this->normalize_headers($headers);
    }
    /**
     * {@inheritDoc}
     */
    public function get_status_code(): int
    {
        return $this->status_code;
    }
    /**
     * {@inheritDoc}
     */
    public function get_body(): string
    {
        return $this->body;
    }
    /**
     * {@inheritDoc}
     */
    public function get_header_line($name, $default = null)
    {
        $normalized_name = strtolower($name);
        return $this->headers[$normalized_name] ?? $default;
    }
    /**
     * Validate that we have a status code argument that will work for our context.
     *
     * @param int $statusCode
     * @throws Exception\InvalidArgumentException For arguments not castable
     *     to integer HTTP status codes.
     */
    private function validate_status_code($status_code): void
    {
        if (!is_numeric($status_code) || is_string($status_code) && trim($status_code) !== $status_code) {
            throw new Exception\InvalidArgumentException(sprintf('%s expects a numeric status code; received %s', self::class, get_debug_type($status_code)));
        }
        if (100 > $status_code || 599 < $status_code) {
            throw new Exception\InvalidArgumentException(sprintf('%s expects an integer status code between 100 and 599 inclusive; received %s', self::class, $status_code));
        }
        if (intval($status_code) !== $status_code) {
            throw new Exception\InvalidArgumentException(sprintf('%s expects an integer status code; received %s', self::class, $status_code));
        }
    }
    /**
     * Validate that we have a body argument that will work for our context.
     *
     * @param mixed $body
     * @throws Exception\InvalidArgumentException For arguments not castable
     *     to strings.
     */
    private function validate_body($body): void
    {
        if (is_string($body)) {
            return;
        }
        if (is_object($body) && method_exists($body, '__toString')) {
            return;
        }
        throw new Exception\InvalidArgumentException(sprintf('%s expects a string body, or an object that can cast to string; received %s', self::class, get_debug_type($body)));
    }
    /**
     * Validate header values.
     *
     * @throws Exception\InvalidArgumentException
     */
    private function validate_headers(array $headers): void
    {
        foreach ($headers as $name => $value) {
            if (!is_string($name) || is_numeric($name) || empty($name)) {
                throw new Exception\InvalidArgumentException(sprintf('Header names provided to %s must be non-empty, non-numeric strings; received %s', self::class, $name));
            }
            if (!is_string($value) && !is_numeric($value)) {
                throw new Exception\InvalidArgumentException(sprintf('Individual header values provided to %s must be a string or numeric; received %s for header %s', self::class, get_debug_type($value), $name));
            }
        }
    }
    /**
     * Normalize header names to lowercase.
     */
    private function normalize_headers(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower((string) $name)] = $value;
        }
        return $normalized;
    }
}