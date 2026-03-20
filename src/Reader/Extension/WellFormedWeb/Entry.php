<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Well_Formed_Web;

use function array_key_exists;
use Laminas\Feed\Reader\Extension;
class Entry extends Extension\Abstract_Entry
{
    /**
     * Get the entry comment Uri
     *
     * @return null|string
     */
    public function get_comment_feed_link()
    {
        $name = 'commentRss';
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $data = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/wfw:' . $name . ')');
        if (!$data) {
            $data = null;
        }
        $this->data[$name] = $data;
        return $data;
    }
    /**
     * Register Slash namespaces
     *
     * @return void
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('wfw', 'http://wellformedweb.org/CommentAPI/');
    }
}