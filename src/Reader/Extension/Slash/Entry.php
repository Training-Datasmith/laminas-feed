<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Slash;

use function array_key_exists;
use function explode;
use Laminas\Feed\Reader\Extension;
class Entry extends Extension\Abstract_Entry
{
    /**
     * Get the entry section
     *
     * @return null|string
     */
    public function get_section()
    {
        return $this->get_data('section');
    }
    /**
     * Get the entry department
     *
     * @return null|string
     */
    public function get_department()
    {
        return $this->get_data('department');
    }
    /**
     * Get the entry hit_parade
     *
     * @return array
     */
    public function get_hit_parade()
    {
        $name = 'hit_parade';
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        $string_parade = $this->get_data($name);
        $hit_parade = [];
        if (!empty($string_parade)) {
            $string_parade = explode(',', (string) $string_parade);
            foreach ($string_parade as $hit) {
                $hit_parade[] = $hit + 0;
                //cast to integer
            }
        }
        $this->data[$name] = $hit_parade;
        return $hit_parade;
    }
    /**
     * Get the entry comments
     *
     * @return int
     */
    public function get_comment_count()
    {
        $name = 'comments';
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        $comments = $this->get_data($name, 'string');
        if (!$comments) {
            $this->data[$name] = null;
            return $this->data[$name];
        }
        return $comments;
    }
    /**
     * Get the entry data specified by name
     *
     * @return null|mixed
     */
    protected function get_data(string $name, string $type = 'string')
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $data = $this->xpath->evaluate($type . '(' . $this->get_xpath_prefix() . '/slash10:' . $name . ')');
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
        $this->xpath->register_namespace('slash10', 'http://purl.org/rss/1.0/modules/slash/');
    }
}