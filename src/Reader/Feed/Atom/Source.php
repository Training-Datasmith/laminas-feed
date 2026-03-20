<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Feed\Atom;

use Dom_Element;
use Domx_Path;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Feed;
use function rtrim;
class Source extends Feed\Atom
{
    /**
     * Constructor: Create a Source object which is largely just a normal
     * Laminas\Feed\Reader\AbstractFeed object only designed to retrieve feed level
     * metadata from an Atom entry's source element.
     *
     * @param string $xpathPrefix Passed from parent Entry object
     * @param string $type Nearly always Atom 1.0
     */
    public function __construct(Dom_Element $source, $xpath_prefix, $type = Reader\Reader::TYPE_ATOM_10)
    {
        $this->dom_document = $source->owner_document;
        $this->xpath = new Domx_Path($this->dom_document);
        $this->data['type'] = $type;
        $this->register_namespaces();
        $this->load_extensions();
        $manager = Reader\Reader::get_extension_manager();
        $extensions = ['Atom\Feed', 'DublinCore\Feed'];
        foreach ($extensions as $name) {
            $extension = $manager->get($name);
            $extension->set_dom_document($this->dom_document);
            $extension->set_type($this->data['type']);
            $extension->set_xpath($this->xpath);
            $this->extensions[$name] = $extension;
        }
        foreach ($this->extensions as $extension) {
            $extension->set_xpath_prefix(rtrim($xpath_prefix, '/') . '/atom:source');
        }
    }
    /**
     * Since this is not an Entry carrier but a vehicle for Feed metadata, any
     * applicable Entry methods are stubbed out and do nothing.
     */
    /**
     * @return void
     */
    public function count()
    {
    }
    /**
     * @return void
     */
    public function current()
    {
    }
    /**
     * @return void
     */
    public function key()
    {
    }
    /**
     * @return void
     */
    public function next()
    {
    }
    /**
     * @return void
     */
    public function rewind()
    {
    }
    /**
     * @return void
     */
    public function valid()
    {
    }
    /**
     * @return void
     */
    protected function index_entries()
    {
    }
}