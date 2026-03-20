<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Creative_Commons;

use function array_key_exists;
use function array_unique;
use function is_string;
use Laminas\Feed\Reader\Exception\RuntimeException;
use Laminas\Feed\Reader\Extension;
use function sprintf;
class Feed extends Extension\Abstract_Feed
{
    /**
     * Get the entry license
     *
     * @param  int $index
     */
    public function get_license($index = 0): ?string
    {
        $licenses = $this->get_licenses();
        if (!isset($licenses[$index])) {
            return null;
        }
        if (!is_string($licenses[$index])) {
            throw new RuntimeException(sprintf('Unable to retrieve license; expected string, received "%s"', get_debug_type($licenses[$index])));
        }
        return $licenses[$index];
    }
    /**
     * Get the entry licenses
     *
     * @return array
     */
    public function get_licenses()
    {
        $name = 'licenses';
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $licenses = [];
        $list = $this->xpath->evaluate('channel/cc:license');
        if ($list->length) {
            foreach ($list as $license) {
                $licenses[] = $license->node_value;
            }
            $licenses = array_unique($licenses);
        }
        $this->data[$name] = $licenses;
        return $this->data[$name];
    }
    /**
     * Register Creative Commons namespaces
     *
     * @return void
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('cc', 'http://backend.userland.com/creativeCommonsRssModule');
    }
}