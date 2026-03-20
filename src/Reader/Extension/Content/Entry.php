<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Content;

use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Extension;
class Entry extends Extension\Abstract_Entry
{
    /** @return string */
    public function get_content(): mixed
    {
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            return $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/content:encoded)');
        }
        return $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/content:encoded)');
    }
    /**
     * Register RSS Content Module namespace
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('content', 'http://purl.org/rss/1.0/modules/content/');
    }
}