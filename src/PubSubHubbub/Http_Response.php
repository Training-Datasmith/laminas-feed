<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub;

use function header;
use function headers_sent;
use function is_int;
use function str_replace;
use function strlen;
use function strtolower;
use function ucwords;
class Http_Response
{
    /**
     * The body of any response to the current callback request
     *
     * @var string
     */
    protected $content = '';
    /**
     * Array of headers. Each header is an array with keys 'name' and 'value'
     *
     * @var array
     */
    protected $headers = [];
    /**
     * HTTP response code to use in headers
     *
     * @var int
     */
    protected $status_code = 200;
    /**
     * Send the response, including all headers
     */
    public function send(): void
    {
        $this->send_headers();
        echo $this->get_content();
    }
    /**
     * Send all headers
     *
     * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
     * has been specified, it is sent with the first header.
     */
    public function send_headers(): void
    {
        if (200 === $this->status_code) {
            return;
        }
        if ($this->headers || 200 !== $this->status_code) {
            $this->can_send_headers(true);
        }
        $http_code_sent = false;
        foreach ($this->headers as $header) {
            if (!$http_code_sent && $this->status_code) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->status_code);
                $http_code_sent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }
        if (!$http_code_sent) {
            header('HTTP/1.1 ' . $this->status_code);
        }
    }
    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param  string $name
     * @param  string $value
     * @param  bool $replace
     * @return $this
     */
    public function set_header($name, $value, $replace = false): static
    {
        $name = $this->_normalize_header($name);
        $value = (string) $value;
        if ($replace) {
            foreach ($this->headers as $key => $header) {
                if ($name === $header['name']) {
                    unset($this->headers[$key]);
                }
            }
        }
        $this->headers[] = ['name' => $name, 'value' => $value, 'replace' => $replace];
        return $this;
    }
    /**
     * Check if a specific Header is set and return its value
     *
     * @param  string $name
     * @return string|null
     */
    public function get_header($name)
    {
        $name = $this->_normalize_header($name);
        foreach ($this->headers as $header) {
            if ($header['name'] === $name) {
                return $header['value'];
            }
        }
    }
    /**
     * Return array of headers; see {@link $headers} for format
     *
     * @return array
     */
    public function get_headers()
    {
        return $this->headers;
    }
    /**
     * Can we send headers?
     *
     * @param  bool $throw Whether or not to throw an exception if headers have been sent; defaults to false
     * @throws Exception\RuntimeException
     */
    public function can_send_headers($throw = false): bool
    {
        $ok = headers_sent($file, $line);
        if ($ok && $throw) {
            throw new Exception\RuntimeException('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
        }
        return !$ok;
    }
    /**
     * Set HTTP response code to use with headers
     *
     * @param  int $code
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_status_code($code): static
    {
        if (!is_int($code) || 100 > $code || 599 < $code) {
            throw new Exception\InvalidArgumentException('Invalid HTTP response code: ' . $code);
        }
        $this->status_code = $code;
        return $this;
    }
    /**
     * Retrieve HTTP response code
     *
     * @return int
     */
    public function get_status_code()
    {
        return $this->status_code;
    }
    /**
     * Set body content
     *
     * @param  string $content
     * @return $this
     */
    public function set_content($content): static
    {
        $this->content = (string) $content;
        $this->set_header('content-length', strlen($content));
        return $this;
    }
    /**
     * Return the body content
     *
     * @return string
     */
    public function get_content()
    {
        return $this->content;
    }
    /**
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _normalize_header($name): string
    {
        $filtered = str_replace(['-', '_'], ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        return str_replace(' ', '-', $filtered);
    }
}