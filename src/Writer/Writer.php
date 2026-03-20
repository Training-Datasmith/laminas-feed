<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use const E_USER_NOTICE;
use function in_array;
use function lcfirst;
use function sprintf;
use function trigger_error;
class Writer
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
    /** @var ExtensionManagerInterface */
    protected static $extension_manager;
    /**
     * Array of registered extensions by class postfix (after the base class
     * name) across four categories - data containers and renderers for entry
     * and feed levels.
     *
     * @var array
     */
    protected static $extensions = ['entry' => [], 'feed' => [], 'entryRenderer' => [], 'feedRenderer' => []];
    /**
     * Set plugin loader for use with Extensions
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
        if (!isset(static::$extension_manager)) {
            static::set_extension_manager(new Extension_Manager());
        }
        return static::$extension_manager;
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
        $feed_renderer_name = $name . '\Renderer\Feed';
        if ($manager->has($feed_renderer_name)) {
            static::$extensions['feedRenderer'][] = $feed_renderer_name;
        }
        $entry_renderer_name = $name . '\Renderer\Entry';
        if ($manager->has($entry_renderer_name)) {
            static::$extensions['entryRenderer'][] = $entry_renderer_name;
        }
    }
    /**
     * Is a given named Extension registered?
     */
    public static function is_registered(string $extension_name): bool
    {
        $feed_name = $extension_name . '\Feed';
        $entry_name = $extension_name . '\Entry';
        $feed_renderer_name = $extension_name . '\Renderer\Feed';
        $entry_renderer_name = $extension_name . '\Renderer\Entry';
        if (in_array($feed_name, static::$extensions['feed']) || in_array($entry_name, static::$extensions['entry']) || in_array($feed_renderer_name, static::$extensions['feedRenderer']) || in_array($entry_renderer_name, static::$extensions['entryRenderer'])) {
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
        static::$extension_manager = null;
        static::$extensions = ['entry' => [], 'feed' => [], 'entryRenderer' => [], 'feedRenderer' => []];
    }
    /**
     * Register core (default) extensions
     */
    public static function register_core_extensions(): void
    {
        static::register_extension('DublinCore');
        static::register_extension('Content');
        static::register_extension('Atom');
        static::register_extension('Slash');
        static::register_extension('WellFormedWeb');
        static::register_extension('Threading');
        static::register_extension('ITunes');
        // Added in 2.10.0; check for it conditionally
        static::has_extension('GooglePlayPodcast') ? static::register_extension('GooglePlayPodcast') : trigger_error(sprintf('Please update your %1$s\ExtensionManagerInterface implementation to add entries for' . ' %1$s\Extension\GooglePlayPodcast\Entry,' . ' %1$s\Extension\GooglePlayPodcast\Feed,' . ' %1$s\Extension\GooglePlayPodcast\Renderer\Entry,' . ' and %1$s\Extension\GooglePlayPodcast\Renderer\Feed.', __NAMESPACE__), E_USER_NOTICE);
        // Added in development; check for it conditionally
        static::has_extension('PodcastIndex') ? static::register_extension('PodcastIndex') : trigger_error(sprintf('Please update your %1$s\ExtensionManagerInterface implementation to add entries for' . ' %1$s\Extension\PodcastIndex\Entry,' . ' %1$s\Extension\PodcastIndex\Feed,' . ' %1$s\Extension\PodcastIndex\Renderer\Entry,' . ' and %1$s\Extension\PodcastIndex\Renderer\Feed.', __NAMESPACE__), E_USER_NOTICE);
    }
    /**
     * @deprecated This method is deprecated and will be removed with version 3.0
     *     Use PHP's lcfirst function instead. @see https://php.net/manual/function.lcfirst.php
     *
     * @param  string $str
     */
    public static function lcfirst($str): string
    {
        return lcfirst($str);
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
        $manager = static::get_extension_manager();
        $feed_name = $name . '\Feed';
        $entry_name = $name . '\Entry';
        $feed_renderer_name = $name . '\Renderer\Feed';
        $entry_renderer_name = $name . '\Renderer\Entry';
        if ($manager->has($feed_name)) {
            return true;
        }
        if ($manager->has($entry_name)) {
            return true;
        }
        if ($manager->has($feed_renderer_name)) {
            return true;
        }
        return (bool) $manager->has($entry_renderer_name);
    }
}