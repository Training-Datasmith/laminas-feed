<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension;

use Dom_Document;
use Domx_Path;
use Laminas\Feed\Reader;
abstract class Abstract_Feed
{
    /**
     * Parsed feed data
     *
     * @var array
     */
    protected $data = [];
    /**
     * Parsed feed data in the shape of a DOMDocument
     *
     * @var DOMDocument
     */
    protected $dom_document;
    /**
     * The base XPath query used to retrieve feed data
     *
     * @var DOMXPath
     */
    protected $xpath;
    /**
     * The XPath prefix
     *
     * @var string
     */
    protected $xpath_prefix = '';
    /**
     * Set the DOM document
     *
     * @return $this
     */
    public function set_dom_document(Dom_Document $dom)
    {
        $this->dom_document = $dom;
        return $this;
    }
    /**
     * Get the DOM
     *
     * @return DOMDocument
     */
    public function get_dom_document()
    {
        return $this->dom_document;
    }
    /**
     * Get the Feed's encoding
     *
     * @return string
     */
    public function get_encoding()
    {
        return $this->get_dom_document()->encoding;
    }
    /**
     * Set the feed type
     *
     * @param  string $type
     * @return $this
     */
    public function set_type($type)
    {
        $this->data['type'] = $type;
        return $this;
    }
    /**
     * Get the feed type
     *
     * If null, it will attempt to autodetect the type.
     *
     * @return string
     */
    public function get_type()
    {
        $type = $this->data['type'];
        if (null === $type) {
            $type = Reader\Reader::detect_type($this->get_dom_document());
            $this->set_type($type);
        }
        return $type;
    }
    /**
     * Return the feed as an array
     *
     * @return array
     */
    public function to_array()
    {
        return $this->data;
    }
    /**
     * Set the XPath query
     *
     * @return $this
     */
    public function set_xpath(?Domx_Path $xpath = null)
    {
        if (null === $xpath) {
            $this->xpath = null;
            return $this;
        }
        $this->xpath = $xpath;
        $this->register_namespaces();
        return $this;
    }
    /**
     * Get the DOMXPath object
     *
     * @return DOMXPath
     */
    public function get_xpath()
    {
        if (null === $this->xpath) {
            $this->set_xpath(new Domx_Path($this->get_dom_document()));
        }
        return $this->xpath;
    }
    /**
     * Get the XPath prefix
     *
     * @return string
     */
    public function get_xpath_prefix()
    {
        return $this->xpath_prefix;
    }
    /**
     * Set the XPath prefix
     *
     * @param string $prefix
     */
    public function set_xpath_prefix($prefix): void
    {
        $this->xpath_prefix = $prefix;
    }
    /**
     * Register the default namespaces for the current feed format
     */
    abstract protected function register_namespaces();
}