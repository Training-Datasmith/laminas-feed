<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Thread;

use function array_key_exists;
use Laminas\Feed\Reader\Extension;
class Entry extends Extension\Abstract_Entry
{
    /**
     * Get the "in-reply-to" value
     */
    public function get_in_reply_to(): void
    {
        // TODO: to be implemented
    }
    // TODO: Implement "replies" and "updated" constructs from standard
    /**
     * Get the total number of threaded responses (i.e comments)
     *
     * @return null|int
     */
    public function get_comment_count()
    {
        return $this->get_data('total');
    }
    /**
     * Get the entry data specified by name
     *
     * @return null|mixed
     */
    protected function get_data(string $name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $data = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/thread10:' . $name . ')');
        if (!$data) {
            $data = null;
        }
        $this->data[$name] = $data;
        return $data;
    }
    /**
     * Register Atom Thread Extension 1.0 namespace
     *
     * @return void
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('thread10', 'http://purl.org/syndication/thread/1.0');
    }
}