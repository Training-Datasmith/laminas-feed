<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension;

use Dom_Document;
use Dom_Element;
use Domx_Path;
use Laminas\Feed\Reader;
abstract class Abstract_Entry
{
    /**
     * Feed entry data
     *
     * @var array
     */
    protected $data = [];
    /**
     * DOM document object
     *
     * @var DOMDocument
     */
    protected $dom_document;
    /**
     * Entry instance
     *
     * @var DOMElement
     */
    protected $entry;
    /**
     * Pointer to the current entry
     *
     * @var int
     */
    protected $entry_key = 0;
    /**
     * XPath object
     *
     * @var DOMXPath
     */
    protected $xpath;
    /**
     * XPath query
     *
     * @var string
     */
    protected $xpath_prefix = '';
    /**
     * Set the entry DOMElement
     *
     * Has side effect of setting the DOMDocument for the entry.
     *
     * @return $this
     */
    public function set_entry_element(Dom_Element $entry)
    {
        $this->entry = $entry;
        $this->dom_document = $entry->owner_document;
        return $this;
    }
    /**
     * Get the entry DOMElement
     *
     * @return DOMElement
     */
    public function get_entry_element()
    {
        return $this->entry;
    }
    /**
     * Set the entry key
     *
     * @param  string $entryKey
     * @return $this
     */
    public function set_entry_key($entry_key)
    {
        $this->entry_key = $entry_key;
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
     * Get the Entry's encoding
     *
     * @return string
     */
    public function get_encoding()
    {
        return $this->get_dom_document()->encoding;
    }
    /**
     * Set the entry type
     *
     * Has side effect of setting xpath prefix
     *
     * @param  string $type
     * @return $this
     */
    public function set_type($type)
    {
        if (null === $type) {
            $this->data['type'] = null;
            return $this;
        }
        $this->data['type'] = $type;
        if ($type === Reader\Reader::TYPE_RSS_10 || $type === Reader\Reader::TYPE_RSS_090) {
            $this->set_xpath_prefix('//rss:item[' . ((int) $this->entry_key + 1) . ']');
            return $this;
        }
        if ($type === Reader\Reader::TYPE_ATOM_10 || $type === Reader\Reader::TYPE_ATOM_03) {
            $this->set_xpath_prefix('//atom:entry[' . ((int) $this->entry_key + 1) . ']');
            return $this;
        }
        $this->set_xpath_prefix('//item[' . ((int) $this->entry_key + 1) . ']');
        return $this;
    }
    /**
     * Get the entry type
     *
     * @return string
     */
    public function get_type()
    {
        $type = $this->data['type'];
        if ($type === null) {
            $type = Reader\Reader::detect_type($this->get_entry_element(), true);
            $this->set_type($type);
        }
        return $type;
    }
    /**
     * Set the XPath query
     *
     * @return $this
     */
    public function set_xpath(Domx_Path $xpath)
    {
        $this->xpath = $xpath;
        $this->register_namespaces();
        return $this;
    }
    /**
     * Get the XPath query object
     *
     * @return DOMXPath
     */
    public function get_xpath()
    {
        if (!$this->xpath) {
            $this->set_xpath(new Domx_Path($this->get_dom_document()));
        }
        return $this->xpath;
    }
    /**
     * Serialize the entry to an array
     *
     * @return array
     */
    public function to_array()
    {
        return $this->data;
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
     * @param  string $prefix
     * @return $this
     */
    public function set_xpath_prefix($prefix)
    {
        $this->xpath_prefix = $prefix;
        return $this;
    }
    /**
     * Register XML namespaces
     *
     * @return void
     */
    abstract protected function register_namespaces();
}