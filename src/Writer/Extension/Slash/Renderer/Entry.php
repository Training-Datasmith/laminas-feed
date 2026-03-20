<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Slash\Renderer;

use Dom_Document;
use Dom_Element;
use function is_numeric;
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
            // RSS 2.0 only
        }
        $this->_set_comment_count($this->dom, $this->base);
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Append entry namespaces
     *
     * @return void
     */
    protected function _append_namespaces()
    {
        $this->get_root_element()->set_attribute('xmlns:slash', 'http://purl.org/rss/1.0/modules/slash/');
    }
    /**
     * Set entry comment count
     *
     * @return void
     */
    protected function _set_comment_count(Dom_Document $dom, Dom_Element $root)
    {
        $count = $this->get_data_container()->get_comment_count();
        if (!$count || !is_numeric($count)) {
            $count = 0;
        }
        $tcount = $this->dom->create_element('slash:comments');
        $tcount->node_value = (string) $count;
        $root->append_child($tcount);
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}