<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Feed;

use function array_key_exists;
use function array_unique;
use function count;
use DateTime;
use Dom_Document;
use function is_array;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Collection;
use Laminas\Feed\Reader\Exception;
use function preg_match;
use function strtotime;
use function trim;
/** @template-extends AbstractFeed<Reader\Entry\Rss> */
class Rss extends Abstract_Feed
{
    /**
     * @param null|string $type
     */
    public function __construct(Dom_Document $dom, $type = null)
    {
        parent::__construct($dom, $type);
        $manager = Reader\Reader::get_extension_manager();
        $feed = $manager->get('DublinCore\Feed');
        $feed->set_dom_document($dom);
        $feed->set_type($this->data['type']);
        $feed->set_xpath($this->xpath);
        $this->extensions['DublinCore\Feed'] = $feed;
        $feed = $manager->get('Atom\Feed');
        $feed->set_dom_document($dom);
        $feed->set_type($this->data['type']);
        $feed->set_xpath($this->xpath);
        $this->extensions['Atom\Feed'] = $feed;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $xpath_prefix = '/rss/channel';
        } else {
            $xpath_prefix = '/rdf:RDF/rss:channel';
        }
        foreach ($this->extensions as $extension) {
            $extension->set_xpath_prefix($xpath_prefix);
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
        $authors = [];
        $authors_dc = $this->get_extension('DublinCore')->get_authors();
        if (!empty($authors_dc)) {
            foreach ($authors_dc as $author) {
                $authors[] = ['name' => $author['name']];
            }
        }
        /**
         * Technically RSS doesn't specific author element use at the feed level
         * but it's supported on a "just in case" basis.
         */
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query('//author');
        } else {
            $list = $this->xpath->query('//rss:author');
        }
        if ($list->length) {
            foreach ($list as $author) {
                $string = trim((string) $author->node_value);
                $data = [];
                // Pretty rough parsing - but it's a catchall
                if (preg_match('/^.*@[^ ]*/', $string, $matches)) {
                    $data['email'] = trim($matches[0]);
                    if (preg_match('/\((.*)\)$/', $string, $matches)) {
                        $data['name'] = $matches[1];
                    }
                    $authors[] = $data;
                }
            }
        }
        if (count($authors) === 0) {
            $authors = $this->get_extension('Atom')->get_authors();
        } else {
            $authors = new Reader\Collection\Author(Reader\Reader::array_unique($authors));
        }
        if (count($authors) === 0) {
            $authors = null;
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
        $copyright = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $copyright = $this->xpath->evaluate('string(/rss/channel/copyright)');
        }
        if (!$copyright && $this->get_extension('DublinCore') !== null) {
            $copyright = $this->get_extension('DublinCore')->get_copyright();
        }
        if (empty($copyright)) {
            $copyright = $this->get_extension('Atom')->get_copyright();
        }
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
        return $this->get_date_modified();
    }
    /**
     * Get the feed modification date
     *
     * @return DateTime
     * @throws Exception\RuntimeException
     */
    public function get_date_modified()
    {
        if (array_key_exists('datemodified', $this->data)) {
            return $this->data['datemodified'];
        }
        $date = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $date_modified = $this->xpath->evaluate('string(/rss/channel/pubDate)');
            if (!$date_modified) {
                $date_modified = $this->xpath->evaluate('string(/rss/channel/lastBuildDate)');
            }
            if ($date_modified) {
                $date_modified_parsed = strtotime((string) $date_modified);
                if ($date_modified_parsed) {
                    $date = new DateTime('@' . $date_modified_parsed);
                } else {
                    $date_standards = [DateTime::RSS, DateTime::RFC822, DateTime::RFC2822];
                    foreach ($date_standards as $standard) {
                        $date = DateTime::create_from_format($standard, $date_modified);
                        if ($date instanceof DateTime) {
                            break;
                        }
                    }
                    if (!$date) {
                        throw new Exception\RuntimeException('Could not load date due to unrecognised' . ' format (should follow RFC 822 or 2822).');
                    }
                }
            }
        }
        if (!$date) {
            $date = $this->get_extension('DublinCore')->get_date();
        }
        if (!$date) {
            $date = $this->get_extension('Atom')->get_date_modified();
        }
        if (!$date) {
            $date = null;
        }
        $this->data['datemodified'] = $date;
        return $this->data['datemodified'];
    }
    /**
     * Get the feed lastBuild date
     *
     * @return DateTime
     * @throws Exception\RuntimeException
     */
    public function get_last_build_date()
    {
        if (array_key_exists('lastBuildDate', $this->data)) {
            return $this->data['lastBuildDate'];
        }
        $date = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $last_build_date = $this->xpath->evaluate('string(/rss/channel/lastBuildDate)');
            if ($last_build_date) {
                $last_build_date_parsed = strtotime((string) $last_build_date);
                if ($last_build_date_parsed) {
                    $date = new DateTime('@' . $last_build_date_parsed);
                } else {
                    $date_standards = [DateTime::RSS, DateTime::RFC822, DateTime::RFC2822, null];
                    foreach ($date_standards as $standard) {
                        try {
                            $date = DateTime::create_from_format($standard, $last_build_date_parsed);
                            break;
                        } catch (\Exception $e) {
                            if ($standard === null) {
                                throw new Exception\RuntimeException('Could not load date due to unrecognised format' . ' (should follow RFC 822 or 2822): ' . $e->get_message(), 0, $e);
                            }
                        }
                    }
                }
            }
        }
        if (!$date) {
            $date = null;
        }
        $this->data['lastBuildDate'] = $date;
        return $this->data['lastBuildDate'];
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
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $description = $this->xpath->evaluate('string(/rss/channel/description)');
        } else {
            $description = $this->xpath->evaluate('string(/rdf:RDF/rss:channel/rss:description)');
        }
        if (!$description && $this->get_extension('DublinCore') !== null) {
            $description = $this->get_extension('DublinCore')->get_description();
        }
        if (empty($description)) {
            $description = $this->get_extension('Atom')->get_description();
        }
        if (!$description) {
            $description = null;
        }
        $this->data['description'] = $description;
        return $this->data['description'];
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
        $id = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $id = $this->xpath->evaluate('string(/rss/channel/guid)');
        }
        if (!$id && $this->get_extension('DublinCore') !== null) {
            $id = $this->get_extension('DublinCore')->get_id();
        }
        if (empty($id)) {
            $id = $this->get_extension('Atom')->get_id();
        }
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
     * Get the feed image data
     *
     * @return null|array
     */
    public function get_image()
    {
        if (array_key_exists('image', $this->data)) {
            return $this->data['image'];
        }
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query('/rss/channel/image');
            $prefix = '/rss/channel/image[1]';
        } else {
            $list = $this->xpath->query('/rdf:RDF/rss:channel/rss:image');
            $prefix = '/rdf:RDF/rss:channel/rss:image[1]';
        }
        if ($list->length > 0) {
            $image = [];
            $value = $this->xpath->evaluate('string(' . $prefix . '/url)');
            if ($value) {
                $image['uri'] = $value;
            }
            $value = $this->xpath->evaluate('string(' . $prefix . '/link)');
            if ($value) {
                $image['link'] = $value;
            }
            $value = $this->xpath->evaluate('string(' . $prefix . '/title)');
            if ($value) {
                $image['title'] = $value;
            }
            $value = $this->xpath->evaluate('string(' . $prefix . '/height)');
            if ($value) {
                $image['height'] = $value;
            }
            $value = $this->xpath->evaluate('string(' . $prefix . '/width)');
            if ($value) {
                $image['width'] = $value;
            }
            $value = $this->xpath->evaluate('string(' . $prefix . '/description)');
            if ($value) {
                $image['description'] = $value;
            }
        } else {
            $image = null;
        }
        $this->data['image'] = $image;
        return $this->data['image'];
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
        $language = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $language = $this->xpath->evaluate('string(/rss/channel/language)');
        }
        if (!$language && $this->get_extension('DublinCore') !== null) {
            $language = $this->get_extension('DublinCore')->get_language();
        }
        if (empty($language)) {
            $language = $this->get_extension('Atom')->get_language();
        }
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
     * Get a link to the feed
     *
     * @return null|string
     */
    public function get_link()
    {
        if (array_key_exists('link', $this->data)) {
            return $this->data['link'];
        }
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $link = $this->xpath->evaluate('string(/rss/channel/link)');
        } else {
            $link = $this->xpath->evaluate('string(/rdf:RDF/rss:channel/rss:link)');
        }
        if (empty($link)) {
            $link = $this->get_extension('Atom')->get_link();
        }
        if (!$link) {
            $link = null;
        }
        $this->data['link'] = $link;
        return $this->data['link'];
    }
    /**
     * Get a link to the feed XML
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
     * Get the feed generator entry
     *
     * @return null|string
     */
    public function get_generator()
    {
        if (array_key_exists('generator', $this->data)) {
            return $this->data['generator'];
        }
        $generator = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $generator = $this->xpath->evaluate('string(/rss/channel/generator)');
        }
        if (!$generator) {
            if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
                $generator = $this->xpath->evaluate('string(/rss/channel/atom:generator)');
            } else {
                $generator = $this->xpath->evaluate('string(/rdf:RDF/rss:channel/atom:generator)');
            }
        }
        if (empty($generator)) {
            $generator = $this->get_extension('Atom')->get_generator();
        }
        if (!$generator) {
            $generator = null;
        }
        $this->data['generator'] = $generator;
        return $this->data['generator'];
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
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $title = $this->xpath->evaluate('string(/rss/channel/title)');
        } else {
            $title = $this->xpath->evaluate('string(/rdf:RDF/rss:channel/rss:title)');
        }
        if (!$title && $this->get_extension('DublinCore') !== null) {
            $title = $this->get_extension('DublinCore')->get_title();
        }
        if (!$title) {
            $title = $this->get_extension('Atom')->get_title();
        }
        if (!$title) {
            $title = null;
        }
        $this->data['title'] = $title;
        return $this->data['title'];
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
        $hubs = $this->get_extension('Atom')->get_hubs();
        if (empty($hubs)) {
            $hubs = null;
        } else {
            $hubs = array_unique($hubs);
        }
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
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query('/rss/channel//category');
        } else {
            $list = $this->xpath->query('/rdf:RDF/rss:channel//rss:category');
        }
        if ($list->length) {
            $category_collection = new Collection\Category();
            foreach ($list as $category) {
                $category_collection[] = ['term' => $category->node_value, 'scheme' => $category->get_attribute('domain'), 'label' => $category->node_value];
            }
        } else {
            $category_collection = $this->get_extension('DublinCore')->get_categories();
        }
        if (count($category_collection) === 0) {
            $category_collection = $this->get_extension('Atom')->get_categories();
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
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $entries = $this->xpath->evaluate('//item');
        } else {
            $entries = $this->xpath->evaluate('//rss:item');
        }
        foreach ($entries as $index => $entry) {
            $this->entries[$index] = $entry;
        }
    }
    /**
     * Register the default namespaces for the current feed format
     *
     * @return void
     */
    protected function register_namespaces()
    {
        switch ($this->data['type']) {
            case Reader\Reader::TYPE_RSS_10:
                $this->xpath->register_namespace('rdf', Reader\Reader::NAMESPACE_RDF);
                $this->xpath->register_namespace('rss', Reader\Reader::NAMESPACE_RSS_10);
                break;
            case Reader\Reader::TYPE_RSS_090:
                $this->xpath->register_namespace('rdf', Reader\Reader::NAMESPACE_RDF);
                $this->xpath->register_namespace('rss', Reader\Reader::NAMESPACE_RSS_090);
                break;
        }
    }
}