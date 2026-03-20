<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub;

use function array_key_exists;
use function array_search;
use function array_unique;
use DateInterval;
use DateTime;
use function gettype;
use function hash;
use function implode;
use function in_array;
use function intval;
use function is_array;
use function is_string;
use Laminas\Feed\Uri;
use Laminas\Http\Client;
use Laminas\Http\Request as HttpRequest;
use Laminas\Stdlib\Array_Utils;
use function md5;
use function random_bytes;
use function rtrim;
use function time;
use Traversable;
use function uksort;
class Subscriber
{
    /**
     * An array of URLs for all Hub Servers to subscribe/unsubscribe.
     *
     * @var array
     */
    protected $hub_urls = [];
    /**
     * An array of optional parameters to be included in any
     * (un)subscribe requests.
     *
     * @var array
     */
    protected $parameters = [];
    /**
     * The URL of the topic (Rss or Atom feed) which is the subject of
     * our current intent to subscribe to/unsubscribe from updates from
     * the currently configured Hub Servers.
     *
     * @var string
     */
    protected $topic_url = '';
    /**
     * The URL Hub Servers must use when communicating with this Subscriber
     *
     * @var string
     */
    protected $callback_url = '';
    /**
     * The number of seconds for which the subscriber would like to have the
     * subscription active. Defaults to null, i.e. not sent, to setup a
     * permanent subscription if possible.
     *
     * @var int
     */
    protected $lease_seconds;
    /**
     * The preferred verification mode (sync or async). By default, this
     * Subscriber prefers synchronous verification, but is considered
     * desirable to support asynchronous verification if possible.
     *
     * Laminas\Feed\Pubsubhubbub\Subscriber will always send both modes, whose
     * order of occurrence in the parameter list determines this preference.
     *
     * @var string
     */
    protected $preferred_verification_mode = Pub_Sub_Hubbub::VERIFICATION_MODE_SYNC;
    /**
     * An array of any errors including keys for 'response', 'hubUrl'.
     * The response is the actual Laminas\Http\Response object.
     *
     * @var array
     */
    protected $errors = [];
    /**
     * An array of Hub Server URLs for Hubs operating at this time in
     * asynchronous verification mode.
     *
     * @var array
     */
    protected $async_hubs = [];
    /**
     * An instance of Laminas\Feed\Pubsubhubbub\Model\SubscriptionPersistence used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @var Model\SubscriptionPersistenceInterface
     */
    protected $storage;
    /**
     * An array of authentication credentials for HTTP Basic Authentication
     * if required by specific Hubs. The array is indexed by Hub Endpoint URI
     * and the value is a simple array of the username and password to apply.
     *
     * @var array
     */
    protected $authentications = [];
    /**
     * Tells the Subscriber to append any subscription identifier to the path
     * of the base Callback URL. E.g. an identifier "subkey1" would be added
     * to the callback URL "http://www.example.com/callback" to create a subscription
     * specific Callback URL of "http://www.example.com/callback/subkey1".
     *
     * This is required for all Hubs using the Pubsubhubbub 0.1 Specification.
     * It should be manually intercepted and passed to the Callback class using
     * Laminas\Feed\Pubsubhubbub\Subscriber\Callback::setSubscriptionKey(). Will
     * require a route in the form "callback/:subkey" to allow the parameter be
     * retrieved from an action using the Laminas\Controller\Action::\getParam()
     * method.
     *
     * @var string
     */
    protected $use_path_parameter = false;
    /**
     * Constructor; accepts an array or Traversable instance to preset
     * options for the Subscriber without calling all supported setter
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
     * @param  array|Traversable $options
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
        if (array_key_exists('callbackUrl', $options)) {
            $this->set_callback_url($options['callbackUrl']);
        }
        if (array_key_exists('topicUrl', $options)) {
            $this->set_topic_url($options['topicUrl']);
        }
        if (array_key_exists('storage', $options)) {
            $this->set_storage($options['storage']);
        }
        if (array_key_exists('leaseSeconds', $options)) {
            $this->set_lease_seconds($options['leaseSeconds']);
        }
        if (array_key_exists('parameters', $options)) {
            $this->set_parameters($options['parameters']);
        }
        if (array_key_exists('authentications', $options)) {
            $this->add_authentications($options['authentications']);
        }
        if (array_key_exists('usePathParameter', $options)) {
            $this->use_path_parameter($options['usePathParameter']);
        }
        if (array_key_exists('preferredVerificationMode', $options)) {
            $this->set_preferred_verification_mode($options['preferredVerificationMode']);
        }
        return $this;
    }
    /**
     * Set the topic URL (RSS or Atom feed) to which the intended (un)subscribe
     * event will relate
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_topic_url(string $url): static
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url" of "' . $url . '" must be a non-empty string and a valid URL');
        }
        $this->topic_url = $url;
        return $this;
    }
    /**
     * Set the topic URL (RSS or Atom feed) to which the intended (un)subscribe
     * event will relate
     *
     * @return string
     * @throws Exception\RuntimeException
     */
    public function get_topic_url()
    {
        if (empty($this->topic_url)) {
            throw new Exception\RuntimeException('A valid Topic (RSS or Atom feed) URL MUST be set before attempting any operation');
        }
        return $this->topic_url;
    }
    /**
     * Set the number of seconds for which any subscription will remain valid
     *
     * @param  int $seconds
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_lease_seconds($seconds): static
    {
        $seconds = intval($seconds);
        if ($seconds <= 0) {
            throw new Exception\InvalidArgumentException('Expected lease seconds must be an integer greater than zero');
        }
        $this->lease_seconds = $seconds;
        return $this;
    }
    /**
     * Get the number of lease seconds on subscriptions
     *
     * @return int
     */
    public function get_lease_seconds()
    {
        return $this->lease_seconds;
    }
    /**
     * Set the callback URL to be used by Hub Servers when communicating with
     * this Subscriber
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_callback_url(string $url): static
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url" of "' . $url . '" must be a non-empty string and a valid URL');
        }
        $this->callback_url = $url;
        return $this;
    }
    /**
     * Get the callback URL to be used by Hub Servers when communicating with
     * this Subscriber
     *
     * @return string
     * @throws Exception\RuntimeException
     */
    public function get_callback_url()
    {
        if (empty($this->callback_url)) {
            throw new Exception\RuntimeException('A valid Callback URL MUST be set before attempting any operation');
        }
        return $this->callback_url;
    }
    /**
     * Set preferred verification mode (sync or async). By default, this
     * Subscriber prefers synchronous verification, but does support
     * asynchronous if that's the Hub Server's utilised mode.
     *
     * Laminas\Feed\Pubsubhubbub\Subscriber will always send both modes, whose
     * order of occurrence in the parameter list determines this preference.
     *
     * @param  string $mode Should be 'sync' or 'async'
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_preferred_verification_mode($mode): static
    {
        if ($mode !== Pub_Sub_Hubbub::VERIFICATION_MODE_SYNC && $mode !== Pub_Sub_Hubbub::VERIFICATION_MODE_ASYNC) {
            throw new Exception\InvalidArgumentException('Invalid preferred mode specified: "' . $mode . '" but should be one of' . ' Laminas\Feed\Pubsubhubbub::VERIFICATION_MODE_SYNC or' . ' Laminas\Feed\Pubsubhubbub::VERIFICATION_MODE_ASYNC');
        }
        $this->preferred_verification_mode = $mode;
        return $this;
    }
    /**
     * Get preferred verification mode (sync or async).
     *
     * @return string
     */
    public function get_preferred_verification_mode()
    {
        return $this->preferred_verification_mode;
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
     * Add authentication credentials for a given URL
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function add_authentication(string $url, array $authentication): static
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url" of "' . $url . '" must be a non-empty string and a valid URL');
        }
        $this->authentications[$url] = $authentication;
        return $this;
    }
    /**
     * Add authentication credentials for hub URLs
     *
     * @return $this
     */
    public function add_authentications(array $authentications): static
    {
        foreach ($authentications as $url => $authentication) {
            $this->add_authentication($url, $authentication);
        }
        return $this;
    }
    /**
     * Get all hub URL authentication credentials
     *
     * @return array
     */
    public function get_authentications()
    {
        return $this->authentications;
    }
    /**
     * Set flag indicating whether or not to use a path parameter
     *
     * @param  bool $bool
     * @return $this
     */
    public function use_path_parameter($bool = true): static
    {
        $this->use_path_parameter = $bool;
        return $this;
    }
    /**
     * Add an optional parameter to the (un)subscribe requests
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
     * Add an optional parameter to the (un)subscribe requests
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
     * Remove an optional parameter for the (un)subscribe requests
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
     * Return an array of optional parameters for (un)subscribe requests
     *
     * @return array
     */
    public function get_parameters()
    {
        return $this->parameters;
    }
    /**
     * Sets an instance of Laminas\Feed\Pubsubhubbub\Model\SubscriptionPersistence used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @return $this
     */
    public function set_storage(Model\Subscription_Persistence_Interface $storage): static
    {
        $this->storage = $storage;
        return $this;
    }
    /**
     * Gets an instance of Laminas\Feed\Pubsubhubbub\Storage\StoragePersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @return Model\SubscriptionPersistenceInterface
     * @throws Exception\RuntimeException
     */
    public function get_storage()
    {
        if ($this->storage === null) {
            throw new Exception\RuntimeException('No storage vehicle has been set.');
        }
        return $this->storage;
    }
    /**
     * Subscribe to one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     */
    public function subscribe_all(): void
    {
        $this->_do_request('subscribe');
    }
    /**
     * Unsubscribe from one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     */
    public function unsubscribe_all(): void
    {
        $this->_do_request('unsubscribe');
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
     * Return an array of Hub Server URLs who returned a response indicating
     * operation in Asynchronous Verification Mode, i.e. they will not confirm
     * any (un)subscription immediately but at a later time (Hubs may be
     * doing this as a batch process when load balancing)
     *
     * @return array
     */
    public function get_async_hubs()
    {
        return $this->async_hubs;
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Executes an (un)subscribe request
     *
     * @param  string $mode
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function _do_request($mode)
    {
        $client = $this->_get_http_client();
        $hubs = $this->get_hub_urls();
        if (empty($hubs)) {
            throw new Exception\RuntimeException('No Hub Server URLs have been set so no subscriptions can be attempted');
        }
        $this->errors = [];
        $this->async_hubs = [];
        foreach ($hubs as $url) {
            if (array_key_exists($url, $this->authentications)) {
                $auth = $this->authentications[$url];
                $client->set_auth($auth[0], $auth[1]);
            }
            $client->set_uri($url);
            $client->set_raw_body($this->_get_request_parameters($url, $mode));
            $response = $client->send();
            if ($response->get_status_code() !== 204 && $response->get_status_code() !== 202) {
                $this->errors[] = ['response' => $response, 'hubUrl' => $url];
                /**
                 * At first I thought it was needed, but the backend storage will
                 * allow tracking async without any user interference. It's left
                 * here in case the user is interested in knowing what Hubs
                 * are using async verification modes so they may update Models and
                 * move these to asynchronous processes.
                 */
            } elseif ($response->get_status_code() === 202) {
                $this->async_hubs[] = ['response' => $response, 'hubUrl' => $url];
            }
        }
    }
    /**
     * Get a basic prepared HTTP client for use
     *
     * @return Client
     */
    protected function _get_http_client()
    {
        $client = Pub_Sub_Hubbub::get_http_client();
        $client->set_method(Http_Request::METHOD_POST);
        $client->set_options(['useragent' => 'Laminas_Feed_Pubsubhubbub_Subscriber/' . Version::VERSION]);
        return $client;
    }
    /**
     * Return a list of standard protocol/optional parameters for addition to
     * client's POST body that are specific to the current Hub Server URL
     *
     * @param  string $hubUrl
     * @param  string $mode
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function _get_request_parameters($hub_url, $mode)
    {
        if (!in_array($mode, ['subscribe', 'unsubscribe'])) {
            throw new Exception\InvalidArgumentException('Invalid mode specified: "' . $mode . '" which should have been "subscribe" or "unsubscribe"');
        }
        $params = ['hub.mode' => $mode, 'hub.topic' => $this->get_topic_url()];
        if ($this->get_preferred_verification_mode() === Pub_Sub_Hubbub::VERIFICATION_MODE_SYNC) {
            $vmodes = [Pub_Sub_Hubbub::VERIFICATION_MODE_SYNC, Pub_Sub_Hubbub::VERIFICATION_MODE_ASYNC];
        } else {
            $vmodes = [Pub_Sub_Hubbub::VERIFICATION_MODE_ASYNC, Pub_Sub_Hubbub::VERIFICATION_MODE_SYNC];
        }
        $params['hub.verify'] = [];
        foreach ($vmodes as $vmode) {
            $params['hub.verify'][] = $vmode;
        }
        /**
         * Establish a persistent verify_token and attach key to callback
         * URL's path/query_string
         */
        $key = $this->_generate_subscription_key($params, $hub_url);
        $token = $this->_generate_verify_token();
        $params['hub.verify_token'] = $token;
        // Note: query string only usable with PuSH 0.2 Hubs
        if (!$this->use_path_parameter) {
            $params['hub.callback'] = $this->get_callback_url() . '?xhub.subscription=' . Pub_Sub_Hubbub::urlencode($key);
        } else {
            $params['hub.callback'] = rtrim($this->get_callback_url(), '/') . '/' . Pub_Sub_Hubbub::urlencode($key);
        }
        if ($mode === 'subscribe' && $this->get_lease_seconds() !== null) {
            $params['hub.lease_seconds'] = $this->get_lease_seconds();
        }
        // hub.secret not currently supported
        $opt_params = $this->get_parameters();
        foreach ($opt_params as $name => $value) {
            $params[$name] = $value;
        }
        // store subscription to storage
        $now = new DateTime();
        $expires = null;
        if (isset($params['hub.lease_seconds'])) {
            $expires = $now->add(new DateInterval('PT' . $params['hub.lease_seconds'] . 'S'))->format('Y-m-d H:i:s');
        }
        // phpcs:disable Generic.Files.LineLength.TooLong
        $data = [
            'id' => $key,
            'topic_url' => $params['hub.topic'],
            'hub_url' => $hub_url,
            'created_time' => $now->format('Y-m-d H:i:s'),
            'lease_seconds' => $params['hub.lease_seconds'],
            /** @psalm-suppress PossiblyInvalidCast */
            'verify_token' => hash('sha256', (string) $params['hub.verify_token']),
            'secret' => null,
            'expiration_time' => $expires,
            'subscription_state' => $mode === 'unsubscribe' ? Pub_Sub_Hubbub::SUBSCRIPTION_TODELETE : Pub_Sub_Hubbub::SUBSCRIPTION_NOTVERIFIED,
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
        $this->get_storage()->set_subscription($data);
        return $this->_to_byte_value_ordered_string($this->_url_encode($params));
    }
    /**
     * Simple helper to generate a verification token used in (un)subscribe
     * requests to a Hub Server. Follows no particular method, which means
     * it might be improved/changed in future.
     *
     * @return string
     */
    protected function _generate_verify_token()
    {
        if (!empty($this->test_static_token)) {
            return $this->test_static_token;
        }
        return bin2hex(random_bytes(16));
    }
    /**
     * Simple helper to generate a verification token used in (un)subscribe
     * requests to a Hub Server.
     *
     * @param  string $hubUrl The Hub Server URL for which this token will apply
     */
    protected function _generate_subscription_key(array $params, string $hub_url): string
    {
        $key_base = $params['hub.topic'] . $hub_url;
        return md5($key_base);
    }
    /**
     * URL Encode an array of parameters
     */
    protected function _url_encode(array $params): array
    {
        $encoded = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $ekey = Pub_Sub_Hubbub::urlencode($key);
                $encoded[$ekey] = [];
                foreach ($value as $duplicate_key) {
                    $encoded[$ekey][] = Pub_Sub_Hubbub::urlencode($duplicate_key);
                }
            } else {
                $encoded[Pub_Sub_Hubbub::urlencode($key)] = Pub_Sub_Hubbub::urlencode($value);
            }
        }
        return $encoded;
    }
    /**
     * Order outgoing parameters
     */
    protected function _to_byte_value_ordered_string(array $params): string
    {
        $return = [];
        uksort($params, strnatcmp(...));
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $keyduplicate) {
                    $return[] = $key . '=' . $keyduplicate;
                }
            } else {
                $return[] = $key . '=' . $value;
            }
        }
        return implode('&', $return);
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * This is STRICTLY for testing purposes only...
     *
     * @internal
     *
     * @var null|string
     */
    protected $test_static_token;
    /**
     * @internal
     */
    final public function set_test_static_token(string $token): void
    {
        $this->test_static_token = $token;
    }
}