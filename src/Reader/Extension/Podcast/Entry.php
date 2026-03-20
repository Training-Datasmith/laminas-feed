<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Podcast;

use const E_USER_DEPRECATED;
use Laminas\Feed\Reader\Extension;
use function trigger_error;
class Entry extends Extension\Abstract_Entry
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
     * Get the entry duration
     *
     * @return string
     */
    public function get_duration()
    {
        if (isset($this->data['duration'])) {
            return $this->data['duration'];
        }
        $duration = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:duration)');
        if (!$duration) {
            $duration = null;
        }
        $this->data['duration'] = $duration;
        return $this->data['duration'];
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
     * Get the entry title
     *
     * @return string
     */
    public function get_title()
    {
        if (isset($this->data['title'])) {
            return $this->data['title'];
        }
        $title = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:title)');
        if (!$title) {
            $title = null;
        }
        $this->data['title'] = $title;
        return $this->data['title'];
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
     * Get the entry image
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
     * Get the episode number
     *
     * @return null|int
     */
    public function get_episode()
    {
        if (isset($this->data['episode'])) {
            return $this->data['episode'];
        }
        $episode = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:episode)');
        if (!$episode) {
            $episode = null;
        }
        $this->data['episode'] = null === $episode ? $episode : (int) $episode;
        return $this->data['episode'];
    }
    /**
     * Get the episode number
     *
     * @return string One of "full", "trailer", or "bonus"; defaults to "full".
     */
    public function get_episode_type()
    {
        if (isset($this->data['episodeType'])) {
            return $this->data['episodeType'];
        }
        $type = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:episodeType)');
        if (!$type) {
            $type = 'full';
        }
        $this->data['episodeType'] = (string) $type;
        return $this->data['episodeType'];
    }
    /**
     * Is the episode closed captioned?
     *
     * Returns true only if itunes:isClosedCaptioned has the value 'Yes'.
     *
     * @return bool
     */
    public function is_closed_captioned()
    {
        if (isset($this->data['isClosedCaptioned'])) {
            return $this->data['isClosedCaptioned'];
        }
        $status = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:isClosedCaptioned)');
        $this->data['isClosedCaptioned'] = $status === 'Yes';
        return $this->data['isClosedCaptioned'];
    }
    /**
     * Get the season number
     *
     * @return null|int
     */
    public function get_season()
    {
        if (isset($this->data['season'])) {
            return $this->data['season'];
        }
        $season = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/itunes:season)');
        if (!$season) {
            $season = null;
        }
        $this->data['season'] = null === $season ? $season : (int) $season;
        return $this->data['season'];
    }
    /**
     * Register iTunes namespace
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
    }
}