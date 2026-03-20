<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Google_Play_Podcast;

use Laminas\Feed\Reader\Extension;
class Entry extends Extension\Abstract_Entry
{
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
     * Get the episode summary/description
     *
     * Uses verbiage so it does not conflict with base entry.
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
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('googleplay', 'http://www.google.com/schemas/play-podcasts/1.0');
    }
}