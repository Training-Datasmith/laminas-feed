<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub\Subscriber;

use function array_key_exists;
use function explode;
use function hash;
use function hash_equals;
use function is_array;
use Laminas\Feed\Pub_Sub_Hubbub;
use Laminas\Feed\Pub_Sub_Hubbub\Exception;
use Laminas\Feed\Uri;
use function rawurldecode;
use function sprintf;
use function stripos;
use function strtolower;
class Callback extends Pub_Sub_Hubbub\Abstract_Callback
{
    /**
     * Contains the content of any feeds sent as updates to the Callback URL
     *
     * @var string
     */
    protected $feed_update;
    /**
     * Holds a manually set subscription key (i.e. identifies a unique
     * subscription) which is typical when it is not passed in the query string
     * but is part of the Callback URL path, requiring manual retrieval e.g.
     * using a route and the \Laminas\Mvc\Router\RouteMatch::getParam() method.
     *
     * @var null|string
     */
    protected $subscription_key;
    /**
     * After verification, this is set to the verified subscription's data.
     *
     * @var array
     */
    protected $current_subscription_data;
    /**
     * Set a subscription key to use for the current callback request manually.
     * Required if usePathParameter is enabled for the Subscriber.
     *
     * @param  string $key
     * @return $this
     */
    public function set_subscription_key($key): static
    {
        $this->subscription_key = $key;
        return $this;
    }
    /**
     * Handle any callback from a Hub Server responding to a subscription or
     * unsubscription request. This should be the Hub Server confirming the
     * the request prior to taking action on it.
     *
     * @param  null|array $httpGetData     GET data if available and not in $_GET
     * @param  bool       $sendResponseNow Whether to send response now or when asked
     */
    public function handle(?array $http_get_data = null, $send_response_now = false): void
    {
        if ($http_get_data === null) {
            $http_get_data = $_GET;
        }
        /**
         * Handle any feed updates (sorry for the mess :P)
         *
         * This DOES NOT attempt to process a feed update. Feed updates
         * SHOULD be validated/processed by an asynchronous process so as
         * to avoid holding up responses to the Hub.
         */
        $content_type = $this->_get_header('Content-Type');
        if (strtolower((string) $_SERVER['REQUEST_METHOD']) === 'post' && $this->_has_valid_verify_token(null, false) && (stripos($content_type, 'application/atom+xml') === 0 || stripos($content_type, 'application/rss+xml') === 0 || stripos($content_type, 'application/xml') === 0 || stripos($content_type, 'text/xml') === 0 || stripos($content_type, 'application/rdf+xml') === 0)) {
            $this->set_feed_update($this->_get_raw_body());
            $this->get_http_response()->set_header('X-Hub-On-Behalf-Of', $this->get_subscriber_count());
            /**
             * Handle any (un)subscribe confirmation requests
             */
        } elseif ($this->is_valid_hub_verification($http_get_data)) {
            $this->get_http_response()->set_content($http_get_data['hub_challenge']);
            switch (strtolower((string) $http_get_data['hub_mode'])) {
                case 'subscribe':
                    $data = $this->current_subscription_data;
                    $data['subscription_state'] = Pub_Sub_Hubbub\Pub_Sub_Hubbub::SUBSCRIPTION_VERIFIED;
                    if (isset($http_get_data['hub_lease_seconds'])) {
                        $data['lease_seconds'] = $http_get_data['hub_lease_seconds'];
                    }
                    $this->get_storage()->set_subscription($data);
                    break;
                case 'unsubscribe':
                    $verify_token_key = $this->_detect_verify_token_key($http_get_data);
                    $this->get_storage()->delete_subscription($verify_token_key);
                    break;
                default:
                    throw new Exception\RuntimeException(sprintf('Invalid hub_mode ("%s") provided', $http_get_data['hub_mode']));
            }
            /**
             * Hey, C'mon! We tried everything else!
             */
        } else {
            $this->get_http_response()->set_status_code(404);
        }
        if ($send_response_now) {
            $this->send_response();
        }
    }
    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     */
    public function is_valid_hub_verification(array $http_get_data): bool
    {
        /**
         * As per the specification, the hub.verify_token is OPTIONAL. This
         * implementation of Pubsubhubbub considers it REQUIRED and will
         * always send a hub.verify_token parameter to be echoed back
         * by the Hub Server. Therefore, its absence is considered invalid.
         */
        if (strtolower((string) $_SERVER['REQUEST_METHOD']) !== 'get') {
            return false;
        }
        $required = ['hub_mode', 'hub_topic', 'hub_challenge', 'hub_verify_token'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $http_get_data)) {
                return false;
            }
        }
        if ($http_get_data['hub_mode'] !== 'subscribe' && $http_get_data['hub_mode'] !== 'unsubscribe') {
            return false;
        }
        if ($http_get_data['hub_mode'] === 'subscribe' && !array_key_exists('hub_lease_seconds', $http_get_data)) {
            return false;
        }
        if (!Uri::factory($http_get_data['hub_topic'])->is_valid()) {
            return false;
        }
        /**
         * Attempt to retrieve any Verification Token Key attached to Callback
         * URL's path by our Subscriber implementation
         */
        if (!$this->_has_valid_verify_token($http_get_data)) {
            return false;
        }
        return true;
    }
    /**
     * Sets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @param  string $feed
     * @return $this
     */
    public function set_feed_update($feed): static
    {
        $this->feed_update = $feed;
        return $this;
    }
    /**
     * Check if any newly received feed (Atom/RSS) update was received
     */
    public function has_feed_update(): bool
    {
        if ($this->feed_update === null) {
            return false;
        }
        return true;
    }
    /**
     * Gets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @return string
     */
    public function get_feed_update()
    {
        return $this->feed_update;
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Check for a valid verify_token. By default attempts to compare values
     * with that sent from Hub, otherwise merely ascertains its existence.
     *
     * @param  array $httpGetData
     * @param  bool $checkValue
     */
    protected function _has_valid_verify_token(?array $http_get_data = null, $check_value = true): bool
    {
        $verify_token_key = $this->_detect_verify_token_key($http_get_data);
        if (empty($verify_token_key)) {
            return false;
        }
        $verify_token_exists = $this->get_storage()->has_subscription($verify_token_key);
        if (!$verify_token_exists) {
            return false;
        }
        if ($check_value) {
            $data = $this->get_storage()->get_subscription($verify_token_key);
            $verify_token = $data['verify_token'];
            if (!hash_equals($verify_token, hash('sha256', (string) $http_get_data['hub_verify_token']))) {
                return false;
            }
            $this->current_subscription_data = $data;
            return true;
        }
        return true;
    }
    /**
     * Attempt to detect the verification token key. This would be passed in
     * the Callback URL (which we are handling with this class!) as a URI
     * path part (the last part by convention).
     *
     * @return false|string
     */
    protected function _detect_verify_token_key(?array $http_get_data = null)
    {
        /**
         * Available when sub keys encoding in Callback URL path
         */
        if (isset($this->subscription_key)) {
            return $this->subscription_key;
        }
        /**
         * Available only if allowed by PuSH 0.2 Hubs
         */
        if (is_array($http_get_data) && isset($http_get_data['xhub_subscription'])) {
            return $http_get_data['xhub_subscription'];
        }
        /**
         * Available (possibly) if corrupted in transit and not part of $_GET
         */
        $params = $this->_parse_query_string();
        if (isset($params['xhub.subscription'])) {
            return rawurldecode($params['xhub.subscription']);
        }
        return false;
    }
    /**
     * Build an array of Query String parameters.
     * This bypasses $_GET which munges parameter names and cannot accept
     * multiple parameters with the same key.
     *
     * @return array|void
     */
    protected function _parse_query_string(): array
    {
        $params = [];
        $query_string = '';
        if (isset($_SERVER['QUERY_STRING'])) {
            $query_string = $_SERVER['QUERY_STRING'];
        }
        if (empty($query_string)) {
            return [];
        }
        $parts = explode('&', (string) $query_string);
        foreach ($parts as $kvpair) {
            $pair = explode('=', $kvpair);
            $key = rawurldecode($pair[0]);
            $value = rawurldecode($pair[1]);
            if (isset($params[$key])) {
                if (is_array($params[$key])) {
                    $params[$key][] = $value;
                } else {
                    $params[$key] = [$params[$key], $value];
                }
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}