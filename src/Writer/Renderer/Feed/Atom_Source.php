<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer\Feed;

use function array_key_exists;
use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;
class Atom_Source extends Abstract_Atom implements Renderer\Renderer_Interface
{
    public function __construct(Writer\Source $container)
    {
        parent::__construct($container);
    }
    /**
     * Render Atom Feed Metadata (Source element)
     *
     * @return $this
     */
    public function render(): static
    {
        if (!$this->container->get_encoding()) {
            $this->container->set_encoding('UTF-8');
        }
        $this->dom = new Dom_Document('1.0', $this->container->get_encoding());
        $this->dom->format_output = true;
        $root = $this->dom->create_element('source');
        $this->set_root_element($root);
        $this->dom->append_child($root);
        $this->_set_language($this->dom, $root);
        $this->_set_base_url($this->dom, $root);
        $this->_set_title($this->dom, $root);
        $this->_set_description($this->dom, $root);
        $this->_set_date_created($this->dom, $root);
        $this->_set_date_modified($this->dom, $root);
        $this->_set_generator($this->dom, $root);
        $this->_set_link($this->dom, $root);
        $this->_set_feed_links($this->dom, $root);
        $this->_set_id($this->dom, $root);
        $this->_set_authors($this->dom, $root);
        $this->_set_copyright($this->dom, $root);
        $this->_set_categories($this->dom, $root);
        foreach ($this->extensions as $ext) {
            $ext->set_type($this->get_type());
            $ext->set_root_element($this->get_root_element());
            $ext->set_dom_document($this->get_dom_document(), $root);
            $ext->render();
        }
        return $this;
    }
    /**
     * Set feed generator string
     *
     * @return void
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _set_generator(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_generator()) {
            return;
        }
        $gdata = $this->get_data_container()->get_generator();
        $generator = $dom->create_element('generator');
        $root->append_child($generator);
        $text = $dom->create_text_node((string) $gdata['name']);
        $generator->append_child($text);
        if (array_key_exists('uri', $gdata)) {
            $generator->set_attribute('uri', $gdata['uri']);
        }
        if (array_key_exists('version', $gdata)) {
            $generator->set_attribute('version', $gdata['version']);
        }
    }
}