<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer;

use Dom_Document;
use Dom_Element;
use function is_bool;
use Laminas\Feed\Writer;
use function stripos;
class Abstract_Renderer
{
    /**
     * Extensions
     *
     * @var array
     */
    protected $extensions = [];
    /** @var DOMDocument */
    protected $dom;
    /** @var bool */
    protected $ignore_exceptions = false;
    /** @var array */
    protected $exceptions = [];
    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $encoding = 'UTF-8';
    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $type;
    /** @var DOMElement */
    protected $root_element;
    /**
     * @param Writer\AbstractFeed $container
     */
    public function __construct(protected $container)
    {
        $this->set_type($this->container->get_type());
        $this->_load_extensions();
    }
    /**
     * Save XML to string
     *
     * @return string
     */
    public function save_xml(): string|false
    {
        return $this->get_dom_document()->save_xml();
    }
    /**
     * Get DOM document
     *
     * @return DOMDocument
     */
    public function get_dom_document()
    {
        return $this->dom;
    }
    /**
     * Get document element from DOM
     *
     * @return DOMElement
     */
    public function get_element()
    {
        return $this->get_dom_document()->document_element;
    }
    /**
     * Get data container of items being rendered
     *
     * @return Writer\AbstractFeed
     */
    public function get_data_container()
    {
        return $this->container;
    }
    /**
     * Set feed encoding
     *
     * @param  string $enc
     * @return $this
     */
    public function set_encoding($enc): static
    {
        $this->encoding = $enc;
        return $this;
    }
    /**
     * Get feed encoding
     *
     * @return string
     */
    public function get_encoding()
    {
        return $this->encoding;
    }
    /**
     * Indicate whether or not to ignore exceptions
     *
     * @param  bool $bool
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function ignore_exceptions($bool = true): static
    {
        if (!is_bool($bool)) {
            throw new Writer\Exception\InvalidArgumentException('Invalid parameter: $bool. Should be TRUE or FALSE (defaults to TRUE if null)');
        }
        $this->ignore_exceptions = $bool;
        return $this;
    }
    /**
     * Get exception list
     *
     * @return array
     */
    public function get_exceptions()
    {
        return $this->exceptions;
    }
    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function set_type($type): void
    {
        $this->type = $type;
    }
    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function get_type()
    {
        return $this->type;
    }
    /**
     * Sets the absolute root element for the XML feed being generated. This
     * helps simplify the appending of namespace declarations, but also ensures
     * namespaces are added to the root element - not scattered across the entire
     * XML file - may assist namespace unsafe parsers and looks pretty ;).
     */
    public function set_root_element(Dom_Element $root): void
    {
        $this->root_element = $root;
    }
    /**
     * Retrieve the absolute root element for the XML feed being generated.
     *
     * @return DOMElement
     */
    public function get_root_element()
    {
        return $this->root_element;
    }
    /**
     * Load extensions from Laminas\Feed\Writer\Writer
     *
     * @return void
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _load_extensions()
    {
        Writer\Writer::register_core_extensions();
        $manager = Writer\Writer::get_extension_manager();
        $all = Writer\Writer::get_extensions();
        $exts = stripos(static::class, 'entry') ? $all['entryRenderer'] : $all['feedRenderer'];
        foreach ($exts as $extension) {
            $plugin = $manager->get($extension);
            $plugin->set_data_container($this->get_data_container());
            $plugin->set_encoding($this->get_encoding());
            $this->extensions[$extension] = $plugin;
        }
    }
}