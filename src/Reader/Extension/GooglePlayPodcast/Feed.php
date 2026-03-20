<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Google_Play_Podcast;

use Dom_Text;
use Laminas\Feed\Reader\Extension;
class Feed extends Extension\Abstract_Feed
{
    /**
     * Get the entry author
     *
     * @return string
     */
    public function get_play_podcast_author()
    {
        if (isset($this->data['author'])) {
            return $this->data['author'];
        }
        $author = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/googleplay:author)');
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
    public function get_play_podcast_block()
    {
        if (isset($this->data['block'])) {
            return $this->data['block'];
        }
        $block = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/googleplay:block)');
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
    public function get_play_podcast_categories()
    {
        if (isset($this->data['categories'])) {
            return $this->data['categories'];
        }
        $category_list = $this->xpath->query($this->get_xpath_prefix() . '/googleplay:category');
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
    public function get_play_podcast_explicit()
    {
        if (isset($this->data['explicit'])) {
            return $this->data['explicit'];
        }
        $explicit = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/googleplay:explicit)');
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
    public function get_play_podcast_image()
    {
        if (isset($this->data['image'])) {
            return $this->data['image'];
        }
        $image = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/googleplay:image/@href)');
        if (!$image) {
            $image = null;
        }
        $this->data['image'] = $image;
        return $this->data['image'];
    }
    /**
     * Get the entry description
     *
     * @return string
     */
    public function get_play_podcast_description()
    {
        if (isset($this->data['description'])) {
            return $this->data['description'];
        }
        $description = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/googleplay:description)');
        if (!$description) {
            $description = null;
        }
        $this->data['description'] = $description;
        return $this->data['description'];
    }
    /**
     * Register googleplay namespace
     *
     * @return void
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('googleplay', 'http://www.google.com/schemas/play-podcasts/1.0');
    }
}