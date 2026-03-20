<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension;

use Dom_Document;
use Dom_Element;
abstract class Abstract_Renderer implements Renderer_Interface
{
    /** @var DOMDocument */
    protected $dom;
    /** @var mixed */
    protected $entry;
    /** @var DOMElement */
    protected $base;
    /** @var mixed */
    protected $container;
    /** @var string */
    protected $type;
    /** @var DOMElement */
    protected $root_element;
    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $encoding = 'UTF-8';
    /**
     * Set the data container
     *
     * @param  mixed $container
     * @return $this
     */
    public function set_data_container($container)
    {
        $this->container = $container;
        return $this;
    }
    /**
     * Set feed encoding
     *
     * @param  string $enc
     * @return $this
     */
    public function set_encoding($enc)
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
     * Set DOMDocument and DOMElement on which to operate
     *
     * @return $this
     */
    public function set_dom_document(Dom_Document $dom, Dom_Element $base)
    {
        $this->dom = $dom;
        $this->base = $base;
        return $this;
    }
    /**
     * Get data container being rendered
     *
     * @return mixed
     */
    public function get_data_container()
    {
        return $this->container;
    }
    /**
     * Set feed type
     *
     * @param  string $type
     * @return $this
     */
    public function set_type($type)
    {
        $this->type = $type;
        return $this;
    }
    /**
     * Get feedtype
     *
     * @return string
     */
    public function get_type()
    {
        return $this->type;
    }
    /**
     * Set root element of document
     *
     * @return $this
     */
    public function set_root_element(Dom_Element $root)
    {
        $this->root_element = $root;
        return $this;
    }
    /**
     * Get root element
     *
     * @return DOMElement
     */
    public function get_root_element()
    {
        return $this->root_element;
    }
    /**
     * Append namespaces to feed
     *
     * @return void
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    abstract protected function _append_namespaces();
}