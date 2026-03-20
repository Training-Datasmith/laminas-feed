<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Atom;

use function array_key_exists;
use function count;
use DateTime;
use Dom_Element;
use function is_string;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Collection;
use Laminas\Feed\Reader\Extension;
use Laminas\Feed\Uri;
use function strlen;
class Feed extends Extension\Abstract_Feed
{
    /**
     * Get a single author
     *
     * @param  int $index
     */
    public function get_author($index = 0): ?string
    {
        $authors = $this->get_authors();
        if (isset($authors[$index]) && is_string($authors[$index])) {
            return $authors[$index];
        }
        return null;
    }
    /**
     * Get an array with feed authors
     *
     * @return Collection\Author
     */
    public function get_authors()
    {
        if (array_key_exists('authors', $this->data)) {
            return $this->data['authors'];
        }
        $list = $this->xpath->query('//atom:author');
        $authors = [];
        if ($list->length) {
            foreach ($list as $author) {
                $author = $this->get_author_from_element($author);
                if (!empty($author)) {
                    $authors[] = $author;
                }
            }
        }
        if (count($authors) === 0) {
            $authors = new Collection\Author();
        } else {
            $authors = new Collection\Author(Reader\Reader::array_unique($authors));
        }
        $this->data['authors'] = $authors;
        return $this->data['authors'];
    }
    /**
     * Get the copyright entry
     *
     * @return null|string
     */
    public function get_copyright()
    {
        if (array_key_exists('copyright', $this->data)) {
            return $this->data['copyright'];
        }
        $copyright = $this->get_type() === Reader\Reader::TYPE_ATOM_03 ? $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:copyright)') : $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:rights)');
        $this->data['copyright'] = is_string($copyright) ? $copyright : null;
        return $this->data['copyright'];
    }
    /**
     * Get the feed creation date
     *
     * @return null|DateTime
     */
    public function get_date_created()
    {
        if (array_key_exists('datecreated', $this->data)) {
            return $this->data['datecreated'];
        }
        $date = null;
        if ($this->get_type() === Reader\Reader::TYPE_ATOM_03) {
            $date_created = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:created)');
        } else {
            $date_created = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:published)');
        }
        if ($date_created) {
            $date = new DateTime($date_created);
        }
        $this->data['datecreated'] = $date;
        return $this->data['datecreated'];
    }
    /**
     * Get the feed modification date
     *
     * @return null|DateTime
     */
    public function get_date_modified()
    {
        if (array_key_exists('datemodified', $this->data)) {
            return $this->data['datemodified'];
        }
        $date = null;
        if ($this->get_type() === Reader\Reader::TYPE_ATOM_03) {
            $date_modified = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:modified)');
        } else {
            $date_modified = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:updated)');
        }
        if ($date_modified) {
            $date = new DateTime($date_modified);
        }
        $this->data['datemodified'] = $date;
        return $this->data['datemodified'];
    }
    /**
     * Get the feed description
     *
     * @return null|string
     */
    public function get_description()
    {
        if (array_key_exists('description', $this->data)) {
            return $this->data['description'];
        }
        $description = $this->get_type() === Reader\Reader::TYPE_ATOM_03 ? $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:tagline)') : $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:subtitle)');
        $this->data['description'] = is_string($description) ? $description : null;
        return $this->data['description'];
    }
    /**
     * Get the feed generator entry
     *
     * @return null|string
     */
    public function get_generator()
    {
        if (array_key_exists('generator', $this->data)) {
            return $this->data['generator'];
        }
        // TODO: Add uri support
        $generator = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:generator)');
        if (!$generator) {
            $generator = null;
        }
        $this->data['generator'] = $generator;
        return $this->data['generator'];
    }
    /**
     * Get the feed ID
     *
     * @return null|string
     */
    public function get_id()
    {
        if (array_key_exists('id', $this->data)) {
            return $this->data['id'];
        }
        $id = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:id)');
        if (!$id) {
            if ($this->get_link()) {
                $id = $this->get_link();
            } elseif ($this->get_title()) {
                $id = $this->get_title();
            } else {
                $id = null;
            }
        }
        $this->data['id'] = $id;
        return $this->data['id'];
    }
    /**
     * Get the feed language
     *
     * @return null|string
     */
    public function get_language()
    {
        if (array_key_exists('language', $this->data)) {
            return $this->data['language'];
        }
        $language = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:lang)');
        if (!$language) {
            $language = $this->xpath->evaluate('string(//@xml:lang[1])');
        }
        if (!$language) {
            $language = null;
        }
        $this->data['language'] = $language;
        return $this->data['language'];
    }
    /**
     * Get the feed image
     *
     * @return null|array
     */
    public function get_image()
    {
        if (array_key_exists('image', $this->data)) {
            return $this->data['image'];
        }
        $image_url = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:logo)');
        if (!$image_url) {
            $image = null;
        } else {
            $image = ['uri' => $image_url];
        }
        $this->data['image'] = $image;
        return $this->data['image'];
    }
    /**
     * Get the base URI of the feed (if set).
     *
     * @return null|string
     */
    public function get_base_url()
    {
        if (array_key_exists('baseUrl', $this->data)) {
            return $this->data['baseUrl'];
        }
        $base_url = $this->xpath->evaluate('string(//@xml:base[1])');
        if (!$base_url) {
            $base_url = null;
        }
        $this->data['baseUrl'] = $base_url;
        return $this->data['baseUrl'];
    }
    /**
     * Get a link to the source website
     *
     * @return null|string
     */
    public function get_link()
    {
        if (array_key_exists('link', $this->data)) {
            return $this->data['link'];
        }
        $link = null;
        $list = $this->xpath->query($this->get_xpath_prefix() . '/atom:link[@rel="alternate"]/@href|' . $this->get_xpath_prefix() . '/atom:link[not(@rel)]/@href');
        if ($list->length) {
            $link = $list->item(0)->node_value;
            $link = $this->absolutise_uri($link);
        }
        $this->data['link'] = $link;
        return $this->data['link'];
    }
    /**
     * Get a link to the feed's XML Url
     *
     * @return null|string
     */
    public function get_feed_link()
    {
        if (array_key_exists('feedlink', $this->data)) {
            return $this->data['feedlink'];
        }
        $link = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:link[@rel="self"]/@href)');
        $link = $this->absolutise_uri($link);
        $this->data['feedlink'] = $link;
        return $this->data['feedlink'];
    }
    /**
     * Get an array of any supported Pusubhubbub endpoints
     *
     * @return null|array
     */
    public function get_hubs()
    {
        if (array_key_exists('hubs', $this->data)) {
            return $this->data['hubs'];
        }
        $hubs = [];
        $list = $this->xpath->query($this->get_xpath_prefix() . '//atom:link[@rel="hub"]/@href');
        if ($list->length) {
            foreach ($list as $uri) {
                $hubs[] = $this->absolutise_uri($uri->node_value);
            }
        } else {
            $hubs = null;
        }
        $this->data['hubs'] = $hubs;
        return $this->data['hubs'];
    }
    /**
     * Get the feed title
     *
     * @return null|string
     */
    public function get_title()
    {
        if (array_key_exists('title', $this->data)) {
            return $this->data['title'];
        }
        $title = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/atom:title)');
        if (empty($title)) {
            $title = null;
        }
        $this->data['title'] = $title;
        return $this->data['title'];
    }
    /**
     * Get all categories
     *
     * @return Collection\Category
     */
    public function get_categories()
    {
        if (array_key_exists('categories', $this->data)) {
            return $this->data['categories'];
        }
        if ($this->get_type() === Reader\Reader::TYPE_ATOM_10) {
            $list = $this->xpath->query($this->get_xpath_prefix() . '/atom:category');
        } else {
            /**
             * Since Atom 0.3 did not support categories, it would have used the
             * Dublin Core extension. However there is a small possibility Atom 0.3
             * may have been retrofittied to use Atom 1.0 instead.
             */
            $this->xpath->register_namespace('atom10', Reader\Reader::NAMESPACE_ATOM_10);
            $list = $this->xpath->query($this->get_xpath_prefix() . '/atom10:category');
        }
        if ($list->length) {
            $category_collection = new Collection\Category();
            foreach ($list as $category) {
                $category_collection[] = ['term' => $category->get_attribute('term'), 'scheme' => $category->get_attribute('scheme'), 'label' => $category->get_attribute('label')];
            }
        } else {
            return new Collection\Category();
        }
        $this->data['categories'] = $category_collection;
        return $this->data['categories'];
    }
    /**
     * Get an author entry in RSS format
     *
     * @return array<string,null|string>|null
     * @psalm-return array{email?: null|string, name?: null|string, uri?: null|string}|null
     */
    protected function get_author_from_element(Dom_Element $element)
    {
        $author = [];
        $email_node = $element->get_elements_by_tag_name('email');
        $name_node = $element->get_elements_by_tag_name('name');
        $uri_node = $element->get_elements_by_tag_name('uri');
        if ($email_node->length && strlen((string) $email_node->item(0)->node_value) > 0) {
            $author['email'] = $email_node->item(0)->node_value;
        }
        if ($name_node->length && strlen((string) $name_node->item(0)->node_value) > 0) {
            $author['name'] = $name_node->item(0)->node_value;
        }
        if ($uri_node->length && strlen((string) $uri_node->item(0)->node_value) > 0) {
            $author['uri'] = $uri_node->item(0)->node_value;
        }
        if (empty($author)) {
            return;
        }
        return $author;
    }
    /**
     * Attempt to absolutise the URI, i.e. if a relative URI apply the
     *  xml:base value as a prefix to turn into an absolute URI.
     *
     * @param  string $link
     * @return null|string
     */
    protected function absolutise_uri($link)
    {
        if (!Uri::factory($link)->is_absolute()) {
            if ($this->get_base_url() !== null) {
                $link = $this->get_base_url() . $link;
                if (!Uri::factory($link)->is_valid()) {
                    $link = null;
                }
            }
        }
        return $link;
    }
    /**
     * Register the default namespaces for the current feed format
     *
     * @return void
     */
    protected function register_namespaces()
    {
        if ($this->get_type() === Reader\Reader::TYPE_ATOM_10 || $this->get_type() === Reader\Reader::TYPE_ATOM_03) {
            return;
            // pre-registered at Feed level
        }
        $atom_detected = $this->get_atom_type();
        match ($atom_detected) {
            Reader\Reader::TYPE_ATOM_03 => $this->xpath->register_namespace('atom', Reader\Reader::NAMESPACE_ATOM_03),
            default => $this->xpath->register_namespace('atom', Reader\Reader::NAMESPACE_ATOM_10),
        };
    }
    /**
     * Detect the presence of any Atom namespaces in use
     *
     * @return null|string
     */
    protected function get_atom_type()
    {
        $dom = $this->get_dom_document();
        $prefix_atom03 = $dom->lookup_prefix(Reader\Reader::NAMESPACE_ATOM_03);
        $prefix_atom10 = $dom->lookup_prefix(Reader\Reader::NAMESPACE_ATOM_10);
        if ($dom->is_default_namespace(Reader\Reader::NAMESPACE_ATOM_10) || !empty($prefix_atom10)) {
            return Reader\Reader::TYPE_ATOM_10;
        }
        if ($dom->is_default_namespace(Reader\Reader::NAMESPACE_ATOM_03) || !empty($prefix_atom03)) {
            return Reader\Reader::TYPE_ATOM_03;
        }
    }
}