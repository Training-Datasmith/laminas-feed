<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Well_Formed_Web\Renderer;

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
            // RSS 2.0 only
        }
        $this->_set_comment_feed_links($this->dom, $this->base);
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
        $this->get_root_element()->set_attribute('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
    }
    /**
     * Set entry comment feed links
     *
     * @return void
     */
    protected function _set_comment_feed_links(Dom_Document $dom, Dom_Element $root)
    {
        $links = $this->get_data_container()->get_comment_feed_links();
        if (!$links || empty($links)) {
            return;
        }
        foreach ($links as $link) {
            if ($link['type'] === 'rss') {
                $flink = $this->dom->create_element('wfw:commentRss');
                $text = $dom->create_text_node((string) $link['uri']);
                $flink->append_child($text);
                $root->append_child($flink);
            }
        }
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}