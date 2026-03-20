<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader;

use function call_user_func_array;
use function method_exists;
use function sprintf;
/**
 * Default implementation of ExtensionManagerInterface
 *
 * Decorator of ExtensionPluginManager.
 *
 * @final this class wasn't designed to be inherited from, but we can't assume that consumers haven't already
 *        extended it, therefore we cannot add the final marker without a new major release.
 */
class Extension_Manager implements Extension_Manager_Interface
{
    protected ?\Laminas\Feed\Reader\Extension_Plugin_Manager $plugin_manager;
    /**
     * Seeds the extension manager with a plugin manager; if none provided,
     * creates an instance.
     */
    public function __construct(?Extension_Plugin_Manager $plugin_manager = null)
    {
        if (null === $plugin_manager) {
            $plugin_manager = new Extension_Plugin_Manager();
        }
        $this->plugin_manager = $plugin_manager;
    }
    /**
     * Method overloading
     *
     * Proxy to composed ExtensionPluginManager instance.
     *
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException
     */
    public function __call(string $method, array $args)
    {
        if (!method_exists($this->plugin_manager, $method)) {
            throw new Exception\BadMethodCallException(sprintf('Method by name of %s does not exist in %s', $method, self::class));
        }
        return call_user_func_array([$this->plugin_manager, $method], $args);
    }
    /**
     * Get the named extension
     *
     * @param  string $extension
     * @return Extension\AbstractEntry|Extension\AbstractFeed
     */
    public function get($extension)
    {
        return $this->plugin_manager->get($extension);
    }
    /**
     * Do we have the named extension?
     *
     * @param  string $extension
     */
    public function has($extension): bool
    {
        return $this->plugin_manager->has($extension);
    }
}