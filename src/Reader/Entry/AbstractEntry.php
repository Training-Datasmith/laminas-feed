<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Entry;

use function call_user_func_array;
use Dom_Document;
use Dom_Element;
use Domx_Path;
use function in_array;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Exception;
use function method_exists;
use function sprintf;
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
        } elseif ($this->dom_document !== null) {
            $this->data['type'] = Reader\Reader::detect_type($this->dom_document);
        } else {
            $this->data['type'] = Reader\Reader::TYPE_ANY;
        }
        $this->load_extensions();
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
     * @return null|Reader\Extension\AbstractEntry
     */
    public function get_extension(string $name)
    {
        $extension_class = $name . '\Entry';
        return isset($this->extensions[$extension_class]) && $this->extensions[$extension_class] instanceof Reader\Extension\Abstract_Entry ? $this->extensions[$extension_class] : null;
    }
    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  array $args
     * @return mixed
     * @throws Exception\RuntimeException If no extensions implements the method.
     */
    public function __call(string $method, array $args)
    {
        foreach ($this->extensions as $extension) {
            if (method_exists($extension, $method)) {
                return call_user_func_array([$extension, $method], $args);
            }
        }
        throw new Exception\RuntimeException(sprintf('Method: %s does not exist and could not be located on a registered Extension', $method));
    }
    /**
     * Load extensions from Laminas\Feed\Reader\Reader
     *
     * @return void
     */
    protected function load_extensions()
    {
        $all = Reader\Reader::get_extensions();
        $manager = Reader\Reader::get_extension_manager();
        $feed = $all['entry'];
        foreach ($feed as $extension) {
            if (in_array($extension, $all['core'])) {
                continue;
            }
            $plugin = $manager->get($extension);
            $plugin->set_entry_element($this->get_element());
            $plugin->set_entry_key($this->entry_key);
            $plugin->set_type($this->data['type']);
            $this->extensions[$extension] = $plugin;
        }
    }
}