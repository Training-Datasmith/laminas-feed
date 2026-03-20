<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer\Entry;

use function array_key_exists;
use DateTime;
use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;
class Atom_Deleted extends Renderer\Abstract_Renderer implements Renderer\Renderer_Interface
{
    public function __construct(Writer\Deleted $container)
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
        $entry = $this->dom->create_element('at:deleted-entry');
        $this->dom->append_child($entry);
        $entry->set_attribute('ref', $this->container->get_reference());
        $entry->set_attribute('when', $this->container->get_when()->format(DateTime::ATOM));
        $this->_set_by($this->dom, $entry);
        $this->_set_comment($this->dom, $entry);
        return $this;
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Set tombstone comment
     *
     * @return void
     */
    protected function _set_comment(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_comment()) {
            return;
        }
        $c = $dom->create_element('at:comment');
        $root->append_child($c);
        $c->set_attribute('type', 'html');
        $cdata = $dom->create_cdata_section($this->get_data_container()->get_comment());
        $c->append_child($cdata);
    }
    /**
     * Set entry authors
     *
     * @return void
     */
    protected function _set_by(Dom_Document $dom, Dom_Element $root)
    {
        $data = $this->container->get_by();
        if (!$data || empty($data)) {
            return;
        }
        $author = $this->dom->create_element('at:by');
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
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}