<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Dublin_Core\Renderer;

use function array_key_exists;
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
        $this->_set_authors($this->dom, $this->base);
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Append namespaces to entry
     *
     * @return void
     */
    protected function _append_namespaces()
    {
        $this->get_root_element()->set_attribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
    }
    /**
     * Set entry author elements
     *
     * @return void
     */
    protected function _set_authors(Dom_Document $dom, Dom_Element $root)
    {
        $authors = $this->get_data_container()->get_authors();
        if (!$authors || empty($authors)) {
            return;
        }
        foreach ($authors as $data) {
            $author = $this->dom->create_element('dc:creator');
            if (array_key_exists('name', $data)) {
                $text = $dom->create_text_node((string) $data['name']);
                $author->append_child($text);
                $root->append_child($author);
            }
        }
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}