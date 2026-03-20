<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub;

use function is_string;
use Laminas\Escaper\Escaper;
use Laminas\Feed\Reader;
use Laminas\Http;
use function str_replace;
class Pub_Sub_Hubbub
{
    /**
     * Verification Modes
     */
    public const VERIFICATION_MODE_SYNC = 'sync';
    public const VERIFICATION_MODE_ASYNC = 'async';
    /**
     * Subscription States
     */
    public const SUBSCRIPTION_VERIFIED = 'verified';
    public const SUBSCRIPTION_NOTVERIFIED = 'not_verified';
    public const SUBSCRIPTION_TODELETE = 'to_delete';
    /** @var Escaper */
    protected static $escaper;
    /**
     * Singleton instance if required of the HTTP client
     *
     * @var null|Http\Client
     */
    protected static $http_client;
    /**
     * Simple utility function which imports any feed URL and
     * determines the existence of Hub Server endpoints. This works
     * best if directly given an instance of Laminas\Feed\Reader\Atom|Laminas\Feed\Reader\Rss
     * to leverage off.
     *
     * @param  string|Reader\Feed\AbstractFeed $source
     * @return array<array-key, mixed>|null
     * @throws Exception\InvalidArgumentException
     */
    public static function detect_hubs($source)
    {
        if (is_string($source)) {
            $feed = Reader\Reader::import($source);
        } elseif ($source instanceof Reader\Feed\Abstract_Feed) {
            $feed = $source;
        } else {
            throw new Exception\InvalidArgumentException('The source parameter was' . ' invalid, i.e. not a URL string or an instance of type' . ' Laminas\Feed\Reader\Feed\AbstractFeed');
        }
        return $feed->get_hubs();
    }
    /**
     * Allows the external environment to make laminas-oauth use a specific
     * Client instance.
     */
    public static function set_http_client(Http\Client $http_client): void
    {
        static::$http_client = $http_client;
    }
    /**
     * Return the singleton instance of the HTTP Client. Note that
     * the instance is reset and cleared of previous parameters GET/POST.
     * Headers are NOT reset but handled by this component if applicable.
     *
     * @return Http\Client
     */
    public static function get_http_client()
    {
        if (!isset(static::$http_client)) {
            static::$http_client = new Http\Client();
        } else {
            static::$http_client->reset_parameters();
        }
        return static::$http_client;
    }
    /**
     * Simple mechanism to delete the entire singleton HTTP Client instance
     * which forces a new instantiation for subsequent requests.
     */
    public static function clear_http_client(): void
    {
        static::$http_client = null;
    }
    /**
     * Set the Escaper instance
     *
     * If null, resets the instance
     */
    public static function set_escaper(?Escaper $escaper = null): void
    {
        static::$escaper = $escaper;
    }
    /**
     * Get the Escaper instance
     *
     * If none registered, lazy-loads an instance.
     *
     * @return Escaper
     */
    public static function get_escaper()
    {
        if (null === static::$escaper) {
            static::set_escaper(new Escaper());
        }
        return static::$escaper;
    }
    /**
     * RFC 3986 safe url encoding method
     *
     * @param  string $string
     * @return string
     */
    public static function urlencode($string): string|array
    {
        $escaper = static::get_escaper();
        $rawencoded = $escaper->escape_url($string);
        return str_replace('%7E', '~', $rawencoded);
    }
}