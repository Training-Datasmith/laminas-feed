<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader;

use function array_key_exists;
use function call_user_func_array;
use Dom_Document;
use Dom_Element;
use Domx_Path;
use function in_array;
use function method_exists;
/**
 * @deprecated This (abstract) class is deprecated. Use Laminas\Feed\Reader\Entry\AbstractEntry instead.
 */
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
     */
    protected ?\Dom_Document $dom_document;
    /**
     * XPath object
     *
     * @var DOMXPath
     */
    protected $xpath;
    /**
     * Registered extensions
     *
     * @var array
     */
    protected $extensions = [];
    /**
     * @param int $entryKey
     * @param null|string $type
     */
    public function __construct(
        /**
         * Entry instance
         */
        protected \Dom_Element $entry,
        /**
         * Pointer to the current entry
         */
        protected $entry_key,
        $type = null
    )
    {
        $this->dom_document = $this->entry->owner_document;
        if ($type !== null) {
            $this->data['type'] = $type;
        } else {
            $this->data['type'] = Reader::detect_type($this->entry);
        }
        $this->_load_extensions();
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
     * Get the entry element
     *
     * @return DOMElement
     */
    public function get_element()
    {
        return $this->entry;
    }
    /**
     * Get the Entry's encoding
     *
     * @return string
     */
    public function get_encoding()
    {
        $assumed = $this->get_dom_document()->encoding;
        if (empty($assumed)) {
            return 'UTF-8';
        }
        return $assumed;
    }
    /**
     * Get entry as xml
     *
     * @return string
     */
    public function save_xml()
    {
        $dom = new Dom_Document('1.0', $this->get_encoding());
        $entry = $dom->import_node($this->get_element(), true);
        $dom->append_child($entry);
        return $dom->save_xml();
    }
    /**
     * Get the entry type
     *
     * @return string
     */
    public function get_type()
    {
        return $this->data['type'];
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
     * Set the XPath query
     *
     * @return $this
     */
    public function set_xpath(Domx_Path $xpath)
    {
        $this->xpath = $xpath;
        return $this;
    }
    /**
     * Get registered extensions
     *
     * @return array
     */
    public function get_extensions()
    {
        return $this->extensions;
    }
    /**
     * Return an Extension object with the matching name (postfixed with _Entry)
     *
     * @return null|Extension\AbstractEntry
     */
    public function get_extension(string $name)
    {
        if (array_key_exists($name . '\Entry', $this->extensions)) {
            return $this->extensions[$name . '\Entry'];
        }
        return null;
    }
    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException If no extensions implements the method.
     */
    public function __call(string $method, array $args)
    {
        foreach ($this->extensions as $extension) {
            if (method_exists($extension, $method)) {
                return call_user_func_array([$extension, $method], $args);
            }
        }
        throw new Exception\BadMethodCallException('Method: ' . $method . ' does not exist and could not be located on a registered Extension');
    }
    /**
     * Load extensions from Laminas\Feed\Reader\Reader
     *
     * @return void
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _load_extensions()
    {
        $all = Reader::get_extensions();
        $feed = $all['entry'];
        foreach ($feed as $extension) {
            if (in_array($extension, $all['core'])) {
                continue;
            }
            $class_name = Reader::get_plugin_loader()->get_class_name($extension);
            $this->extensions[$extension] = new $class_name($this->get_element(), $this->entry_key, $this->data['type']);
        }
    }
}