<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub;

use function array_key_exists;
use function array_search;
use function array_unique;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use Laminas\Feed\Uri;
use Laminas\Http\Client;
use Laminas\Http\Request as HttpRequest;
use Laminas\Stdlib\Array_Utils;
use Traversable;
use function urlencode;
class Publisher
{
    /**
     * An array of URLs for all Hub Servers used by the Publisher, and to
     * which all topic update notifications will be sent.
     *
     * @var array
     */
    protected $hub_urls = [];
    /**
     * An array of topic (Atom or RSS feed) URLs which have been updated and
     * whose updated status will be notified to all Hub Servers.
     *
     * @var array
     */
    protected $updated_topic_urls = [];
    /**
     * An array of any errors including keys for 'response', 'hubUrl'.
     * The response is the actual Laminas\Http\Response object.
     *
     * @var array
     */
    protected $errors = [];
    /**
     * An array of topic (Atom or RSS feed) URLs which have been updated and
     * whose updated status will be notified to all Hub Servers.
     *
     * @var array
     */
    protected $parameters = [];
    /**
     * Constructor; accepts an array or Laminas\Config\Config instance to preset
     * options for the Publisher without calling all supported setter
     * methods in turn.
     *
     * @param null|array|Traversable $options
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
    public function set_options($options): static
    {
        if ($options instanceof Traversable) {
            $options = Array_Utils::iterator_to_array($options);
        }
        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Array or Traversable object expected, got ' . gettype($options));
        }
        if (array_key_exists('hubUrls', $options)) {
            $this->add_hub_urls($options['hubUrls']);
        }
        if (array_key_exists('updatedTopicUrls', $options)) {
            $this->add_updated_topic_urls($options['updatedTopicUrls']);
        }
        if (array_key_exists('parameters', $options)) {
            $this->set_parameters($options['parameters']);
        }
        return $this;
    }
    /**
     * Add a Hub Server URL supported by Publisher
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function add_hub_url(string $url): static
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url" of "' . $url . '" must be a non-empty string and a valid URL');
        }
        $this->hub_urls[] = $url;
        return $this;
    }
    /**
     * Add an array of Hub Server URLs supported by Publisher
     *
     * @return $this
     */
    public function add_hub_urls(array $urls): static
    {
        foreach ($urls as $url) {
            $this->add_hub_url($url);
        }
        return $this;
    }
    /**
     * Remove a Hub Server URL
     *
     * @param  string $url
     * @return $this
     */
    public function remove_hub_url($url): static
    {
        if (!in_array($url, $this->get_hub_urls())) {
            return $this;
        }
        $key = array_search($url, $this->hub_urls);
        unset($this->hub_urls[$key]);
        return $this;
    }
    /**
     * Return an array of unique Hub Server URLs currently available
     *
     * @return array
     */
    public function get_hub_urls()
    {
        $this->hub_urls = array_unique($this->hub_urls);
        return $this->hub_urls;
    }
    /**
     * Add a URL to a topic (Atom or RSS feed) which has been updated
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function add_updated_topic_url(string $url): static
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url" of "' . $url . '" must be a non-empty string and a valid URL');
        }
        $this->updated_topic_urls[] = $url;
        return $this;
    }
    /**
     * Add an array of Topic URLs which have been updated
     *
     * @return $this
     */
    public function add_updated_topic_urls(array $urls): static
    {
        foreach ($urls as $url) {
            $this->add_updated_topic_url($url);
        }
        return $this;
    }
    /**
     * Remove an updated topic URL
     *
     * @param  string $url
     * @return $this
     */
    public function remove_updated_topic_url($url): static
    {
        if (!in_array($url, $this->get_updated_topic_urls())) {
            return $this;
        }
        $key = array_search($url, $this->updated_topic_urls);
        unset($this->updated_topic_urls[$key]);
        return $this;
    }
    /**
     * Return an array of unique updated topic URLs currently available
     *
     * @return array
     */
    public function get_updated_topic_urls()
    {
        $this->updated_topic_urls = array_unique($this->updated_topic_urls);
        return $this->updated_topic_urls;
    }
    /**
     * Notifies a single Hub Server URL of changes
     *
     * @param  string $url The Hub Server's URL
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function notify_hub($url): void
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url" of "' . $url . '" must be a non-empty string and a valid URL');
        }
        $client = $this->_get_http_client();
        $client->set_uri($url);
        $response = $client->get_response();
        if ($response->get_status_code() !== 204) {
            throw new Exception\RuntimeException('Notification to Hub Server at "' . $url . '" appears to have failed with a status code of' . ' "' . $response->get_status_code() . '" and message "' . $response->get_content() . '"');
        }
    }
    /**
     * Notifies all Hub Server URLs of changes
     *
     * If a Hub notification fails, certain data will be retained in an
     * an array retrieved using getErrors(), if a failure occurs for any Hubs
     * the isSuccess() check will return FALSE. This method is designed not
     * to needlessly fail with an Exception/Error unless from Laminas\Http\Client.
     *
     * @throws Exception\RuntimeException
     */
    public function notify_all(): void
    {
        $client = $this->_get_http_client();
        $hubs = $this->get_hub_urls();
        if (empty($hubs)) {
            throw new Exception\RuntimeException('No Hub Server URLs have been set so no notifications can be sent');
        }
        $this->errors = [];
        foreach ($hubs as $url) {
            $client->set_uri($url);
            $client->send();
            $response = $client->get_response();
            if ($response->get_status_code() !== 204) {
                $this->errors[] = ['response' => $response, 'hubUrl' => $url];
            }
        }
    }
    /**
     * Add an optional parameter to the update notification requests
     *
     * @param  string|array<string, string> $name
     * @param  null|string $value
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_parameter($name, $value = null): static
    {
        if (is_array($name)) {
            $this->set_parameters($name);
            return $this;
        }
        if (empty($name) || !is_string($name)) {
            throw new Exception\InvalidArgumentException('Invalid parameter "name" of "' . $name . '" must be a non-empty string');
        }
        if ($value === null) {
            $this->remove_parameter($name);
            return $this;
        }
        if (empty($value) || !is_string($value) && $value !== null) {
            throw new Exception\InvalidArgumentException('Invalid parameter "value" of "' . $value . '" must be a non-empty string');
        }
        $this->parameters[$name] = $value;
        return $this;
    }
    /**
     * Add an optional parameter to the update notification requests
     *
     * @return $this
     */
    public function set_parameters(array $parameters): static
    {
        foreach ($parameters as $name => $value) {
            $this->set_parameter($name, $value);
        }
        return $this;
    }
    /**
     * Remove an optional parameter for the notification requests
     *
     * @param  string $name
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function remove_parameter($name): static
    {
        if (empty($name) || !is_string($name)) {
            throw new Exception\InvalidArgumentException('Invalid parameter "name" of "' . $name . '" must be a non-empty string');
        }
        if (array_key_exists($name, $this->parameters)) {
            unset($this->parameters[$name]);
        }
        return $this;
    }
    /**
     * Return an array of optional parameters for notification requests
     *
     * @return array
     */
    public function get_parameters()
    {
        return $this->parameters;
    }
    /**
     * Returns a boolean indicator of whether the notifications to Hub
     * Servers were ALL successful. If even one failed, FALSE is returned.
     */
    public function is_success(): bool
    {
        return !$this->errors;
    }
    /**
     * Return an array of errors met from any failures, including keys:
     * 'response' => the Laminas\Http\Response object from the failure
     * 'hubUrl' => the URL of the Hub Server whose notification failed
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }
    /**
     * Get a basic prepared HTTP client for use
     *
     * @return Client
     * @throws Exception\RuntimeException
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _get_http_client()
    {
        $client = Pub_Sub_Hubbub::get_http_client();
        $client->set_method(Http_Request::METHOD_POST);
        $client->set_options(['useragent' => 'Laminas_Feed_Pubsubhubbub_Publisher/' . Version::VERSION]);
        $params = [];
        $params[] = 'hub.mode=publish';
        $topics = $this->get_updated_topic_urls();
        if (empty($topics)) {
            throw new Exception\RuntimeException('No updated topic URLs have been set');
        }
        foreach ($topics as $topic_url) {
            $params[] = 'hub.url=' . urlencode((string) $topic_url);
        }
        $opt_params = $this->get_parameters();
        foreach ($opt_params as $name => $value) {
            $params[] = urlencode((string) $name) . '=' . urlencode((string) $value);
        }
        $param_string = implode('&', $params);
        $client->set_raw_body($param_string);
        return $client;
    }
}