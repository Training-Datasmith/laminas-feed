<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Renderer\Feed;

use function array_key_exists;
use function ctype_digit;
use DateTime;
use Dom_Document;
use Dom_Element;
use function is_string;
use Laminas\Feed\Uri;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Renderer;
use Laminas\Feed\Writer\Version;
class Rss extends Renderer\Abstract_Renderer implements Renderer\Renderer_Interface
{
    public function __construct(Writer\Feed $container)
    {
        parent::__construct($container);
    }
    /**
     * Render RSS feed
     *
     * @return $this
     */
    public function render(): static
    {
        $this->dom = new Dom_Document('1.0', $this->container->get_encoding());
        $this->dom->format_output = true;
        $this->dom->substitute_entities = false;
        $rss = $this->dom->create_element('rss');
        $this->set_root_element($rss);
        $rss->set_attribute('version', '2.0');
        $channel = $this->dom->create_element('channel');
        $rss->append_child($channel);
        $this->dom->append_child($rss);
        $this->_set_language($this->dom, $channel);
        $this->_set_base_url($this->dom, $channel);
        $this->_set_title($this->dom, $channel);
        $this->_set_description($this->dom, $channel);
        $this->_set_image($this->dom, $channel);
        $this->_set_date_created($this->dom, $channel);
        $this->_set_date_modified($this->dom, $channel);
        $this->_set_last_build_date($this->dom, $channel);
        $this->_set_generator($this->dom, $channel);
        $this->_set_link($this->dom, $channel);
        $this->_set_authors($this->dom, $channel);
        $this->_set_copyright($this->dom, $channel);
        $this->_set_categories($this->dom, $channel);
        foreach ($this->extensions as $ext) {
            $ext->set_type($this->get_type());
            $ext->set_root_element($this->get_root_element());
            $ext->set_dom_document($this->get_dom_document(), $channel);
            $ext->render();
        }
        foreach ($this->container as $entry) {
            if ($this->get_data_container()->get_encoding()) {
                $entry->set_encoding($this->get_data_container()->get_encoding());
            }
            if ($entry instanceof Writer\Entry) {
                $renderer = new Renderer\Entry\Rss($entry);
            } else {
                continue;
            }
            if ($this->ignore_exceptions === true) {
                $renderer->ignore_exceptions();
            }
            $renderer->set_type($this->get_type());
            $renderer->set_root_element($this->dom->document_element);
            $renderer->render();
            $element = $renderer->get_element();
            $imported = $this->dom->import_node($element, true);
            $channel->append_child($imported);
        }
        return $this;
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Set feed language
     *
     * @return void
     */
    protected function _set_language(Dom_Document $dom, Dom_Element $root)
    {
        $lang = $this->get_data_container()->get_language();
        if (!$lang) {
            return;
        }
        $language = $dom->create_element('language');
        $root->append_child($language);
        $language->node_value = $lang;
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
            $message = 'RSS 2.0 feed elements MUST contain exactly one' . ' title element but a title has not been set';
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
     * Set feed description
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_description(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_description()) {
            $message = 'RSS 2.0 feed elements MUST contain exactly one' . ' description element but one has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $subtitle = $dom->create_element('description');
        $root->append_child($subtitle);
        $text = $dom->create_text_node((string) $this->get_data_container()->get_description());
        $subtitle->append_child($text);
    }
    /**
     * Set date feed was last modified
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
        $name = $gdata['name'];
        if (array_key_exists('version', $gdata)) {
            $name .= ' ' . $gdata['version'];
        }
        if (array_key_exists('uri', $gdata)) {
            $name .= ' (' . $gdata['uri'] . ')';
        }
        $text = $dom->create_text_node($name);
        $generator->append_child($text);
    }
    /**
     * Set link to feed
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_link(Dom_Document $dom, Dom_Element $root)
    {
        $value = $this->get_data_container()->get_link();
        if (!$value) {
            $message = 'RSS 2.0 feed elements MUST contain exactly one' . ' link element but one has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $link = $dom->create_element('link');
        $root->append_child($link);
        $text = $dom->create_text_node($value);
        $link->append_child($text);
        if (!Uri::factory($value)->is_valid()) {
            $link->set_attribute('isPermaLink', 'false');
        }
    }
    /**
     * Set feed authors
     *
     * @return void
     */
    protected function _set_authors(Dom_Document $dom, Dom_Element $root)
    {
        $authors = $this->get_data_container()->get_authors();
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
        $copy = $dom->create_element('copyright');
        $root->append_child($copy);
        $text = $dom->create_text_node($copyright);
        $copy->append_child($text);
    }
    /**
     * Set feed channel image
     *
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _set_image(Dom_Document $dom, Dom_Element $root)
    {
        $image = $this->get_data_container()->get_image();
        if (!$image) {
            return;
        }
        if (!isset($image['title']) || empty($image['title']) || !is_string($image['title'])) {
            $message = 'RSS 2.0 feed images must include a title';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        if (empty($image['link']) || !is_string($image['link']) || !Uri::factory($image['link'])->is_valid()) {
            $message = 'Invalid parameter: parameter \'link\'' . ' must be a non-empty string and valid URI/IRI';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignore_exceptions) {
                throw $exception;
            }
            $this->exceptions[] = $exception;
            return;
        }
        $img = $dom->create_element('image');
        $root->append_child($img);
        $url = $dom->create_element('url');
        $text = $dom->create_text_node((string) $image['uri']);
        $url->append_child($text);
        $title = $dom->create_element('title');
        $text = $dom->create_text_node($image['title']);
        $title->append_child($text);
        $link = $dom->create_element('link');
        $text = $dom->create_text_node($image['link']);
        $link->append_child($text);
        $img->append_child($url);
        $img->append_child($title);
        $img->append_child($link);
        if (isset($image['height'])) {
            if (!ctype_digit((string) $image['height']) || $image['height'] > 400) {
                $message = 'Invalid parameter: parameter \'height\'' . ' must be an integer not exceeding 400';
                $exception = new Writer\Exception\InvalidArgumentException($message);
                if (!$this->ignore_exceptions) {
                    throw $exception;
                }
                $this->exceptions[] = $exception;
                return;
            }
            $height = $dom->create_element('height');
            $text = $dom->create_text_node((string) $image['height']);
            $height->append_child($text);
            $img->append_child($height);
        }
        if (isset($image['width'])) {
            if (!ctype_digit((string) $image['width']) || $image['width'] > 144) {
                $message = 'Invalid parameter: parameter \'width\'' . ' must be an integer not exceeding 144';
                $exception = new Writer\Exception\InvalidArgumentException($message);
                if (!$this->ignore_exceptions) {
                    throw $exception;
                }
                $this->exceptions[] = $exception;
                return;
            }
            $width = $dom->create_element('width');
            $text = $dom->create_text_node((string) $image['width']);
            $width->append_child($text);
            $img->append_child($width);
        }
        if (isset($image['description'])) {
            if (empty($image['description']) || !is_string($image['description'])) {
                $message = 'Invalid parameter: parameter \'description\'' . ' must be a non-empty string';
                $exception = new Writer\Exception\InvalidArgumentException($message);
                if (!$this->ignore_exceptions) {
                    throw $exception;
                }
                $this->exceptions[] = $exception;
                return;
            }
            $desc = $dom->create_element('description');
            $text = $dom->create_text_node($image['description']);
            $desc->append_child($text);
            $img->append_child($desc);
        }
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
     * Set date feed last build date
     *
     * @return void
     */
    protected function _set_last_build_date(Dom_Document $dom, Dom_Element $root)
    {
        if (!$this->get_data_container()->get_last_build_date()) {
            return;
        }
        $last_build_date = $dom->create_element('lastBuildDate');
        $root->append_child($last_build_date);
        $text = $dom->create_text_node($this->get_data_container()->get_last_build_date()->format(DateTime::RSS));
        $last_build_date->append_child($text);
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
            if (isset($cat['scheme'])) {
                $category->set_attribute('domain', $cat['scheme']);
            }
            $text = $dom->create_text_node((string) $cat['term']);
            $category->append_child($text);
            $root->append_child($category);
        }
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}