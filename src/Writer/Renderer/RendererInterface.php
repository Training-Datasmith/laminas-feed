<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer;

use Dom_Document;
use Dom_Element;
interface Renderer_Interface
{
    /**
     * Render feed/entry
     *
     * @return void
     */
    public function render();
    /**
     * Save feed and/or entry to XML and return string
     *
     * @return string
     */
    public function save_xml();
    /**
     * Get DOM document
     *
     * @return DOMDocument
     */
    public function get_dom_document();
    /**
     * Get document element from DOM
     *
     * @return DOMElement
     */
    public function get_element();
    /**
     * Get data container containing feed items
     *
     * @return mixed
     */
    public function get_data_container();
    /**
     * Should exceptions be ignored?
     *
     * @return mixed
     */
    public function ignore_exceptions();
    /**
     * Get list of thrown exceptions
     *
     * @return array
     */
    public function get_exceptions();
    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function set_type($type);
    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function get_type();
    /**
     * Sets the absolute root element for the XML feed being generated. This
     * helps simplify the appending of namespace declarations, but also ensures
     * namespaces are added to the root element - not scattered across the entire
     * XML file - may assist namespace unsafe parsers and looks pretty ;).
     */
    public function set_root_element(Dom_Element $root);
    /**
     * Retrieve the absolute root element for the XML feed being generated.
     *
     * @return DOMElement
     */
    public function get_root_element();
}