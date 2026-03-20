<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub;

use function array_key_exists;
use function file_get_contents;
use function function_exists;
use function gettype;
use function intval;
use function is_array;
use function is_resource;
use Laminas\Http\Php_Environment\Response as PhpResponse;
use Laminas\Stdlib\Array_Utils;
use function sprintf;
use function str_replace;
use function stream_get_contents;
use function strlen;
use function strtoupper;
use function substr;
use Traversable;
use function trim;
abstract class Abstract_Callback implements Callback_Interface
{
    /**
     * An instance of Laminas\Feed\Pubsubhubbub\Model\SubscriptionPersistenceInterface
     * used to background save any verification tokens associated with a subscription
     * or other.
     *
     * @var Model\SubscriptionPersistenceInterface
     */
    protected $storage;
    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Laminas\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Laminas\Controller\Response\Http.
     *
     * @var HttpResponse|PhpResponse
     */
    protected $http_response;
    /**
     * The input stream to use when retrieving the request body. Defaults to
     * php://input, but can be set to another value in order to force usage
     * of another input method. This should primarily be used for testing
     * purposes.
     *
     * @var resource|string String indicates a filename or stream to open;
     *     resource indicates an already created stream to use.
     */
    protected $input_stream = 'php://input';
    /**
     * The number of Subscribers for which any updates are on behalf of.
     *
     * @var int
     */
    protected $subscriber_count = 1;
    /**
     * Constructor; accepts an array or Traversable object to preset
     * options for the Subscriber without calling all supported setter
     * methods in turn.
     *
     * @param null|array|Traversable $options Options array or Traversable object
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->set_options($options);
        }
    }
    /**
     * Process any injected configuration options
     *
     * @param  array|Traversable $options Options array or Traversable object
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_options($options)
    {
        if ($options instanceof Traversable) {
            $options = Array_Utils::iterator_to_array($options);
        }
        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Array or Traversable object expected, got ' . gettype($options));
        }
        if (is_array($options)) {
            $this->set_options($options);
        }
        if (array_key_exists('storage', $options)) {
            $this->set_storage($options['storage']);
        }
        return $this;
    }
    /**
     * Send the response, including all headers.
     * If you wish to handle this via Laminas\Http, use the getter methods
     * to retrieve any data needed to be set on your HTTP Response object, or
     * simply give this object the HTTP Response instance to work with for you!
     */
    public function send_response(): void
    {
        $this->get_http_response()->send();
    }
    /**
     * Sets an instance of Laminas\Feed\Pubsubhubbub\Model\SubscriptionPersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @return $this
     */
    public function set_storage(Model\Subscription_Persistence_Interface $storage)
    {
        $this->storage = $storage;
        return $this;
    }
    /**
     * Gets an instance of Laminas\Feed\Pubsubhubbub\Model\SubscriptionPersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @return Model\SubscriptionPersistenceInterface
     * @throws Exception\RuntimeException
     */
    public function get_storage()
    {
        if ($this->storage === null) {
            throw new Exception\RuntimeException('No storage object has been set that subclasses' . ' Laminas\Feed\Pubsubhubbub\Model\SubscriptionPersistence');
        }
        return $this->storage;
    }
    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Laminas\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Laminas\Controller\Response\Http.
     *
     * @param  HttpResponse|PhpResponse $httpResponse
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_http_response($http_response)
    {
        if (!$http_response instanceof Http_Response && !$http_response instanceof Php_Response) {
            throw new Exception\InvalidArgumentException('HTTP Response object must' . ' implement one of Laminas\Feed\Pubsubhubbub\HttpResponse or' . ' Laminas\Http\PhpEnvironment\Response');
        }
        $this->http_response = $http_response;
        return $this;
    }
    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Laminas\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Laminas\Controller\Response\Http.
     *
     * @return HttpResponse|PhpResponse
     */
    public function get_http_response()
    {
        if ($this->http_response === null) {
            $this->http_response = new Http_Response();
        }
        return $this->http_response;
    }
    /**
     * Sets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     * Defaults to 1 if left unchanged.
     *
     * @param  int|string $count
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_subscriber_count($count)
    {
        $count = intval($count);
        if ($count <= 0) {
            throw new Exception\InvalidArgumentException('Subscriber count must be' . ' greater than zero');
        }
        $this->subscriber_count = $count;
        return $this;
    }
    /**
     * Gets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     *
     * @return int
     */
    public function get_subscriber_count()
    {
        return $this->subscriber_count;
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Attempt to detect the callback URL (specifically the path forward)
     *
     * @return string
     */
    protected function _detect_callback_url()
    {
        $callback_url = null;
        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        $iis_url_rewritten = $_SERVER['IIS_WasUrlRewritten'] ?? null;
        $unencoded_url = $_SERVER['UNENCODED_URL'] ?? null;
        if ('1' === $iis_url_rewritten && !empty($unencoded_url)) {
            return $unencoded_url;
        }
        // HTTP proxy requests setup request URI with scheme and host [and port]
        // + the URL path, only use URL path.
        if (isset($_SERVER['REQUEST_URI'])) {
            $callback_url = $this->build_callback_url_from_request_uri();
        }
        if (null !== $callback_url) {
            return $callback_url;
        }
        if (isset($_SERVER['ORIG_PATH_INFO'])) {
            return $this->build_callback_url_from_orig_path_info();
        }
        return '';
    }
    /**
     * Get the HTTP host
     *
     * @return string
     */
    protected function _get_http_host()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        $https = $_SERVER['HTTPS'] ?? null;
        $scheme = $https === 'on' ? 'https' : 'http';
        $name = $_SERVER['SERVER_NAME'] ?? '';
        $port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
        if ($scheme === 'http' && $port === 80 || $scheme === 'https' && $port === 443) {
            return $name;
        }
        return sprintf('%s:%d', $name, $port);
    }
    /**
     * Retrieve a Header value from either $_SERVER or Apache
     *
     * @param  string $header
     * @return bool|string
     */
    protected function _get_header($header)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }
    /**
     * Return the raw body of the request
     *
     * @return false|string Raw body, or false if not present
     */
    protected function _get_raw_body()
    {
        $body = is_resource($this->input_stream) ? stream_get_contents($this->input_stream) : file_get_contents($this->input_stream);
        return strlen(trim($body)) > 0 ? $body : false;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Build the callback URL from the REQUEST_URI server parameter.
     *
     * @return string
     */
    private function build_callback_url_from_request_uri()
    {
        $callback_url = $_SERVER['REQUEST_URI'];
        $https = $_SERVER['HTTPS'] ?? null;
        $scheme = $https === 'on' ? 'https' : 'http';
        if ($https === 'on') {
            $scheme = 'https';
        }
        $scheme_and_http_host = $scheme . '://' . $this->_get_http_host();
        if (str_starts_with((string) $callback_url, $scheme_and_http_host)) {
            return substr((string) $callback_url, strlen($scheme_and_http_host));
        }
        return $callback_url;
    }
    /**
     * Build the callback URL from the ORIG_PATH_INFO server parameter.
     *
     * @return string
     */
    private function build_callback_url_from_orig_path_info()
    {
        $callback_url = $_SERVER['ORIG_PATH_INFO'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $callback_url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return $callback_url;
    }
}