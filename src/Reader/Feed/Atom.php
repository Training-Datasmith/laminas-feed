<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Feed;

use function array_key_exists;
use function count;
use DateTime;
use Dom_Document;
use function is_array;
use Laminas\Feed\Reader;
/** @template-extends AbstractFeed<Reader\Entry\Atom> */
class Atom extends Abstract_Feed
{
    /**
     * @param null|string $type
     */
    public function __construct(Dom_Document $dom, $type = null)
    {
        parent::__construct($dom, $type);
        $manager = Reader\Reader::get_extension_manager();
        $atom_feed = $manager->get('Atom\Feed');
        $atom_feed->set_dom_document($dom);
        $atom_feed->set_type($this->data['type']);
        $atom_feed->set_xpath($this->xpath);
        $this->extensions['Atom\Feed'] = $atom_feed;
        $atom_feed = $manager->get('DublinCore\Feed');
        $atom_feed->set_dom_document($dom);
        $atom_feed->set_type($this->data['type']);
        $atom_feed->set_xpath($this->xpath);
        $this->extensions['DublinCore\Feed'] = $atom_feed;
        foreach ($this->extensions as $extension) {
            $extension->set_xpath_prefix('/atom:feed');
        }
    }
    /**
     * Get a single author
     *
     * @param  int $index
     * @return null|array<string, string>
     */
    public function get_author($index = 0): ?array
    {
        $authors = $this->get_authors();
        return isset($authors[$index]) && is_array($authors[$index]) ? $authors[$index] : null;
    }
    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function get_authors()
    {
        if (array_key_exists('authors', $this->data)) {
            return $this->data['authors'];
        }
        $authors = $this->get_extension('Atom')->get_authors();
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
        $copyright = $this->get_extension('Atom')->get_copyright();
        if (!$copyright) {
            $copyright = null;
        }
        $this->data['copyright'] = $copyright;
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
        $date_created = $this->get_extension('Atom')->get_date_created();
        if (!$date_created) {
            $date_created = null;
        }
        $this->data['datecreated'] = $date_created;
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
        $date_modified = $this->get_extension('Atom')->get_date_modified();
        if (!$date_modified) {
            $date_modified = null;
        }
        $this->data['datemodified'] = $date_modified;
        return $this->data['datemodified'];
    }
    /**
     * Get the feed lastBuild date. This is not implemented in Atom.
     *
     * @return void
     */
    public function get_last_build_date()
    {
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
        $description = $this->get_extension('Atom')->get_description();
        if (!$description) {
            $description = null;
        }
        $this->data['description'] = $description;
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
        $generator = $this->get_extension('Atom')->get_generator();
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
        $id = $this->get_extension('Atom')->get_id();
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
        $language = $this->get_extension('Atom')->get_language();
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
     * Get a link to the source website
     *
     * @return null|string
     */
    public function get_base_url()
    {
        if (array_key_exists('baseUrl', $this->data)) {
            return $this->data['baseUrl'];
        }
        $base_url = $this->get_extension('Atom')->get_base_url();
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
        $link = $this->get_extension('Atom')->get_link();
        $this->data['link'] = $link;
        return $this->data['link'];
    }
    /**
     * Get feed image data
     *
     * @return null|array
     */
    public function get_image()
    {
        if (array_key_exists('image', $this->data)) {
            return $this->data['image'];
        }
        $link = $this->get_extension('Atom')->get_image();
        $this->data['image'] = $link;
        return $this->data['image'];
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
        $link = $this->get_extension('Atom')->get_feed_link();
        if ($link === null || empty($link)) {
            $link = $this->get_original_source_uri();
        }
        $this->data['feedlink'] = $link;
        return $this->data['feedlink'];
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
        $title = $this->get_extension('Atom')->get_title();
        $this->data['title'] = $title;
        return $this->data['title'];
    }
    /**
     * Get an array of any supported PubSubHubbub endpoints
     *
     * @return null|array
     */
    public function get_hubs()
    {
        if (array_key_exists('hubs', $this->data)) {
            return $this->data['hubs'];
        }
        $hubs = $this->get_extension('Atom')->get_hubs();
        $this->data['hubs'] = $hubs;
        return $this->data['hubs'];
    }
    /**
     * Get all categories
     *
     * @return Reader\Collection\Category
     */
    public function get_categories()
    {
        if (array_key_exists('categories', $this->data)) {
            return $this->data['categories'];
        }
        $category_collection = $this->get_extension('Atom')->get_categories();
        if (count($category_collection) === 0) {
            $category_collection = $this->get_extension('DublinCore')->get_categories();
        }
        $this->data['categories'] = $category_collection;
        return $this->data['categories'];
    }
    /**
     * Read all entries to the internal entries array
     *
     * @return void
     */
    protected function index_entries()
    {
        if ($this->get_type() === Reader\Reader::TYPE_ATOM_10 || $this->get_type() === Reader\Reader::TYPE_ATOM_03) {
            $entries = $this->xpath->evaluate('//atom:entry');
            foreach ($entries as $index => $entry) {
                $this->entries[$index] = $entry;
            }
        }
    }
    /**
     * Register the default namespaces for the current feed format
     *
     * @return void
     */
    protected function register_namespaces()
    {
        match ($this->data['type']) {
            Reader\Reader::TYPE_ATOM_03 => $this->xpath->register_namespace('atom', Reader\Reader::NAMESPACE_ATOM_03),
            default => $this->xpath->register_namespace('atom', Reader\Reader::NAMESPACE_ATOM_10),
        };
    }
}