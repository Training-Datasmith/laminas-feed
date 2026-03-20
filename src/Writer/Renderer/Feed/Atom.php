<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer\Feed;

use Dom_Document;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;
class Atom extends Abstract_Atom implements Renderer\Renderer_Interface
{
    public function __construct(Writer\Feed $container)
    {
        parent::__construct($container);
    }
    /**
     * Render Atom feed
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
        $root = $this->dom->create_element_ns(Writer\Writer::NAMESPACE_ATOM_10, 'feed');
        $this->set_root_element($root);
        $this->dom->append_child($root);
        $this->_set_language($this->dom, $root);
        $this->_set_base_url($this->dom, $root);
        $this->_set_title($this->dom, $root);
        $this->_set_description($this->dom, $root);
        $this->_set_image($this->dom, $root);
        $this->_set_date_created($this->dom, $root);
        $this->_set_date_modified($this->dom, $root);
        $this->_set_generator($this->dom, $root);
        $this->_set_link($this->dom, $root);
        $this->_set_feed_links($this->dom, $root);
        $this->_set_id($this->dom, $root);
        $this->_set_authors($this->dom, $root);
        $this->_set_copyright($this->dom, $root);
        $this->_set_categories($this->dom, $root);
        $this->_set_hubs($this->dom, $root);
        foreach ($this->extensions as $ext) {
            $ext->set_type($this->get_type());
            $ext->set_root_element($this->get_root_element());
            $ext->set_dom_document($this->get_dom_document(), $root);
            $ext->render();
        }
        foreach ($this->container as $entry) {
            if ($this->get_data_container()->get_encoding()) {
                $entry->set_encoding($this->get_data_container()->get_encoding());
            }
            if ($entry instanceof Writer\Entry) {
                $renderer = new Renderer\Entry\Atom($entry);
            } else {
                if (!$this->dom->document_element->has_attribute('xmlns:at')) {
                    $this->dom->document_element->set_attribute('xmlns:at', 'http://purl.org/atompub/tombstones/1.0');
                }
                $renderer = new Renderer\Entry\Atom_Deleted($entry);
            }
            if ($this->ignore_exceptions === true) {
                $renderer->ignore_exceptions();
            }
            $renderer->set_type($this->get_type());
            $renderer->set_root_element($this->dom->document_element);
            $renderer->render();
            $element = $renderer->get_element();
            $imported = $this->dom->import_node($element, true);
            $root->append_child($imported);
        }
        return $this;
    }
}