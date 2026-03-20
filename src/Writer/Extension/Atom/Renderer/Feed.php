<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Atom\Renderer;

use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer\Extension;
use function strtolower;
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
        /**
         * RSS 2.0 only. Used mainly to include Atom links and
         * Pubsubhubbub Hub endpoint URIs under the Atom namespace
         */
        if (strtolower($this->get_type()) === 'atom') {
            return;
        }
        $this->_set_feed_links($this->dom, $this->base);
        $this->_set_hubs($this->dom, $this->base);
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Append namespaces to root element of feed
     *
     * @return void
     */
    protected function _append_namespaces()
    {
        $this->get_root_element()->set_attribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
    }
    /**
     * Set feed link elements
     *
     * @return void
     */
    protected function _set_feed_links(Dom_Document $dom, Dom_Element $root)
    {
        $flinks = $this->get_data_container()->get_feed_links();
        if (!$flinks || empty($flinks)) {
            return;
        }
        foreach ($flinks as $type => $href) {
            if (strtolower((string) $type) === $this->get_type()) {
                // issue 2605
                $mime = 'application/' . strtolower((string) $type) . '+xml';
                $flink = $dom->create_element('atom:link');
                $root->append_child($flink);
                $flink->set_attribute('rel', 'self');
                $flink->set_attribute('type', $mime);
                $flink->set_attribute('href', $href);
            }
        }
        $this->called = true;
    }
    /**
     * Set PuSH hubs
     *
     * @return void
     */
    protected function _set_hubs(Dom_Document $dom, Dom_Element $root)
    {
        $hubs = $this->get_data_container()->get_hubs();
        if (!$hubs || empty($hubs)) {
            return;
        }
        foreach ($hubs as $hub_url) {
            $hub = $dom->create_element('atom:link');
            $hub->set_attribute('rel', 'hub');
            $hub->set_attribute('href', $hub_url);
            $root->append_child($hub);
        }
        $this->called = true;
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}