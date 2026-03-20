<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer\Entry;

use function array_key_exists;
use function class_exists;
use function date;
use DateTime;
use Dom_Document;
use Dom_Element;
use Laminas\Feed\Uri;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;
use Laminas\Validator;
use function preg_match;
use function preg_replace;
use function str_replace;
use function strlen;
use function strtotime;
use tidy;
class Atom extends Renderer\Abstract_Renderer implements Renderer\Renderer_Interface
{
    public function __construct(Writer\Entry $container)
    {
        parent::__construct($container);
    }
    /**
     * Render atom entry
     *
     * @return $this
     */
    public function render(): static
    {
        $this->dom = new Dom_Document('1.0', $this->container->get_encoding());
        $this->dom->format_output = true;
        $entry = $this->dom->create_element_ns(Writer\Writer::NAMESPACE_ATOM_10, 'entry');
        $this->dom->append_child($entry);
        $this->_set_source($this->dom, $entry);
        $this->_set_title($this->dom, $entry);
        $this->_set_description($this->dom, $entry);
        $this->_set_date_created($this->dom, $entry);
        $this->_set_date_modified($this->dom, $entry);
        $this->_set_link($this->dom, $entry);
        $this->_set_id($this->dom, $entry);
        $this->_set_authors($this->dom, $entry);
        $this->_set_enclosure($this->dom, $entry);
        $this->_set_content($this->dom, $entry);
        $this->_set_categories($this->dom, $entry);
        foreach ($this->extensions as $ext) {
            $ext->set_type($this->get_type());
            $ext->set_root_element($this->get_root_element());
            $ext->set_dom_document($this->get_dom_document(), $entry);
            $ext->render();
        }
        return $this;
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Set entry title
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_title(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_title()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one' . ' atom:title element but a title has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $title = $dom->create_element('title');
        $root->append_child($title);
        $title->set_attribute('type', 'html');
        $cdata = $dom->create_cdata_section($this->get_data_container()->get_title());
        $title->append_child($cdata);
    }
    /**
     * Set entry description
     *
     * @return void
     */
    protected function _set_description(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_description()) {
            return;
            // unless src content or base64
        }
        $subtitle = $dom->create_element('summary');
        $root->append_child($subtitle);
        $subtitle->set_attribute('type', 'html');
        $cdata = $dom->create_cdata_section($this->get_data_container()->get_description());
        $subtitle->append_child($cdata);
    }
    /**
     * Set date entry was modified
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_date_modified(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_date_modified()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one' . ' atom:updated element but a modification date has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $updated = $dom->create_element('updated');
        $root->append_child($updated);
        $text = $dom->create_text_node($this->get_data_container()->get_date_modified()->format(DateTime::ATOM));
        $updated->append_child($text);
    }
    /**
     * Set date entry was created
     *
     * @return void
     */
    protected function _set_date_created(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_date_created()) {
            return;
        }
        $el = $dom->create_element('published');
        $root->append_child($el);
        $text = $dom->create_text_node($this->get_data_container()->get_date_created()->format(DateTime::ATOM));
        $el->append_child($text);
    }
    /**
     * Set entry authors
     *
     * @return void
     */
    protected function _set_authors(Dom_Document $dom, Dom_Element $root)
    {
        $authors = $this->container->get_authors();
        if (!$authors || empty($authors)) {
            /**
             * This will actually trigger an Exception at the feed level if
             * a feed level author is not set.
             */
            return;
        }
        foreach ($authors as $data) {
            $author = $this->dom->create_element('author');
            $name = $this->dom->create_element('name');
            $author->append_child($name);
            $root->append_child($author);
            $text = $dom->create_text_node((string) $data['name']);
            $name->append_child($text);
            if (array_key_exists('email', $data)) {
                $email = $this->dom->create_element('email');
                $author->append_child($email);
                $text = $dom->create_text_node((string) $data['email']);
                $email->append_child($text);
            }
            if (array_key_exists('uri', $data)) {
                $uri = $this->dom->create_element('uri');
                $author->append_child($uri);
                $text = $dom->create_text_node((string) $data['uri']);
                $uri->append_child($text);
            }
        }
    }
    /**
     * Set entry enclosure
     *
     * @return void
     */
    protected function _set_enclosure(Dom_Document $dom, Dom_Element $root)
    {
        $data = $this->container->get_enclosure();
        if (!$data || empty($data)) {
            return;
        }
        $enclosure = $this->dom->create_element('link');
        $enclosure->set_attribute('rel', 'enclosure');
        if (isset($data['type'])) {
            $enclosure->set_attribute('type', $data['type']);
        }
        if (isset($data['length'])) {
            $enclosure->set_attribute('length', $data['length']);
        }
        $enclosure->set_attribute('href', $data['uri']);
        $root->append_child($enclosure);
    }
    /**
     * @return void
     */
    protected function _set_link(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_link()) {
            return;
        }
        $link = $dom->create_element('link');
        $root->append_child($link);
        $link->set_attribute('rel', 'alternate');
        $link->set_attribute('type', 'text/html');
        $link->set_attribute('href', $this->get_data_container()->get_link());
    }
    /**
     * Set entry identifier
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_id(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_id() && !$this->get_data_container()->get_link()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one ' . 'atom:id element, or as an alternative, we can use the same ' . 'value as atom:link however neither a suitable link nor an ' . 'id have been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        if (!$this->get_data_container()->get_id()) {
            $this->get_data_container()->set_id($this->get_data_container()->get_link());
        }
        if (!Uri::factory($this->get_data_container()->get_id())->is_valid() && !preg_match("#^urn:[a-zA-Z0-9][a-zA-Z0-9\\-]{1,31}:([a-zA-Z0-9\\(\\)\\+\\,\\.\\:\\=\\@\\;\$\\_\\!\\*\\-]|%[0-9a-fA-F]{2})*#", (string) $this->get_data_container()->get_id()) && !$this->_validate_tag_uri($this->get_data_container()->get_id())) {
            throw new Writer\Exception\InvalidArgumentException('Atom 1.0 IDs must be a valid URI/IRI');
        }
        $id = $dom->create_element('id');
        $root->append_child($id);
        $text = $dom->create_text_node((string) $this->get_data_container()->get_id());
        $id->append_child($text);
    }
    /**
     * Validate a URI using the tag scheme (RFC 4151)
     *
     * @param  string $id
     * @return bool
     */
    protected function _validate_tag_uri($id)
    {
        if (preg_match('/^tag:(?P<name>.*),(?P<date>\d{4}-?\d{0,2}-?\d{0,2}):(?P<specific>.*)(.*:)*$/', $id, $matches)) {
            $dvalid = false;
            $date = $matches['date'];
            $d6 = strtotime($date);
            if (strlen($date) === 4 && $date <= date('Y')) {
                $dvalid = true;
            } elseif (strlen($date) === 7 && $d6 < strtotime('now')) {
                $dvalid = true;
            } elseif (strlen($date) === 10 && $d6 < strtotime('now')) {
                $dvalid = true;
            }
            $validator = new Validator\Email_Address();
            if ($validator->is_valid($matches['name'])) {
                $nvalid = true;
            } else {
                $nvalid = $validator->is_valid('info@' . $matches['name']);
            }
            return $dvalid && $nvalid;
        }
        return false;
    }
    /**
     * Set entry content
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_content(Dom_Document $dom, Dom_Element $root)
    {
        $content = $this->get_data_container()->get_content();
        if (!$content && !$this->get_data_container()->get_link()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one ' . 'atom:content element, or as an alternative, at least one link ' . 'with a rel attribute of "alternate" to indicate an alternate ' . 'method to consume the content.';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        if (!$content) {
            return;
        }
        $element = $dom->create_element('content');
        $element->set_attribute('type', 'xhtml');
        $xhtml_element = $this->_load_xhtml($content);
        $xhtml = $dom->import_node($xhtml_element, true);
        $element->append_child($xhtml);
        $root->append_child($element);
    }
    /**
     * Load a HTML string and attempt to normalise to XML
     *
     * @param string $content
     * @return DOMElement
     */
    protected function _load_xhtml($content): ?\Dom_Element
    {
        if (class_exists(tidy::class, false)) {
            $tidy = new tidy();
            $config = ['output-xhtml' => true, 'show-body-only' => true, 'quote-nbsp' => false];
            $encoding = str_replace('-', '', $this->get_encoding());
            $tidy->parse_string($content, $config, $encoding);
            $tidy->clean_repair();
            $xhtml = (string) $tidy;
        } else {
            $xhtml = $content;
        }
        $xhtml = preg_replace(['/(<[\/]?)([a-zA-Z]+)/'], '$1xhtml:$2', (string) $xhtml);
        $dom = new Dom_Document('1.0', $this->get_encoding());
        $dom->load_xml('<xhtml:div xmlns:xhtml="http://www.w3.org/1999/xhtml">' . $xhtml . '</xhtml:div>');
        return $dom->document_element;
    }
    /**
     * Set entry categories
     *
     * @return void
     */
    protected function _set_categories(Dom_Document $dom, Dom_Element $root)
    {
        $categories = $this->get_data_container()->get_categories();
        if (!$categories) {
            return;
        }
        foreach ($categories as $cat) {
            $category = $dom->create_element('category');
            $category->set_attribute('term', $cat['term']);
            if (isset($cat['label'])) {
                $category->set_attribute('label', $cat['label']);
            } else {
                $category->set_attribute('label', $cat['term']);
            }
            if (isset($cat['scheme'])) {
                $category->set_attribute('scheme', $cat['scheme']);
            }
            $root->append_child($category);
        }
    }
    /**
     * Append Source element (Atom 1.0 Feed Metadata)
     *
     * @return void
     */
    protected function _set_source(Dom_Document $dom, Dom_Element $root)
    {
        $source = $this->get_data_container()->get_source();
        if (!$source) {
            return;
        }
        $renderer = new Renderer\Feed\Atom_Source($source);
        $renderer->set_type($this->get_type());
        $element = $renderer->render()->get_element();
        $imported = $dom->import_node($element, true);
        $root->append_child($imported);
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}