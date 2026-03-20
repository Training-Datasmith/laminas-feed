<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Google_Play_Podcast\Renderer;

use Dom_Document;
use Dom_Element;
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
        $this->_set_explicit($this->dom, $this->base);
        $this->_set_description($this->dom, $this->base);
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
        $this->get_root_element()->set_attribute('xmlns:googleplay', 'http://www.google.com/schemas/play-podcasts/1.0');
    }
    /**
     * Set feed authors
     *
     * @return void
     */
    protected function _set_authors(Dom_Document $dom, Dom_Element $root)
    {
        $authors = $this->get_data_container()->get_play_podcast_authors();
        if (!$authors || empty($authors)) {
            return;
        }
        foreach ($authors as $author) {
            $el = $dom->create_element('googleplay:author');
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
        $block = $this->get_data_container()->get_play_podcast_block();
        if ($block === null) {
            return;
        }
        $el = $dom->create_element('googleplay:block');
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
        $cats = $this->get_data_container()->get_play_podcast_categories();
        if (!$cats || empty($cats)) {
            return;
        }
        foreach ($cats as $key => $cat) {
            if (!is_array($cat)) {
                $el = $dom->create_element('googleplay:category');
                $el->set_attribute('text', $cat);
                $root->append_child($el);
            } else {
                $el = $dom->create_element('googleplay:category');
                $el->set_attribute('text', $key);
                $root->append_child($el);
                foreach ($cat as $subcat) {
                    $el2 = $dom->create_element('googleplay:category');
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
        $image = $this->get_data_container()->get_play_podcast_image();
        if (!$image) {
            return;
        }
        $el = $dom->create_element('googleplay:image');
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
        $explicit = $this->get_data_container()->get_play_podcast_explicit();
        if ($explicit === null) {
            return;
        }
        $el = $dom->create_element('googleplay:explicit');
        $text = $dom->create_text_node((string) $explicit);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set podcast description
     *
     * @return void
     */
    protected function _set_description(Dom_Document $dom, Dom_Element $root)
    {
        $description = $this->get_data_container()->get_play_podcast_description();
        if (!$description) {
            return;
        }
        $el = $dom->create_element('googleplay:description');
        $text = $dom->create_text_node((string) $description);
        $el->append_child($text);
        $root->append_child($el);
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}