<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Entry;

use function array_key_exists;
use function count;
use DateTime;
use Dom_Element;
use Domx_Path;
use function is_array;
use function is_string;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Collection\Author as AuthorCollection;
use Laminas\Feed\Reader\Exception\RuntimeException;
use Laminas\Feed\Reader\Extension\Atom\Entry;
use Laminas\Feed\Reader\Extension\Dublin_Core\Entry as DublinCoreEntry;
use Laminas\Feed\Reader\Extension\Thread\Entry as ThreadEntry;
class Atom extends Abstract_Entry implements Entry_Interface
{
    /**
     * XPath query
     */
    protected string $xpath_query;
    /**
     * @param int $entryKey
     * @param null|string $type
     */
    public function __construct(Dom_Element $entry, $entry_key, $type = null)
    {
        parent::__construct($entry, $entry_key, $type);
        // Everyone by now should know XPath indices start from 1 not 0
        $this->xpath_query = '//atom:entry[' . ($this->entry_key + 1) . ']';
        $manager = Reader\Reader::get_extension_manager();
        $extensions = ['Atom\Entry', 'Thread\Entry', 'DublinCore\Entry'];
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
     * @return AuthorCollection
     */
    public function get_authors()
    {
        if (array_key_exists('authors', $this->data)) {
            return $this->data['authors'];
        }
        $people = $this->get_atom_extension()->get_authors();
        $this->data['authors'] = $people;
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
        $content = $this->get_atom_extension()->get_content();
        $this->data['content'] = $content;
        return $this->data['content'];
    }
    /**
     * Get the entry creation date
     *
     * @return null|DateTime
     */
    public function get_date_created()
    {
        if (array_key_exists('datecreated', $this->data)) {
            return $this->data['datecreated'];
        }
        $date_created = $this->get_atom_extension()->get_date_created();
        $this->data['datecreated'] = $date_created;
        return $this->data['datecreated'];
    }
    /**
     * Get the entry modification date
     *
     * @return null|DateTime
     */
    public function get_date_modified()
    {
        if (array_key_exists('datemodified', $this->data)) {
            return $this->data['datemodified'];
        }
        $date_modified = $this->get_atom_extension()->get_date_modified();
        $this->data['datemodified'] = $date_modified;
        return $this->data['datemodified'];
    }
    /**
     * Get the entry description
     *
     * @return string
     */
    public function get_description()
    {
        if (array_key_exists('description', $this->data)) {
            return $this->data['description'];
        }
        $description = $this->get_atom_extension()->get_description();
        $this->data['description'] = $description;
        return $this->data['description'];
    }
    /**
     * Get the entry enclosure
     *
     * @return null|object{href: string, length: int, type: string}
     */
    public function get_enclosure()
    {
        if (array_key_exists('enclosure', $this->data)) {
            return $this->data['enclosure'];
        }
        $enclosure = $this->get_atom_extension()->get_enclosure();
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
        $id = $this->get_atom_extension()->get_id();
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
        $links = $this->get_atom_extension()->get_links();
        $this->data['links'] = $links;
        return $this->data['links'];
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
        $title = $this->get_atom_extension()->get_title();
        $this->data['title'] = $title;
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
        $commentcount = $this->get_thread_extension()->get_comment_count();
        if (!$commentcount) {
            $commentcount = $this->get_atom_extension()->get_comment_count();
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
        $commentlink = $this->get_atom_extension()->get_comment_link();
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
        $commentfeedlink = $this->get_atom_extension()->get_comment_feed_link();
        $this->data['commentfeedlink'] = $commentfeedlink;
        return $this->data['commentfeedlink'];
    }
    /**
     * Get category data as a Reader\Reader_Collection_Category object
     *
     * @return Reader\Collection\Category
     */
    public function get_categories()
    {
        if (array_key_exists('categories', $this->data)) {
            return $this->data['categories'];
        }
        $category_collection = $this->get_atom_extension()->get_categories();
        if (count($category_collection) === 0) {
            $category_collection = $this->get_dublin_core_extension()->get_categories();
        }
        $this->data['categories'] = $category_collection;
        return $this->data['categories'];
    }
    /**
     * Get source feed metadata from the entry
     *
     * @return null|Reader\Feed\Atom\Source
     */
    public function get_source()
    {
        if (array_key_exists('source', $this->data)) {
            return $this->data['source'];
        }
        $source = $this->get_atom_extension()->get_source();
        $this->data['source'] = $source;
        return $this->data['source'];
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
    private function get_atom_extension(): Entry
    {
        $extension = $this->get_extension('Atom');
        if (!$extension instanceof Entry) {
            throw new RuntimeException('Unable to retrieve Atom entry extension');
        }
        return $extension;
    }
    private function get_dublin_core_extension(): Dublin_Core_Entry
    {
        $extension = $this->get_extension('DublinCore');
        if (!$extension instanceof Dublin_Core_Entry) {
            throw new RuntimeException('Unable to retrieve DublinCore entry extension');
        }
        return $extension;
    }
    private function get_thread_extension(): Thread_Entry
    {
        $extension = $this->get_extension('Thread');
        if (!$extension instanceof Thread_Entry) {
            throw new RuntimeException('Unable to retrieve Thread entry extension');
        }
        return $extension;
    }
}