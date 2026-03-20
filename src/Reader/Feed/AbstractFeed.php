<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Feed;

use function array_key_exists;
use function call_user_func_array;
use function count;
use Dom_Document;
use Dom_Element;
// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use Domx_Path;
use function in_array;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Exception;
use function method_exists;
use Return_Type_Will_Change;
use function sprintf;
/**
 * @template TItem of Reader\Entry\Rss|Reader\Entry\Atom
 * @template-implements FeedInterface<TItem>
 */
abstract class Abstract_Feed implements Feed_Interface
{
    /**
     * Parsed feed data
     *
     * @var array
     */
    protected $data = [];
    /**
     * An array of parsed feed entries
     *
     * @var array
     */
    protected $entries = [];
    /**
     * A pointer for the iterator to keep track of the entries array
     *
     * @var int
     */
    protected $entries_key = 0;
    /**
     * The base XPath query used to retrieve feed data
     */
    protected \Domx_Path $xpath;
    /**
     * Array of loaded extensions
     *
     * @var array
     */
    protected $extensions = [];
    /**
     * Original Source URI (set if imported from a URI)
     *
     * @var string
     */
    protected $original_source_uri;
    /**
     * @param DOMDocument $domDocument The DOM object for the feed's XML
     * @param null|string $type Feed type
     */
    public function __construct(protected \Dom_Document $dom_document, $type = null)
    {
        $this->xpath = new Domx_Path($this->dom_document);
        if ($type !== null) {
            $this->data['type'] = $type;
        } else {
            $this->data['type'] = Reader\Reader::detect_type($this->dom_document);
        }
        $this->register_namespaces();
        $this->index_entries();
        $this->load_extensions();
    }
    /**
     * Set an original source URI for the feed being parsed. This value
     * is returned from getFeedLink() method if the feed does not carry
     * a self-referencing URI.
     *
     * @param string $uri
     */
    public function set_original_source_uri($uri): void
    {
        $this->original_source_uri = $uri;
    }
    /**
     * Get an original source URI for the feed being parsed. Returns null if
     * unset or the feed was not imported from a URI.
     *
     * @return null|string
     */
    public function get_original_source_uri()
    {
        return $this->original_source_uri;
    }
    /**
     * Get the number of feed entries.
     * Required by the Iterator interface.
     *
     * @return int
     */
    #[Return_Type_Will_Change]
    public function count()
    {
        return count($this->entries);
    }
    /**
     * Return the current entry
     *
     * @return Reader\Entry\EntryInterface
     */
    #[Return_Type_Will_Change]
    public function current()
    {
        if (str_starts_with($this->get_type(), 'rss')) {
            $reader = new Reader\Entry\Rss($this->entries[$this->key()], $this->key(), $this->get_type());
        } else {
            $reader = new Reader\Entry\Atom($this->entries[$this->key()], $this->key(), $this->get_type());
        }
        $reader->set_xpath($this->xpath);
        return $reader;
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
        $assumed = $this->get_dom_document()->encoding;
        if (empty($assumed)) {
            return 'UTF-8';
        }
        return $assumed;
    }
    /**
     * Get feed as xml
     *
     * @return string
     */
    public function save_xml()
    {
        return $this->get_dom_document()->save_xml();
    }
    /**
     * Get the DOMElement representing the items/feed element
     *
     * @return DOMElement
     */
    public function get_element()
    {
        return $this->get_dom_document()->document_element;
    }
    /**
     * Get the DOMXPath object for this feed
     *
     * @return DOMXPath
     */
    public function get_xpath()
    {
        return $this->xpath;
    }
    /**
     * Get the feed type
     *
     * @return string
     */
    public function get_type()
    {
        return $this->data['type'];
    }
    /**
     * Return the current feed key
     *
     * @return int
     */
    #[Return_Type_Will_Change]
    public function key()
    {
        return $this->entries_key;
    }
    /**
     * Move the feed pointer forward
     */
    #[Return_Type_Will_Change]
    public function next(): void
    {
        ++$this->entries_key;
    }
    /**
     * Reset the pointer in the feed object
     */
    #[Return_Type_Will_Change]
    public function rewind(): void
    {
        $this->entries_key = 0;
    }
    /**
     * Check to see if the iterator is still valid
     *
     * @return bool
     */
    #[Return_Type_Will_Change]
    public function valid()
    {
        return 0 <= $this->entries_key && $this->entries_key < $this->count();
    }
    /** @return array */
    public function get_extensions()
    {
        return $this->extensions;
    }
    /**
     * @param mixed[] $args
     * @return mixed
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
     * Return an Extension object with the matching name (postfixed with _Feed)
     *
     * @return null|Reader\Extension\AbstractFeed
     */
    public function get_extension(string $name)
    {
        if (array_key_exists($name . '\Feed', $this->extensions)) {
            return $this->extensions[$name . '\Feed'];
        }
        return null;
    }
    protected function load_extensions()
    {
        $all = Reader\Reader::get_extensions();
        $manager = Reader\Reader::get_extension_manager();
        $feed = $all['feed'];
        foreach ($feed as $extension) {
            if (in_array($extension, $all['core'])) {
                continue;
            }
            if (!$manager->has($extension)) {
                throw new Exception\RuntimeException(sprintf('Unable to load extension "%s"; cannot find class', $extension));
            }
            $plugin = $manager->get($extension);
            $plugin->set_dom_document($this->get_dom_document());
            $plugin->set_type($this->data['type']);
            $plugin->set_xpath($this->xpath);
            $this->extensions[$extension] = $plugin;
        }
    }
    /**
     * Read all entries to the internal entries array
     */
    abstract protected function index_entries();
    /**
     * Register the default namespaces for the current feed format
     */
    abstract protected function register_namespaces();
}