<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer\Entry;

use function array_key_exists;
use function ctype_digit;
use DateTime;
use Dom_Document;
use Dom_Element;
use Laminas\Feed\Uri;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;
class Rss extends Renderer\Abstract_Renderer implements Renderer\Renderer_Interface
{
    public function __construct(Writer\Entry $container)
    {
        parent::__construct($container);
    }
    /**
     * Render RSS entry
     *
     * @return $this
     */
    public function render(): static
    {
        $this->dom = new Dom_Document('1.0', $this->container->get_encoding());
        $this->dom->format_output = true;
        $this->dom->substitute_entities = false;
        $entry = $this->dom->create_element('item');
        $this->dom->append_child($entry);
        $this->_set_title($this->dom, $entry);
        $this->_set_description($this->dom, $entry);
        $this->_set_date_created($this->dom, $entry);
        $this->_set_date_modified($this->dom, $entry);
        $this->_set_link($this->dom, $entry);
        $this->_set_id($this->dom, $entry);
        $this->_set_authors($this->dom, $entry);
        $this->_set_enclosure($this->dom, $entry);
        $this->_set_comment_link($this->dom, $entry);
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
        if (!$this->get_data_container()->get_description() && !$this->get_data_container()->get_title()) {
            $message = 'RSS 2.0 entry elements SHOULD contain exactly one' . ' title element but a title has not been set. In addition, there' . ' is no description as required in the absence of a title.';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $title = $dom->create_element('title');
        $root->append_child($title);
        $text = $dom->create_text_node((string) $this->get_data_container()->get_title());
        $title->append_child($text);
    }
    /**
     * Set entry description
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_description(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_description() && !$this->get_data_container()->get_title()) {
            $message = 'RSS 2.0 entry elements SHOULD contain exactly one' . ' description element but a description has not been set. In' . ' addition, there is no title element as required in the absence' . ' of a description.';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        if (!$this->get_data_container()->get_description()) {
            return;
        }
        $subtitle = $dom->create_element('description');
        $root->append_child($subtitle);
        $text = $dom->create_cdata_section($this->get_data_container()->get_description());
        $subtitle->append_child($text);
    }
    /**
     * Set date entry was last modified
     *
     * @return void
     */
    protected function _set_date_modified(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_date_modified()) {
            return;
        }
        $updated = $dom->create_element('pubDate');
        $root->append_child($updated);
        $text = $dom->create_text_node($this->get_data_container()->get_date_modified()->format(DateTime::RSS));
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
        if (!$this->get_data_container()->get_date_modified()) {
            $this->get_data_container()->set_date_modified($this->get_data_container()->get_date_created());
        }
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
            return;
        }
        foreach ($authors as $data) {
            $author = $this->dom->create_element('author');
            $name = $data['name'];
            if (array_key_exists('email', $data)) {
                $name = $data['email'] . ' (' . $data['name'] . ')';
            }
            $text = $dom->create_text_node((string) $name);
            $author->append_child($text);
            $root->append_child($author);
        }
    }
    /**
     * Set entry enclosure
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_enclosure(Dom_Document $dom, Dom_Element $root)
    {
        $data = $this->container->get_enclosure();
        if (!$data || empty($data)) {
            return;
        }
        if (!isset($data['type'])) {
            $exception = new Writer\Exception\InvalidArgumentException('Enclosure "type" is not set');
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        if (!isset($data['length'])) {
            $exception = new Writer\Exception\InvalidArgumentException('Enclosure "length" is not set');
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        if ((int) $data['length'] < 0 || !ctype_digit((string) $data['length'])) {
            $exception = new Writer\Exception\InvalidArgumentException('Enclosure "length" must be an integer indicating the content\'s length in bytes');
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $enclosure = $this->dom->create_element('enclosure');
        $enclosure->set_attribute('type', $data['type']);
        $enclosure->set_attribute('length', (string) $data['length']);
        $enclosure->set_attribute('url', $data['uri']);
        $root->append_child($enclosure);
    }
    /**
     * Set link to entry
     *
     * @return void
     */
    protected function _set_link(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_link()) {
            return;
        }
        $link = $dom->create_element('link');
        $root->append_child($link);
        $text = $dom->create_text_node((string) $this->get_data_container()->get_link());
        $link->append_child($text);
    }
    /**
     * Set entry identifier
     *
     * @return void
     */
    protected function _set_id(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_id() && !$this->get_data_container()->get_link()) {
            return;
        }
        $id = $dom->create_element('guid');
        $root->append_child($id);
        if (!$this->get_data_container()->get_id()) {
            $this->get_data_container()->set_id($this->get_data_container()->get_link());
        }
        $text = $dom->create_text_node((string) $this->get_data_container()->get_id());
        $id->append_child($text);
        $uri = Uri::factory($this->get_data_container()->get_id());
        if (!$uri->is_valid() || !$uri->is_absolute()) {
            /** @see http://www.rssboard.org/rss-profile#element-channel-item-guid */
            $id->set_attribute('isPermaLink', 'false');
        }
    }
    /**
     * Set link to entry comments
     *
     * @return void
     */
    protected function _set_comment_link(Dom_Document $dom, Dom_Element $root)
    {
        $link = $this->get_data_container()->get_comment_link();
        if (!$link) {
            return;
        }
        $clink = $this->dom->create_element('comments');
        $text = $dom->create_text_node((string) $link);
        $clink->append_child($text);
        $root->append_child($clink);
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
            if (isset($cat['scheme'])) {
                $category->set_attribute('domain', $cat['scheme']);
            }
            $text = $dom->create_cdata_section($cat['term']);
            $category->append_child($text);
            $root->append_child($category);
        }
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}