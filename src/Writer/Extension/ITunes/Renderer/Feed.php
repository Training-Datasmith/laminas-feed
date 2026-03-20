<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\I_Tunes\Renderer;

use Dom_Document;
use Dom_Element;
use function implode;
use function is_array;
use Laminas\Feed\Writer\Extension;
class Feed extends Extension\Abstract_Renderer
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
     * Render feed
     */
    public function render(): void
    {
        $this->_set_authors($this->dom, $this->base);
        $this->_set_block($this->dom, $this->base);
        $this->_set_categories($this->dom, $this->base);
        $this->_set_image($this->dom, $this->base);
        $this->_set_duration($this->dom, $this->base);
        $this->_set_explicit($this->dom, $this->base);
        $this->_set_keywords($this->dom, $this->base);
        $this->_set_new_feed_url($this->dom, $this->base);
        $this->_set_owners($this->dom, $this->base);
        $this->_set_subtitle($this->dom, $this->base);
        $this->_set_summary($this->dom, $this->base);
        $this->_set_type($this->dom, $this->base);
        $this->_set_complete($this->dom, $this->base);
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Append feed namespaces
     *
     * @return void
     */
    protected function _append_namespaces()
    {
        $this->get_root_element()->set_attribute('xmlns:itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
    }
    /**
     * Set feed authors
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
        }
        $this->called = true;
    }
    /**
     * Set feed itunes block
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
     * Set feed categories
     *
     * @return void
     */
    protected function _set_categories(Dom_Document $dom, Dom_Element $root)
    {
        $cats = $this->get_data_container()->get_itunes_categories();
        if (!$cats || empty($cats)) {
            return;
        }
        foreach ($cats as $key => $cat) {
            if (!is_array($cat)) {
                $el = $dom->create_element('itunes:category');
                $el->set_attribute('text', $cat);
                $root->append_child($el);
            } else {
                $el = $dom->create_element('itunes:category');
                $el->set_attribute('text', $key);
                $root->append_child($el);
                foreach ($cat as $subcat) {
                    $el2 = $dom->create_element('itunes:category');
                    $el2->set_attribute('text', $subcat);
                    $el->append_child($el2);
                }
            }
        }
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
     * Set feed cumulative duration
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
     * Set feed keywords
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
     * Set feed's new URL
     *
     * @return void
     */
    protected function _set_new_feed_url(Dom_Document $dom, Dom_Element $root)
    {
        $url = $this->get_data_container()->get_itunes_new_feed_url();
        if (!$url) {
            return;
        }
        $el = $dom->create_element('itunes:new-feed-url');
        $text = $dom->create_text_node((string) $url);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed owners
     *
     * @return void
     */
    protected function _set_owners(Dom_Document $dom, Dom_Element $root)
    {
        $owners = $this->get_data_container()->get_itunes_owners();
        if (!$owners || empty($owners)) {
            return;
        }
        foreach ($owners as $owner) {
            $el = $dom->create_element('itunes:owner');
            $name = $dom->create_element('itunes:name');
            $text = $dom->create_text_node((string) $owner['name']);
            $name->append_child($text);
            $email = $dom->create_element('itunes:email');
            $text = $dom->create_text_node((string) $owner['email']);
            $email->append_child($text);
            $root->append_child($el);
            $el->append_child($name);
            $el->append_child($email);
        }
        $this->called = true;
    }
    /**
     * Set feed subtitle
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
     * Set feed summary
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
     * Set podcast type
     *
     * @return void
     */
    protected function _set_type(Dom_Document $dom, Dom_Element $root)
    {
        $type = $this->get_data_container()->get_itunes_type();
        if (!$type) {
            return;
        }
        $el = $dom->create_element('itunes:type');
        $text = $dom->create_text_node((string) $type);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set complete status
     *
     * @return void
     */
    protected function _set_complete(Dom_Document $dom, Dom_Element $root)
    {
        $status = $this->get_data_container()->get_itunes_complete();
        if (!$status) {
            return;
        }
        $el = $dom->create_element('itunes:complete');
        $text = $dom->create_text_node('Yes');
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}