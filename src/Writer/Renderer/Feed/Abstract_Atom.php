<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer\Feed;

use function array_key_exists;
use DateTime;
use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;
use Laminas\Feed\Writer\Version;
use function strtolower;
class Abstract_Atom extends Renderer\Abstract_Renderer
{
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Set feed language
     *
     * @return void
     */
    protected function _set_language(Dom_Document $dom, Dom_Element $root)
    {
        if ($this->get_data_container()->get_language()) {
            $root->set_attribute('xml:lang', $this->get_data_container()->get_language());
        }
    }
    /**
     * Set feed title
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_title(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_title()) {
            $message = 'Atom 1.0 feed elements MUST contain exactly one' . ' atom:title element but a title has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $title = $dom->create_element('title');
        $root->append_child($title);
        $title->set_attribute('type', 'text');
        $text = $dom->create_text_node((string) $this->get_data_container()->get_title());
        $title->append_child($text);
    }
    /**
     * Set feed description
     *
     * @return void
     */
    protected function _set_description(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_description()) {
            return;
        }
        $subtitle = $dom->create_element('subtitle');
        $root->append_child($subtitle);
        $subtitle->set_attribute('type', 'text');
        $text = $dom->create_text_node((string) $this->get_data_container()->get_description());
        $subtitle->append_child($text);
    }
    /**
     * Set date feed was last modified
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_date_modified(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_date_modified()) {
            $message = 'Atom 1.0 feed elements MUST contain exactly one' . ' atom:updated element but a modification date has not been set';
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
     * Set feed generator string
     *
     * @return void
     */
    protected function _set_generator(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_generator()) {
            $this->get_data_container()->set_generator('Laminas_Feed_Writer', Version::VERSION, 'https://getlaminas.org');
        }
        $gdata = $this->get_data_container()->get_generator();
        $generator = $dom->create_element('generator');
        $root->append_child($generator);
        $text = $dom->create_text_node((string) $gdata['name']);
        $generator->append_child($text);
        if (array_key_exists('uri', $gdata)) {
            $generator->set_attribute('uri', $gdata['uri']);
        }
        if (array_key_exists('version', $gdata)) {
            $generator->set_attribute('version', $gdata['version']);
        }
    }
    /**
     * Set link to feed
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
        $link->set_attribute('rel', 'alternate');
        $link->set_attribute('type', 'text/html');
        $link->set_attribute('href', $this->get_data_container()->get_link());
    }
    /**
     * Set feed links
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_feed_links(Dom_Document $dom, Dom_Element $root)
    {
        $flinks = $this->get_data_container()->get_feed_links();
        if (!$flinks || !array_key_exists('atom', $flinks)) {
            $message = 'Atom 1.0 feed elements SHOULD contain one atom:link ' . 'element with a rel attribute value of "self".  This is the ' . 'preferred URI for retrieving Atom Feed Documents representing ' . 'this Atom feed but a feed link has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        foreach ($flinks as $type => $href) {
            $mime = 'application/' . strtolower($type) . '+xml';
            $flink = $dom->create_element('link');
            $root->append_child($flink);
            $flink->set_attribute('rel', 'self');
            $flink->set_attribute('type', $mime);
            $flink->set_attribute('href', $href);
        }
    }
    /**
     * Set feed authors
     *
     * @return void
     */
    protected function _set_authors(Dom_Document $dom, Dom_Element $root)
    {
        $authors = $this->container->get_authors();
        if (!$authors || empty($authors)) {
            /**
             * Technically we should defer an exception until we can check
             * that all entries contain an author. If any entry is missing
             * an author, then a missing feed author element is invalid
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
     * Set feed identifier
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_id(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_id() && !$this->get_data_container()->get_link()) {
            $message = 'Atom 1.0 feed elements MUST contain exactly one ' . 'atom:id element, or as an alternative, we can use the same ' . 'value as atom:link however neither a suitable link nor an ' . 'id have been set';
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
        $id = $dom->create_element('id');
        $root->append_child($id);
        $text = $dom->create_text_node((string) $this->get_data_container()->get_id());
        $id->append_child($text);
    }
    /**
     * Set feed copyright
     *
     * @return void
     */
    protected function _set_copyright(Dom_Document $dom, Dom_Element $root)
    {
        $copyright = $this->get_data_container()->get_copyright();
        if (!$copyright) {
            return;
        }
        $copy = $dom->create_element('rights');
        $root->append_child($copy);
        $text = $dom->create_text_node($copyright);
        $copy->append_child($text);
    }
    /**
     * Set feed level logo (image)
     *
     * @return void
     */
    protected function _set_image(Dom_Document $dom, Dom_Element $root)
    {
        $image = $this->get_data_container()->get_image();
        if (!$image) {
            return;
        }
        $img = $dom->create_element('logo');
        $root->append_child($img);
        $text = $dom->create_text_node((string) $image['uri']);
        $img->append_child($text);
    }
    /**
     * Set date feed was created
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
     * Set base URL to feed links
     *
     * @return void
     */
    protected function _set_base_url(Dom_Document $dom, Dom_Element $root)
    {
        $base_url = $this->get_data_container()->get_base_url();
        if (!$base_url) {
            return;
        }
        $root->set_attribute('xml:base', $base_url);
    }
    /**
     * Set hubs to which this feed pushes
     *
     * @return void
     */
    protected function _set_hubs(Dom_Document $dom, Dom_Element $root)
    {
        $hubs = $this->get_data_container()->get_hubs();
        if (!$hubs) {
            return;
        }
        foreach ($hubs as $hub_url) {
            $hub = $dom->create_element('link');
            $hub->set_attribute('rel', 'hub');
            $hub->set_attribute('href', $hub_url);
            $root->append_child($hub);
        }
    }
    /**
     * Set feed categories
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
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}