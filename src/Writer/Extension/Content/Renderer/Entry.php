<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Content\Renderer;

use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer\Extension;
use function strtolower;
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
        if (strtolower($this->get_type()) === 'atom') {
            return;
        }
        $this->_set_content($this->dom, $this->base);
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Append namespaces to root element
     *
     * @return void
     */
    protected function _append_namespaces()
    {
        $this->get_root_element()->set_attribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
    }
    /**
     * Set entry content
     *
     * @return void
     */
    protected function _set_content(Dom_Document $dom, Dom_Element $root)
    {
        $content = $this->get_data_container()->get_content();
        if (!$content) {
            return;
        }
        $element = $dom->create_element('content:encoded');
        $root->append_child($element);
        $cdata = $dom->create_cdata_section($content);
        $element->append_child($cdata);
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}