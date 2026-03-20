<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function call_user_func_array;
use function method_exists;
use function sprintf;
/**
 * Default implementation of ExtensionManagerInterface
 *
 * Decorator for an ExtensionManagerInstance.
 */
class Extension_Manager implements Extension_Manager_Interface
{
    protected ?\Laminas\Feed\Writer\Extension_Manager_Interface $plugin_manager;
    /**
     * Seeds the extension manager with a plugin manager; if none provided,
     * creates and decorates an instance of StandaloneExtensionManager.
     */
    public function __construct(?Extension_Manager_Interface $plugin_manager = null)
    {
        if (null === $plugin_manager) {
            $plugin_manager = new Standalone_Extension_Manager();
        }
        $this->plugin_manager = $plugin_manager;
    }
    /**
     * Method overloading
     *
     * Proxy to composed ExtensionManagerInterface instance.
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
     * @return Extension\AbstractRenderer
     */
    public function get($extension)
    {
        return $this->plugin_manager->get($extension);
    }
    /**
     * Do we have the named extension?
     *
     * @param  string $extension
     * @return bool
     */
    public function has($extension)
    {
        return $this->plugin_manager->has($extension);
    }
}