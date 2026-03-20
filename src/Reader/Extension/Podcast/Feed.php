<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Podcast;

use Dom_Text;
use const E_USER_DEPRECATED;
use Laminas\Feed\Reader\Extension;
use function trigger_error;
class Feed extends Extension\Abstract_Feed
{
    /**
     * Get the entry author
     *
     * @return string
     */
    public function get_cast_author()
    {
        if (isset($this->data['author'])) {
            return $this->data['author'];
        }
        $author = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:author)');
        if (!$author) {
            $author = null;
        }
        $this->data['author'] = $author;
        return $this->data['author'];
    }
    /**
     * Get the entry block
     *
     * @return string
     */
    public function get_block()
    {
        if (isset($this->data['block'])) {
            return $this->data['block'];
        }
        $block = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:block)');
        if (!$block) {
            $block = null;
        }
        $this->data['block'] = $block;
        return $this->data['block'];
    }
    /**
     * Get the entry category
     *
     * @return null|array
     */
    public function get_itunes_categories()
    {
        if (isset($this->data['categories'])) {
            return $this->data['categories'];
        }
        $category_list = $this->xpath->query($this->get_xpath_prefix() . '/itunes:category');
        $categories = [];
        if ($category_list->length > 0) {
            foreach ($category_list as $node) {
                $children = [];
                if ($node->child_nodes->length > 0) {
                    foreach ($node->child_nodes as $child_node) {
                        if (!$child_node instanceof Dom_Text) {
                            $children[$child_node->get_attribute('text')] = null;
                        }
                    }
                }
                $categories[$node->get_attribute('text')] = $children === [] ? null : $children;
            }
        }
        if (!$categories) {
            $categories = null;
        }
        $this->data['categories'] = $categories;
        return $this->data['categories'];
    }
    /**
     * Get the entry explicit
     *
     * @return string
     */
    public function get_explicit()
    {
        if (isset($this->data['explicit'])) {
            return $this->data['explicit'];
        }
        $explicit = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:explicit)');
        if (!$explicit) {
            $explicit = null;
        }
        $this->data['explicit'] = $explicit;
        return $this->data['explicit'];
    }
    /**
     * Get the feed/podcast image
     *
     * @return string
     */
    public function get_itunes_image()
    {
        if (isset($this->data['image'])) {
            return $this->data['image'];
        }
        $image = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:image/@href)');
        if (!$image) {
            $image = null;
        }
        $this->data['image'] = $image;
        return $this->data['image'];
    }
    /**
     * Get the entry keywords
     *
     * @deprecated since 2.10.0; itunes:keywords is no longer part of the
     *     iTunes podcast RSS specification.
     *
     * @return string
     */
    public function get_keywords()
    {
        trigger_error('itunes:keywords has been deprecated in the iTunes podcast RSS specification,' . ' and should not be relied on.', E_USER_DEPRECATED);
        if (isset($this->data['keywords'])) {
            return $this->data['keywords'];
        }
        $keywords = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:keywords)');
        if (!$keywords) {
            $keywords = null;
        }
        $this->data['keywords'] = $keywords;
        return $this->data['keywords'];
    }
    /**
     * Get the entry's new feed url
     *
     * @return string
     */
    public function get_new_feed_url()
    {
        if (isset($this->data['new-feed-url'])) {
            return $this->data['new-feed-url'];
        }
        $new_feed_url = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:new-feed-url)');
        if (!$new_feed_url) {
            $new_feed_url = null;
        }
        $this->data['new-feed-url'] = $new_feed_url;
        return $this->data['new-feed-url'];
    }
    /**
     * Get the entry owner
     *
     * @return string
     */
    public function get_owner()
    {
        if (isset($this->data['owner'])) {
            return $this->data['owner'];
        }
        $owner = null;
        $email = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:owner/itunes:email)');
        $name = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:owner/itunes:name)');
        if (!empty($email)) {
            $owner = $email . (empty($name) ? '' : ' (' . $name . ')');
        } elseif (!empty($name)) {
            $owner = $name;
        }
        if (!$owner) {
            $owner = null;
        }
        $this->data['owner'] = $owner;
        return $this->data['owner'];
    }
    /**
     * Get the entry subtitle
     *
     * @return string
     */
    public function get_subtitle()
    {
        if (isset($this->data['subtitle'])) {
            return $this->data['subtitle'];
        }
        $subtitle = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:subtitle)');
        if (!$subtitle) {
            $subtitle = null;
        }
        $this->data['subtitle'] = $subtitle;
        return $this->data['subtitle'];
    }
    /**
     * Get the entry summary
     *
     * @return string
     */
    public function get_summary()
    {
        if (isset($this->data['summary'])) {
            return $this->data['summary'];
        }
        $summary = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:summary)');
        if (!$summary) {
            $summary = null;
        }
        $this->data['summary'] = $summary;
        return $this->data['summary'];
    }
    /**
     * Get the type of podcast
     *
     * @return string One of "episodic" or "serial". Defaults to "episodic"
     *     if no itunes:type tag is encountered.
     */
    public function get_podcast_type()
    {
        if (isset($this->data['podcastType'])) {
            return $this->data['podcastType'];
        }
        $type = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:type)');
        if (!$type) {
            $type = 'episodic';
        }
        $this->data['podcastType'] = (string) $type;
        return $this->data['podcastType'];
    }
    /**
     * Is the podcast complete (no more episodes will post)?
     *
     * @return bool
     */
    public function is_complete()
    {
        if (isset($this->data['complete'])) {
            return $this->data['complete'];
        }
        $complete = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:complete)');
        if (!$complete) {
            $complete = false;
        }
        $this->data['complete'] = $complete === 'Yes';
        return $this->data['complete'];
    }
    /**
     * Register iTunes namespace
     *
     * @return void
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
    }
}