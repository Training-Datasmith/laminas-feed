<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Threading\Renderer;

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
        if (strtolower($this->get_type()) === 'rss') {
            return;
            // Atom 1.0 only
        }
        $this->_set_comment_link($this->dom, $this->base);
        $this->_set_comment_feed_links($this->dom, $this->base);
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
        $this->get_root_element()->set_attribute('xmlns:thr', 'http://purl.org/syndication/thread/1.0');
    }
    /**
     * Set comment link
     *
     * @return void
     */
    protected function _set_comment_link(Dom_Document $dom, Dom_Element $root)
    {
        $link = $this->get_data_container()->get_comment_link();
        if (!$link) {
            return;
        }
        $clink = $this->dom->create_element('link');
        $clink->set_attribute('rel', 'replies');
        $clink->set_attribute('type', 'text/html');
        $clink->set_attribute('href', $link);
        $count = $this->get_data_container()->get_comment_count();
        if ($count !== null) {
            $clink->set_attribute('thr:count', $count);
        }
        $root->append_child($clink);
        $this->called = true;
    }
    /**
     * Set comment feed links
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
            $flink = $this->dom->create_element('link');
            $flink->set_attribute('rel', 'replies');
            $flink->set_attribute('type', 'application/' . $link['type'] . '+xml');
            $flink->set_attribute('href', $link['uri']);
            $count = $this->get_data_container()->get_comment_count();
            if ($count !== null) {
                $flink->set_attribute('thr:count', $count);
            }
            $root->append_child($flink);
            $this->called = true;
        }
    }
    /**
     * Set entry comment count
     *
     * @return void
     */
    protected function _set_comment_count(Dom_Document $dom, Dom_Element $root)
    {
        $count = $this->get_data_container()->get_comment_count();
        if ($count === null || !is_numeric($count)) {
            return;
        }
        $tcount = $this->dom->create_element('thr:total');
        $tcount->node_value = (string) $count;
        $root->append_child($tcount);
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}