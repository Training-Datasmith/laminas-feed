<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader;

use function array_unique;
use Dom_Document;
use Domx_Path;
use const E_NOTICE;
use const E_USER_NOTICE;
use const E_WARNING;
use function file_get_contents;
use function function_exists;
use function in_array;
use function ini_restore;
use function ini_set;
use function is_string;
use Laminas\Cache\Storage\Storage_Interface as CacheStorage;
use Laminas\Feed\Reader\Exception\Invalid_Http_Client_Exception;
use Laminas\Http as LaminasHttp;
use Laminas\Stdlib\Error_Handler;
use function libxml_disable_entity_loader;
use function libxml_get_last_error;
use function libxml_use_internal_errors;
use const LIBXML_VERSION;
use function md5;
use function serialize;
use function sprintf;
use function strlen;
use function strpos;
use function trigger_error;
use function trim;
use function unserialize;
use const XML_DOCUMENT_TYPE_NODE;
class Reader implements Reader_Import_Interface
{
    /**
     * Namespace constants
     */
    public const NAMESPACE_ATOM_03 = 'http://purl.org/atom/ns#';
    public const NAMESPACE_ATOM_10 = 'http://www.w3.org/2005/Atom';
    public const NAMESPACE_RDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    public const NAMESPACE_RSS_090 = 'http://my.netscape.com/rdf/simple/0.9/';
    public const NAMESPACE_RSS_10 = 'http://purl.org/rss/1.0/';
    /**
     * Feed type constants
     */
    public const TYPE_ANY = 'any';
    public const TYPE_ATOM_03 = 'atom-03';
    public const TYPE_ATOM_10 = 'atom-10';
    public const TYPE_ATOM_10_ENTRY = 'atom-10-entry';
    public const TYPE_ATOM_ANY = 'atom';
    public const TYPE_RSS_090 = 'rss-090';
    public const TYPE_RSS_091 = 'rss-091';
    public const TYPE_RSS_091_NETSCAPE = 'rss-091n';
    public const TYPE_RSS_091_USERLAND = 'rss-091u';
    public const TYPE_RSS_092 = 'rss-092';
    public const TYPE_RSS_093 = 'rss-093';
    public const TYPE_RSS_094 = 'rss-094';
    public const TYPE_RSS_10 = 'rss-10';
    public const TYPE_RSS_20 = 'rss-20';
    public const TYPE_RSS_ANY = 'rss';
    /**
     * Cache instance
     *
     * @var CacheStorage
     */
    protected static $cache;
    /**
     * HTTP client object to use for retrieving feeds
     *
     * @var Http\ClientInterface
     */
    protected static $http_client;
    /**
     * Override HTTP PUT and DELETE request methods?
     *
     * @var bool
     */
    protected static $http_method_override = false;
    /** @var bool */
    protected static $http_conditional_get = false;
    /** @var null|ExtensionManagerInterface */
    protected static $extension_manager;
    /** @var array<string, string[]> */
    protected static $extensions = ['feed' => ['DublinCore\Feed', 'Atom\Feed'], 'entry' => ['Content\Entry', 'DublinCore\Entry', 'Atom\Entry'], 'core' => ['DublinCore\Feed', 'Atom\Feed', 'Content\Entry', 'DublinCore\Entry', 'Atom\Entry']];
    /**
     * Disable the ability to load external XML entities based on libxml version
     *
     * If we are using libxml < 2.9, unsafe XML entity loading must be
     * disabled with a flag.
     *
     * If we are using libxml >= 2.9, XML entity loading is disabled by default.
     *
     * @param bool $flag
     * @return bool
     */
    public static function disable_entity_loader($flag = true)
    {
        if (LIBXML_VERSION < 20900) {
            return libxml_disable_entity_loader($flag);
            // phpcs:ignore
        }
        return $flag;
    }
    /**
     * Get the Feed cache
     *
     * @return CacheStorage
     */
    public static function get_cache()
    {
        return static::$cache;
    }
    /**
     * Set the feed cache
     */
    public static function set_cache(Cache_Storage $cache): void
    {
        static::$cache = $cache;
    }
    /**
     * Set the HTTP client instance
     *
     * Sets the HTTP client object to use for retrieving the feeds.
     *
     * @param Http\ClientInterface|LaminasHttp\Client $httpClient
     */
    public static function set_http_client($http_client): void
    {
        if ($http_client instanceof Laminas_Http\Client) {
            $http_client = new Http\Laminas_Http_Client_Decorator($http_client);
        }
        if (!$http_client instanceof Http\Client_Interface) {
            throw new Invalid_Http_Client_Exception();
        }
        static::$http_client = $http_client;
    }
    /**
     * Gets the HTTP client object. If none is set, a new LaminasHttp\Client will be used.
     *
     * @return Http\ClientInterface
     */
    public static function get_http_client()
    {
        if (!static::$http_client) {
            static::$http_client = new Http\Laminas_Http_Client_Decorator(new Laminas_Http\Client());
        }
        return static::$http_client;
    }
    /**
     * Toggle using POST instead of PUT and DELETE HTTP methods
     *
     * Some feed implementations do not accept PUT and DELETE HTTP
     * methods, or they can't be used because of proxies or other
     * measures. This allows turning on using POST where PUT and
     * DELETE would normally be used; in addition, an
     * X-Method-Override header will be sent with a value of PUT or
     * DELETE as appropriate.
     *
     * @param  bool $override Whether to override PUT and DELETE.
     */
    public static function set_http_method_override($override = true): void
    {
        static::$http_method_override = $override;
    }
    /**
     * Get the HTTP override state
     *
     * @return bool
     */
    public static function get_http_method_override()
    {
        return static::$http_method_override;
    }
    /**
     * Set the flag indicating whether or not to use HTTP conditional GET
     *
     * @param  bool $bool
     */
    public static function use_http_conditional_get($bool = true): void
    {
        static::$http_conditional_get = $bool;
    }
    /**
     * Import a feed by providing a URI
     *
     * @param  string $uri The URI to the feed
     * @param  null|string $etag OPTIONAL Last received ETag for this resource
     * @param  null|string $lastModified OPTIONAL Last-Modified value for this resource
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException
     */
    public static function import($uri, $etag = null, $last_modified = null)
    {
        $cache = self::get_cache();
        $client = self::get_http_client();
        $cache_id = 'Laminas_Feed_Reader_' . md5($uri);
        if (static::$http_conditional_get && $cache) {
            $headers = [];
            $data = $cache->get_item($cache_id);
            if ($data && $client instanceof Http\Header_Aware_Client_Interface) {
                // Only check for ETag and last modified values in the cache
                // if we have a client capable of emitting headers in the first place.
                if ($etag === null) {
                    $etag = $cache->get_item($cache_id . '_etag');
                }
                if ($last_modified === null) {
                    $last_modified = $cache->get_item($cache_id . '_lastmodified');
                }
                if ($etag) {
                    $headers['If-None-Match'] = [$etag];
                }
                if ($last_modified) {
                    $headers['If-Modified-Since'] = [$last_modified];
                }
            }
            $response = $client->get($uri, $headers);
            if ($response->get_status_code() !== 200 && $response->get_status_code() !== 304) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->get_status_code());
            }
            if ((int) $response->get_status_code() === 304) {
                $response_xml = $data;
            } else {
                $response_xml = $response->get_body();
                $cache->set_item($cache_id, $response_xml);
                if ($response instanceof Http\Header_Aware_Response_Interface) {
                    if ($response->get_header_line('ETag', false)) {
                        $cache->set_item($cache_id . '_etag', $response->get_header_line('ETag'));
                    }
                    if ($response->get_header_line('Last-Modified', false)) {
                        $cache->set_item($cache_id . '_lastmodified', $response->get_header_line('Last-Modified'));
                    }
                }
            }
            return static::import_string($response_xml);
        }
        if ($cache) {
            $data = $cache->get_item($cache_id);
            if ($data) {
                return static::import_string($data);
            }
            $response = $client->get($uri);
            if ((int) $response->get_status_code() !== 200) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->get_status_code());
            }
            $response_xml = $response->get_body();
            $cache->set_item($cache_id, $response_xml);
            return static::import_string($response_xml);
        }
        $response = $client->get($uri);
        if ((int) $response->get_status_code() !== 200) {
            throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->get_status_code());
        }
        $reader = static::import_string($response->get_body());
        $reader->set_original_source_uri($uri);
        return $reader;
    }
    /**
     * Import a feed from a remote URI
     *
     * Performs similarly to import(), except it uses the HTTP client passed to
     * the method, and does not take into account cached data.
     *
     * Primary purpose is to make it possible to use the Reader with alternate
     * HTTP client implementations.
     *
     * @param  string $uri
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException If response is not an Http\ResponseInterface.
     */
    public static function import_remote_feed($uri, Http\Client_Interface $client)
    {
        $response = $client->get($uri);
        if (!$response instanceof Http\Response_Interface) {
            throw new Exception\RuntimeException(sprintf('Did not receive a %s\Http\ResponseInterface from the provided HTTP client; received "%s"', __NAMESPACE__, get_debug_type($response)));
        }
        if ($response->get_status_code() !== 200) {
            throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->get_status_code());
        }
        $reader = static::import_string($response->get_body());
        $reader->set_original_source_uri($uri);
        return $reader;
    }
    /**
     * Import a feed from a string
     *
     * @param string $string
     * @return Feed\FeedInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public static function import_string($string): \Laminas\Feed\Reader\Feed\Atom|\Laminas\Feed\Reader\Entry\Atom|\Laminas\Feed\Reader\Feed\Rss
    {
        $trimmed = trim($string);
        if (!is_string($string) || empty($trimmed)) {
            throw new Exception\InvalidArgumentException('Only non empty strings are allowed as input');
        }
        $libxml_errflag = libxml_use_internal_errors(true);
        $disable_entity_loader_flag = self::disable_entity_loader();
        $dom = new Dom_Document();
        $status = $dom->load_xml(trim($string));
        if ($dom->encoding === null) {
            $dom->encoding = 'UTF-8';
        }
        foreach ($dom->child_nodes as $child) {
            if ($child->node_type === XML_DOCUMENT_TYPE_NODE) {
                throw new Exception\InvalidArgumentException('Invalid XML: Detected use of illegal DOCTYPE');
            }
        }
        self::disable_entity_loader($disable_entity_loader_flag);
        libxml_use_internal_errors($libxml_errflag);
        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $error->message = trim($error->message);
                $errormsg = "DOMDocument cannot parse XML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse XML: Please check the XML document's validity";
            }
            throw new Exception\RuntimeException($errormsg);
        }
        $type = static::detect_type($dom);
        static::register_core_extensions();
        if (str_starts_with($type, 'rss')) {
            $reader = new Feed\Rss($dom, $type);
        } elseif (8 === strpos($type, 'entry')) {
            $reader = new Entry\Atom($dom->document_element, 0, self::TYPE_ATOM_10);
        } elseif (str_starts_with($type, 'atom')) {
            $reader = new Feed\Atom($dom, $type);
        } else {
            throw new Exception\RuntimeException('The URI used does not point to a ' . 'valid Atom, RSS or RDF feed that Laminas\Feed\Reader can parse.');
        }
        return $reader;
    }
    /**
     * Imports a feed from a file located at $filename.
     *
     * @param  string $filename
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException
     */
    public static function import_file($filename)
    {
        Error_Handler::start();
        $feed = file_get_contents($filename);
        $err = Error_Handler::stop();
        if ($feed === false) {
            throw new Exception\RuntimeException("File '{$filename}' could not be loaded", 0, $err);
        }
        return static::import_string($feed);
    }
    /**
     * Find feed links
     *
     * @param  string $uri
     * @throws Exception\RuntimeException
     */
    public static function find_feed_links($uri): \Laminas\Feed\Reader\Feed_Set
    {
        $client = static::get_http_client();
        $response = $client->get($uri);
        if ($response->get_status_code() !== 200) {
            throw new Exception\RuntimeException("Failed to access {$uri}, got response code " . $response->get_status_code());
        }
        $response_html = $response->get_body();
        $libxml_errflag = libxml_use_internal_errors(true);
        $disable_entity_loader_flag = self::disable_entity_loader();
        $dom = new Dom_Document();
        $status = $dom->load_html(trim($response_html));
        self::disable_entity_loader($disable_entity_loader_flag);
        libxml_use_internal_errors($libxml_errflag);
        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $error->message = trim($error->message);
                $errormsg = "DOMDocument cannot parse HTML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse HTML: Please check the XML document's validity";
            }
            throw new Exception\RuntimeException($errormsg);
        }
        $feed_set = new Feed_Set();
        $links = $dom->get_elements_by_tag_name('link');
        $feed_set->add_links($links, $uri);
        return $feed_set;
    }
    /**
     * Detect the feed type of the provided feed
     *
     * @param  string|DOMDocument|Feed\AbstractFeed $feed
     * @param  bool $specOnly
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public static function detect_type($feed, $spec_only = false): string
    {
        if ($feed instanceof Feed\Abstract_Feed) {
            $dom = $feed->get_dom_document();
        } elseif ($feed instanceof Dom_Document) {
            $dom = $feed;
        } elseif (is_string($feed) && !empty($feed)) {
            Error_Handler::start(E_NOTICE | E_WARNING);
            ini_set('track_errors', '1');
            $disable_entity_loader_flag = self::disable_entity_loader();
            $dom = new Dom_Document();
            $status = $dom->load_xml($feed);
            foreach ($dom->child_nodes as $child) {
                if ($child->node_type === XML_DOCUMENT_TYPE_NODE) {
                    throw new Exception\InvalidArgumentException('Invalid XML: Detected use of illegal DOCTYPE');
                }
            }
            self::disable_entity_loader($disable_entity_loader_flag);
            ini_restore('track_errors');
            Error_Handler::stop();
            if (!$status) {
                if (!isset($php_errormsg)) {
                    if (function_exists('xdebug_is_enabled')) {
                        $php_errormsg = '(error message not available, when XDebug is running)';
                    } else {
                        $php_errormsg = '(error message not available)';
                    }
                }
                throw new Exception\RuntimeException("DOMDocument cannot parse XML: {$php_errormsg}");
            }
        } else {
            throw new Exception\InvalidArgumentException('Invalid object/scalar provided: must' . ' be of type Laminas\Feed\Reader\Feed, DomDocument or string');
        }
        $xpath = new Domx_Path($dom);
        if ($xpath->query('/rss')->length) {
            $type = self::TYPE_RSS_ANY;
            $version = $xpath->evaluate('string(/rss/@version)');
            if (strlen((string) $version) > 0) {
                switch ($version) {
                    case '2.0':
                        $type = self::TYPE_RSS_20;
                        break;
                    case '0.94':
                        $type = self::TYPE_RSS_094;
                        break;
                    case '0.93':
                        $type = self::TYPE_RSS_093;
                        break;
                    case '0.92':
                        $type = self::TYPE_RSS_092;
                        break;
                    case '0.91':
                        $type = self::TYPE_RSS_091;
                        break;
                }
            }
            return $type;
        }
        $xpath->register_namespace('rdf', self::NAMESPACE_RDF);
        if ($xpath->query('/rdf:RDF')->length) {
            $xpath->register_namespace('rss', self::NAMESPACE_RSS_10);
            if ($xpath->query('/rdf:RDF/rss:channel')->length || $xpath->query('/rdf:RDF/rss:image')->length || $xpath->query('/rdf:RDF/rss:item')->length || $xpath->query('/rdf:RDF/rss:textinput')->length) {
                return self::TYPE_RSS_10;
            }
            $xpath->register_namespace('rss', self::NAMESPACE_RSS_090);
            if ($xpath->query('/rdf:RDF/rss:channel')->length || $xpath->query('/rdf:RDF/rss:image')->length || $xpath->query('/rdf:RDF/rss:item')->length || $xpath->query('/rdf:RDF/rss:textinput')->length) {
                return self::TYPE_RSS_090;
            }
        }
        $xpath->register_namespace('atom', self::NAMESPACE_ATOM_10);
        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_10;
        }
        if ($xpath->query('//atom:entry')->length) {
            return $spec_only === true ? self::TYPE_ATOM_10 : self::TYPE_ATOM_10_ENTRY;
        }
        $xpath->register_namespace('atom', self::NAMESPACE_ATOM_03);
        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_03;
        }
        return self::TYPE_ANY;
    }
    /**
     * Set plugin manager for use with Extensions
     */
    public static function set_extension_manager(Extension_Manager_Interface $extension_manager): void
    {
        static::$extension_manager = $extension_manager;
    }
    /**
     * Get plugin manager for use with Extensions
     *
     * @return ExtensionManagerInterface
     */
    public static function get_extension_manager()
    {
        $manager = static::$extension_manager;
        if (!$manager instanceof Extension_Manager_Interface) {
            $manager = new Standalone_Extension_Manager();
            static::set_extension_manager($manager);
        }
        return $manager;
    }
    /**
     * Register an Extension by name
     *
     * @throws Exception\RuntimeException If unable to resolve Extension class.
     */
    public static function register_extension(string $name): void
    {
        if (!static::has_extension($name)) {
            throw new Exception\RuntimeException(sprintf('Could not load extension "%s" using Plugin Loader.' . ' Check prefix paths are configured and extension exists.', $name));
        }
        // Return early if already registered.
        if (static::is_registered($name)) {
            return;
        }
        $manager = static::get_extension_manager();
        $feed_name = $name . '\Feed';
        if ($manager->has($feed_name)) {
            static::$extensions['feed'][] = $feed_name;
        }
        $entry_name = $name . '\Entry';
        if ($manager->has($entry_name)) {
            static::$extensions['entry'][] = $entry_name;
        }
    }
    /**
     * Is a given named Extension registered?
     */
    public static function is_registered(string $extension_name): bool
    {
        $feed_name = $extension_name . '\Feed';
        $entry_name = $extension_name . '\Entry';
        if (in_array($feed_name, static::$extensions['feed']) || in_array($entry_name, static::$extensions['entry'])) {
            return true;
        }
        return false;
    }
    /**
     * Get a list of extensions
     *
     * @return array
     */
    public static function get_extensions()
    {
        return static::$extensions;
    }
    /**
     * Reset class state to defaults
     */
    public static function reset(): void
    {
        static::$cache = null;
        static::$http_client = null;
        static::$http_method_override = false;
        static::$http_conditional_get = false;
        static::$extension_manager = null;
        static::$extensions = ['feed' => ['DublinCore\Feed', 'Atom\Feed'], 'entry' => ['Content\Entry', 'DublinCore\Entry', 'Atom\Entry'], 'core' => ['DublinCore\Feed', 'Atom\Feed', 'Content\Entry', 'DublinCore\Entry', 'Atom\Entry']];
    }
    /**
     * Register core (default) extensions
     *
     * @return void
     */
    protected static function register_core_extensions()
    {
        static::register_extension('DublinCore');
        static::register_extension('Content');
        static::register_extension('Atom');
        static::register_extension('Slash');
        static::register_extension('WellFormedWeb');
        static::register_extension('Thread');
        static::register_extension('Podcast');
        static::register_extension('Podcast');
        // Added in 2.10.0; check for it conditionally
        static::has_extension('GooglePlayPodcast') ? static::register_extension('GooglePlayPodcast') : trigger_error(sprintf('Please update your %1$s\ExtensionManagerInterface implementation to add entries for' . ' %1$s\Extension\GooglePlayPodcast\Entry and %1$s\Extension\GooglePlayPodcast\Feed.', __NAMESPACE__), E_USER_NOTICE);
        // Added in development; check for it conditionally
        static::has_extension('PodcastIndex') ? static::register_extension('PodcastIndex') : trigger_error(sprintf('Please update your %1$s\ExtensionManagerInterface implementation to add entries for' . ' %1$s\Extension\PodcastIndex\Entry and %1$s\Extension\PodcastIndex\Feed.', __NAMESPACE__), E_USER_NOTICE);
    }
    /**
     * Utility method to apply array_unique operation to a multidimensional
     * array.
     *
     * @template TInput of array
     * @param TInput $array
     * @return TInput
     */
    public static function array_unique(array $array): array
    {
        foreach ($array as &$value) {
            $value = serialize($value);
        }
        $array = array_unique($array);
        foreach ($array as &$value) {
            $value = unserialize($value);
        }
        return $array;
    }
    /**
     * Does the extension manager have the named extension?
     *
     * This method exists to allow us to test if an extension is present in the
     * extension manager. It may be used by registerExtension() to determine if
     * the extension has items present in the manager, or by
     * registerCoreExtension() to determine if the core extension has entries
     * in the extension manager. In the latter case, this can be useful when
     * adding new extensions in a minor release, as custom extension manager
     * implementations may not yet have an entry for the extension, which would
     * then otherwise cause registerExtension() to fail.
     */
    protected static function has_extension(string $name): bool
    {
        $feed_name = $name . '\Feed';
        $entry_name = $name . '\Entry';
        $manager = static::get_extension_manager();
        if ($manager->has($feed_name)) {
            return true;
        }
        return (bool) $manager->has($entry_name);
    }
}