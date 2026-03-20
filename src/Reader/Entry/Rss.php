<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Entry;

use function array_key_exists;
use function count;
use function date_create_from_format;
use DateTime;
use DateTimeInterface;
use Dom_Element;
use Dom_Node_List;
use Domx_Path;
use function in_array;
use function is_array;
use function is_string;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Exception;
use Laminas\Feed\Reader\Exception\RuntimeException;
use Laminas\Feed\Reader\Extension\Atom\Entry as AtomEntry;
use Laminas\Feed\Reader\Extension\Content\Entry as ContentEntry;
use Laminas\Feed\Reader\Extension\Dublin_Core\Entry as DublinCoreEntry;
use Laminas\Feed\Reader\Extension\Slash\Entry as SlashEntry;
use Laminas\Feed\Reader\Extension\Thread\Entry as ThreadEntry;
use Laminas\Feed\Reader\Extension\Well_Formed_Web\Entry as WellFormedWebEntry;
use function preg_match;
use stdClass;
use function strtotime;
use function trim;
class Rss extends Abstract_Entry implements Entry_Interface
{
    /**
     * XPath query for RDF
     */
    protected string $xpath_query_rdf;
    /**
     * XPath query for RSS
     */
    protected string $xpath_query_rss;
    /**
     * @param string $entryKey
     * @param null|string $type
     */
    public function __construct(Dom_Element $entry, $entry_key, $type = null)
    {
        parent::__construct($entry, $entry_key, $type);
        $this->xpath_query_rss = '//item[' . ($this->entry_key + 1) . ']';
        $this->xpath_query_rdf = '//rss:item[' . ($this->entry_key + 1) . ']';
        $manager = Reader\Reader::get_extension_manager();
        $extensions = ['DublinCore\Entry', 'Content\Entry', 'Atom\Entry', 'WellFormedWeb\Entry', 'Slash\Entry', 'Thread\Entry'];
        foreach ($extensions as $name) {
            $extension = $manager->get($name);
            $extension->set_entry_element($entry);
            $extension->set_entry_key($entry_key);
            $extension->set_type($type);
            $this->extensions[$name] = $extension;
        }
    }
    /**
     * @inheritDoc
     * @param int $index
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
     * @return null|array
     */
    public function get_authors()
    {
        if (array_key_exists('authors', $this->data)) {
            return $this->data['authors'];
        }
        $authors = [];
        $authors_dc = $this->get_dublin_core_extension()->get_authors();
        if (!empty($authors_dc)) {
            foreach ($authors_dc as $author) {
                $authors[] = ['name' => $author['name']];
            }
        }
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query($this->xpath_query_rss . '//author');
        } else {
            $list = $this->xpath->query($this->xpath_query_rdf . '//rss:author');
        }
        if ($list instanceof Dom_Node_List && $list->length) {
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
            $authors = $this->get_atom_extension()->get_authors();
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
     * Get the entry content
     *
     * @return string
     */
    public function get_content()
    {
        if (array_key_exists('content', $this->data)) {
            return $this->data['content'];
        }
        $content = $this->get_content_extension()->get_content();
        if (empty($content)) {
            $content = $this->get_description();
        }
        if (empty($content)) {
            $content = $this->get_atom_extension()->get_content();
        }
        $this->data['content'] = $content;
        return $this->data['content'];
    }
    /**
     * Get the entry's date of creation
     *
     * @return DateTime
     */
    public function get_date_created()
    {
        return $this->get_date_modified();
    }
    /**
     * Get the entry's date of modification
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
            $date_modified = $this->xpath->evaluate('string(' . $this->xpath_query_rss . '/pubDate)');
            if ($date_modified) {
                $date_modified_parsed = strtotime((string) $date_modified);
                if ($date_modified_parsed) {
                    $date = new DateTime('@' . $date_modified_parsed);
                } else {
                    $date_standards = [DateTime::RSS, DateTime::RFC822, DateTime::RFC2822, null];
                    foreach ($date_standards as $standard) {
                        try {
                            $date = date_create_from_format($standard, $date_modified);
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
        if (!$date instanceof DateTimeInterface) {
            $date = $this->get_dublin_core_extension()->get_date();
        }
        if (!$date instanceof DateTimeInterface) {
            $date = $this->get_atom_extension()->get_date_modified();
        }
        if (!$date instanceof DateTimeInterface) {
            $date = null;
        }
        $this->data['datemodified'] = $date;
        return $this->data['datemodified'];
    }
    /**
     * Get the entry description
     *
     * @return null|string
     */
    public function get_description()
    {
        if (array_key_exists('description', $this->data)) {
            return $this->data['description'];
        }
        $rss_types = [Reader\Reader::TYPE_RSS_090, Reader\Reader::TYPE_RSS_10];
        $description = in_array($this->get_type(), $rss_types, true) ? $this->xpath->evaluate('string(' . $this->xpath_query_rdf . '/rss:description)') : $this->xpath->evaluate('string(' . $this->xpath_query_rss . '/description)');
        if (!$description) {
            $description = $this->get_dublin_core_extension()->get_description();
        }
        if (empty($description)) {
            $description = $this->get_atom_extension()->get_description();
        }
        $this->data['description'] = is_string($description) ? $description : null;
        return $this->data['description'];
    }
    /**
     * Get the entry enclosure
     *
     * @return null|object{url?: string, href?: string, length: int, type: string}
     */
    public function get_enclosure()
    {
        if (array_key_exists('enclosure', $this->data)) {
            return $this->data['enclosure'];
        }
        $enclosure = null;
        if ($this->get_type() === Reader\Reader::TYPE_RSS_20) {
            $node_list = $this->xpath->query($this->xpath_query_rss . '/enclosure');
            if ($node_list instanceof Dom_Node_List && $node_list->length > 0) {
                /** @var DOMElement $node */
                $node = $node_list->item(0);
                $enclosure = new stdClass();
                $enclosure->url = $node->get_attribute('url');
                $enclosure->length = $node->get_attribute('length');
                $enclosure->type = $node->get_attribute('type');
            }
        }
        if (!$enclosure) {
            $enclosure = $this->get_atom_extension()->get_enclosure();
        }
        $this->data['enclosure'] = $enclosure;
        return $this->data['enclosure'];
    }
    /**
     * Get the entry ID
     *
     * @return string
     */
    public function get_id()
    {
        if (array_key_exists('id', $this->data)) {
            return $this->data['id'];
        }
        $id = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $id = $this->xpath->evaluate('string(' . $this->xpath_query_rss . '/guid)');
        }
        if (!$id) {
            $id = $this->get_dublin_core_extension()->get_id();
        }
        if (empty($id)) {
            $id = $this->get_atom_extension()->get_id();
        }
        if (!$id) {
            if ($this->get_permalink()) {
                $id = $this->get_permalink();
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
     * Get a specific link
     *
     * @param  int $index
     */
    public function get_link($index = 0): ?string
    {
        if (!array_key_exists('links', $this->data)) {
            $this->get_links();
        }
        return isset($this->data['links'][$index]) && is_string($this->data['links'][$index]) ? $this->data['links'][$index] : null;
    }
    /**
     * Get all links
     *
     * @return array
     */
    public function get_links()
    {
        if (array_key_exists('links', $this->data)) {
            return $this->data['links'];
        }
        $links = [];
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query($this->xpath_query_rss . '//link');
        } else {
            $list = $this->xpath->query($this->xpath_query_rdf . '//rss:link');
        }
        if ($list instanceof Dom_Node_List && $list->length) {
            foreach ($list as $link) {
                $links[] = $link->node_value;
            }
        } else {
            $links = $this->get_atom_extension()->get_links();
        }
        $this->data['links'] = $links;
        return $this->data['links'];
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
            $list = $this->xpath->query($this->xpath_query_rss . '//category');
        } else {
            $list = $this->xpath->query($this->xpath_query_rdf . '//rss:category');
        }
        if ($list instanceof Dom_Node_List && $list->length) {
            $category_collection = new Reader\Collection\Category();
            foreach ($list as $category) {
                $category_collection[] = ['term' => $category->node_value, 'scheme' => $category->get_attribute('domain'), 'label' => $category->node_value];
            }
        } else {
            $category_collection = $this->get_dublin_core_extension()->get_categories();
        }
        if (count($category_collection) === 0) {
            $category_collection = $this->get_atom_extension()->get_categories();
        }
        $this->data['categories'] = $category_collection;
        return $this->data['categories'];
    }
    /**
     * Get a permalink to the entry
     *
     * @return string
     */
    public function get_permalink()
    {
        return $this->get_link(0);
    }
    /**
     * Get the entry title
     *
     * @return string
     */
    public function get_title()
    {
        if (array_key_exists('title', $this->data)) {
            return $this->data['title'];
        }
        $title = $this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090 ? $this->xpath->evaluate('string(' . $this->xpath_query_rss . '/title)') : $this->xpath->evaluate('string(' . $this->xpath_query_rdf . '/rss:title)');
        if (!$title) {
            $title = $this->get_dublin_core_extension()->get_title();
        }
        if (!$title) {
            $title = $this->get_atom_extension()->get_title();
        }
        $this->data['title'] = is_string($title) ? $title : '';
        return $this->data['title'];
    }
    /**
     * Get the number of comments/replies for current entry
     *
     * @return int
     */
    public function get_comment_count()
    {
        if (array_key_exists('commentcount', $this->data)) {
            return $this->data['commentcount'];
        }
        $commentcount = $this->get_slash_extension()->get_comment_count();
        if (!$commentcount) {
            $commentcount = $this->get_thread_extension()->get_comment_count();
        }
        if (!$commentcount) {
            $commentcount = $this->get_atom_extension()->get_comment_count();
        }
        if (!$commentcount) {
            $commentcount = 0;
        }
        $this->data['commentcount'] = $commentcount;
        return $this->data['commentcount'];
    }
    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function get_comment_link()
    {
        if (array_key_exists('commentlink', $this->data)) {
            return $this->data['commentlink'];
        }
        $commentlink = null;
        if ($this->get_type() !== Reader\Reader::TYPE_RSS_10 && $this->get_type() !== Reader\Reader::TYPE_RSS_090) {
            $commentlink = $this->xpath->evaluate('string(' . $this->xpath_query_rss . '/comments)');
        }
        if (!$commentlink) {
            $commentlink = $this->get_atom_extension()->get_comment_link();
        }
        if (!$commentlink) {
            $commentlink = null;
        }
        $this->data['commentlink'] = $commentlink;
        return $this->data['commentlink'];
    }
    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function get_comment_feed_link()
    {
        if (array_key_exists('commentfeedlink', $this->data)) {
            return $this->data['commentfeedlink'];
        }
        $commentfeedlink = $this->get_well_formed_web_extension()->get_comment_feed_link();
        if (!$commentfeedlink) {
            $commentfeedlink = $this->get_atom_extension()->get_comment_feed_link('rss');
        }
        if (!$commentfeedlink) {
            $commentfeedlink = $this->get_atom_extension()->get_comment_feed_link('rdf');
        }
        if (!$commentfeedlink) {
            $commentfeedlink = null;
        }
        $this->data['commentfeedlink'] = $commentfeedlink;
        return $this->data['commentfeedlink'];
    }
    /**
     * Set the XPath query (incl. on all Extensions)
     */
    public function set_xpath(Domx_Path $xpath): void
    {
        parent::set_xpath($xpath);
        foreach ($this->extensions as $extension) {
            $extension->set_xpath($this->xpath);
        }
    }
    private function get_atom_extension(): Atom_Entry
    {
        $extension = $this->get_extension('Atom');
        if (!$extension instanceof Atom_Entry) {
            throw new RuntimeException('Unable to load Atom entry extension');
        }
        return $extension;
    }
    private function get_content_extension(): Content_Entry
    {
        $extension = $this->get_extension('Content');
        if (!$extension instanceof Content_Entry) {
            throw new RuntimeException('Unable to load Content entry extension');
        }
        return $extension;
    }
    private function get_dublin_core_extension(): Dublin_Core_Entry
    {
        $extension = $this->get_extension('DublinCore');
        if (!$extension instanceof Dublin_Core_Entry) {
            throw new RuntimeException('Unable to load DublinCore entry extension');
        }
        return $extension;
    }
    private function get_slash_extension(): Slash_Entry
    {
        $extension = $this->get_extension('Slash');
        if (!$extension instanceof Slash_Entry) {
            throw new RuntimeException('Unable to load Slash entry extension');
        }
        return $extension;
    }
    private function get_thread_extension(): Thread_Entry
    {
        $extension = $this->get_extension('Thread');
        if (!$extension instanceof Thread_Entry) {
            throw new RuntimeException('Unable to load Thread entry extension');
        }
        return $extension;
    }
    private function get_well_formed_web_extension(): Well_Formed_Web_Entry
    {
        $extension = $this->get_extension('WellFormedWeb');
        if (!$extension instanceof Well_Formed_Web_Entry) {
            throw new RuntimeException('Unable to load WellFormedWeb entry extension');
        }
        return $extension;
    }
}