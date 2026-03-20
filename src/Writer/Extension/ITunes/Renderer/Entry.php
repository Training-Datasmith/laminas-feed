<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\I_Tunes\Renderer;

use Dom_Document;
use Dom_Element;
use function implode;
use Laminas\Feed\Writer\Extension;
class Entry extends Extension\Abstract_Renderer
{
    /**
     * Set to TRUE if a rendering method actually renders something. This
     * is used to prevent premature appending of a XML namespace declaration
     * until an element which requires it is actually appended.
     *
     * @var bool
     */
    protected $called = false;
    /**
     * Render entry
     */
    public function render(): void
    {
        $this->_set_authors($this->dom, $this->base);
        $this->_set_block($this->dom, $this->base);
        $this->_set_duration($this->dom, $this->base);
        $this->_set_image($this->dom, $this->base);
        $this->_set_explicit($this->dom, $this->base);
        $this->_set_keywords($this->dom, $this->base);
        $this->_set_title($this->dom, $this->base);
        $this->_set_subtitle($this->dom, $this->base);
        $this->_set_summary($this->dom, $this->base);
        $this->_set_episode($this->dom, $this->base);
        $this->_set_episode_type($this->dom, $this->base);
        $this->_set_closed_captioned($this->dom, $this->base);
        $this->_set_season($this->dom, $this->base);
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Append namespaces to entry root
     *
     * @return void
     */
    protected function _append_namespaces()
    {
        $this->get_root_element()->set_attribute('xmlns:itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
    }
    /**
     * Set entry authors
     *
     * @return void
     */
    protected function _set_authors(Dom_Document $dom, Dom_Element $root)
    {
        $authors = $this->get_data_container()->get_itunes_authors();
        if (!$authors || empty($authors)) {
            return;
        }
        foreach ($authors as $author) {
            $el = $dom->create_element('itunes:author');
            $text = $dom->create_text_node((string) $author);
            $el->append_child($text);
            $root->append_child($el);
            $this->called = true;
        }
    }
    /**
     * Set itunes block
     *
     * @return void
     */
    protected function _set_block(Dom_Document $dom, Dom_Element $root)
    {
        $block = $this->get_data_container()->get_itunes_block();
        if ($block === null) {
            return;
        }
        $el = $dom->create_element('itunes:block');
        $text = $dom->create_text_node((string) $block);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry duration
     *
     * @return void
     */
    protected function _set_duration(Dom_Document $dom, Dom_Element $root)
    {
        $duration = $this->get_data_container()->get_itunes_duration();
        if (!$duration) {
            return;
        }
        $el = $dom->create_element('itunes:duration');
        $text = $dom->create_text_node((string) $duration);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed image (icon)
     *
     * @return void
     */
    protected function _set_image(Dom_Document $dom, Dom_Element $root)
    {
        $image = $this->get_data_container()->get_itunes_image();
        if (!$image) {
            return;
        }
        $el = $dom->create_element('itunes:image');
        $el->set_attribute('href', $image);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set explicit flag
     *
     * @return void
     */
    protected function _set_explicit(Dom_Document $dom, Dom_Element $root)
    {
        $explicit = $this->get_data_container()->get_itunes_explicit();
        if ($explicit === null) {
            return;
        }
        $el = $dom->create_element('itunes:explicit');
        $text = $dom->create_text_node((string) $explicit);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry keywords
     *
     * @return void
     */
    protected function _set_keywords(Dom_Document $dom, Dom_Element $root)
    {
        $keywords = $this->get_data_container()->get_itunes_keywords();
        if (!$keywords || empty($keywords)) {
            return;
        }
        $el = $dom->create_element('itunes:keywords');
        $text = $dom->create_text_node(implode(',', $keywords));
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry title
     *
     * @return void
     */
    protected function _set_title(Dom_Document $dom, Dom_Element $root)
    {
        $title = $this->get_data_container()->get_itunes_title();
        if (!$title) {
            return;
        }
        $el = $dom->create_element('itunes:title');
        $text = $dom->create_text_node((string) $title);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry subtitle
     *
     * @return void
     */
    protected function _set_subtitle(Dom_Document $dom, Dom_Element $root)
    {
        $subtitle = $this->get_data_container()->get_itunes_subtitle();
        if (!$subtitle) {
            return;
        }
        $el = $dom->create_element('itunes:subtitle');
        $text = $dom->create_text_node((string) $subtitle);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry summary
     *
     * @return void
     */
    protected function _set_summary(Dom_Document $dom, Dom_Element $root)
    {
        $summary = $this->get_data_container()->get_itunes_summary();
        if (!$summary) {
            return;
        }
        $el = $dom->create_element('itunes:summary');
        $text = $dom->create_text_node((string) $summary);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry episode number
     *
     * @return void
     */
    protected function _set_episode(Dom_Document $dom, Dom_Element $root)
    {
        $episode = $this->get_data_container()->get_itunes_episode();
        if (!$episode) {
            return;
        }
        $el = $dom->create_element('itunes:episode');
        $text = $dom->create_text_node((string) $episode);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry episode type
     *
     * @return void
     */
    protected function _set_episode_type(Dom_Document $dom, Dom_Element $root)
    {
        $type = $this->get_data_container()->get_itunes_episode_type();
        if (!$type) {
            return;
        }
        $el = $dom->create_element('itunes:episodeType');
        $text = $dom->create_text_node((string) $type);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set closed captioning status for episode
     *
     * @return void
     */
    protected function _set_closed_captioned(Dom_Document $dom, Dom_Element $root)
    {
        $status = $this->get_data_container()->get_itunes_is_closed_captioned();
        if (!$status) {
            return;
        }
        $el = $dom->create_element('itunes:isClosedCaptioned');
        $text = $dom->create_text_node('Yes');
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry season number
     *
     * @return void
     */
    protected function _set_season(Dom_Document $dom, Dom_Element $root)
    {
        $season = $this->get_data_container()->get_itunes_season();
        if (!$season) {
            return;
        }
        $el = $dom->create_element('itunes:season');
        $text = $dom->create_text_node((string) $season);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}