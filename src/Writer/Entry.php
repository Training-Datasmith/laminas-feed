<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function array_key_exists;
use BadMethodCallException;
use DateTime;
use DateTimeInterface;
use function gettype;
use function in_array;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use Laminas\Feed\Uri;
use function sprintf;
class Entry
{
    /**
     * Internal array containing all data associated with this entry or item.
     *
     * @var array
     */
    protected $data = [];
    /**
     * Registered extensions
     *
     * @var array
     */
    protected $extensions = [];
    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $type;
    /**
     * Constructor: Primarily triggers the registration of core extensions and
     * loads those appropriate to this data container.
     */
    public function __construct()
    {
        Writer::register_core_extensions();
        $this->_load_extensions();
    }
    /**
     * Set a single author
     *
     * The following option keys are supported:
     * 'name'  => (string) The name
     * 'email' => (string) An optional email
     * 'uri'   => (string) An optional and valid URI
     *
     * @return $this
     * @throws Exception\InvalidArgumentException If any value of $author not follow the format.
     */
    public function add_author(array $author): static
    {
        // Check array values
        if (!array_key_exists('name', $author) || empty($author['name']) || !is_string($author['name'])) {
            throw new Exception\InvalidArgumentException('Invalid parameter: author array must include a "name" key with a non-empty string value');
        }
        if (isset($author['email'])) {
            if (empty($author['email']) || !is_string($author['email'])) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "email" array value must be a non-empty string');
            }
        }
        if (isset($author['uri'])) {
            if (empty($author['uri']) || !is_string($author['uri']) || !Uri::factory($author['uri'])->is_valid()) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI');
            }
        }
        $this->data['authors'][] = $author;
        return $this;
    }
    /**
     * Set an array with feed authors
     *
     * @see addAuthor
     *
     * @return $this
     */
    public function add_authors(array $authors): static
    {
        foreach ($authors as $author) {
            $this->add_author($author);
        }
        return $this;
    }
    /**
     * Set the feed character encoding
     *
     * @param  string $encoding
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_encoding($encoding): static
    {
        if (empty($encoding) || !is_string($encoding)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['encoding'] = $encoding;
        return $this;
    }
    /**
     * Get the feed character encoding
     *
     * @return null|string
     */
    public function get_encoding()
    {
        if (!array_key_exists('encoding', $this->data)) {
            return 'UTF-8';
        }
        return $this->data['encoding'];
    }
    /**
     * Set the copyright entry
     *
     * @param  string $copyright
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_copyright($copyright): static
    {
        if (empty($copyright) || !is_string($copyright)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['copyright'] = $copyright;
        return $this;
    }
    /**
     * Set the entry's content
     *
     * @param  string $content
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_content($content): static
    {
        if (empty($content) || !is_string($content)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['content'] = $content;
        return $this;
    }
    /**
     * Set the feed creation date
     *
     * @param  null|int|DateTimeInterface $date
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_date_created($date = null): static
    {
        if ($date === null) {
            $date = new DateTime();
        }
        if (is_int($date)) {
            $date = new DateTime('@' . $date);
        }
        if (!$date instanceof DateTimeInterface) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp passed as parameter');
        }
        $this->data['dateCreated'] = $date;
        return $this;
    }
    /**
     * Set the feed modification date
     *
     * @param  null|int|DateTimeInterface $date
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_date_modified($date = null): static
    {
        if ($date === null) {
            $date = new DateTime();
        }
        if (is_int($date)) {
            $date = new DateTime('@' . $date);
        }
        if (!$date instanceof DateTimeInterface) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp passed as parameter');
        }
        $this->data['dateModified'] = $date;
        return $this;
    }
    /**
     * Set the feed description
     *
     * @param  string $description
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_description($description): static
    {
        if (empty($description) || !is_string($description)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['description'] = $description;
        return $this;
    }
    /**
     * Set the feed ID
     *
     * @param  string $id
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_id($id): static
    {
        if (empty($id) || !is_string($id)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['id'] = $id;
        return $this;
    }
    /**
     * Set a link to the HTML source of this entry
     *
     * @param  string $link
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_link($link): static
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->data['link'] = $link;
        return $this;
    }
    /**
     * Set the number of comments associated with this entry
     *
     * @param  int|float|string $count
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_comment_count($count): static
    {
        // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedNotEqualOperator
        if (!is_numeric($count) || (int) $count != $count || (int) $count < 0) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "count" must be a positive integer number or zero');
        }
        $this->data['commentCount'] = (int) $count;
        return $this;
    }
    /**
     * Set a link to a HTML page containing comments associated with this entry
     *
     * @param  string $link
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_comment_link($link): static
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        $this->data['commentLink'] = $link;
        return $this;
    }
    /**
     * Set a link to an XML feed for any comments associated with this entry
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_comment_feed_link(array $link): static
    {
        if (!isset($link['uri']) || !is_string($link['uri']) || !Uri::factory($link['uri'])->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        if (!isset($link['type']) || !in_array($link['type'], ['atom', 'rss', 'rdf'])) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "type" must be one of "atom", "rss" or "rdf"');
        }
        if (!isset($this->data['commentFeedLinks'])) {
            $this->data['commentFeedLinks'] = [];
        }
        $this->data['commentFeedLinks'][] = $link;
        return $this;
    }
    /**
     * Set a links to an XML feed for any comments associated with this entry.
     * Each link is an array with keys "uri" and "type", where type is one of:
     * "atom", "rss" or "rdf".
     *
     * @return $this
     */
    public function set_comment_feed_links(array $links): static
    {
        foreach ($links as $link) {
            $this->set_comment_feed_link($link);
        }
        return $this;
    }
    /**
     * Set the feed title
     *
     * @param  string $title
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_title($title): static
    {
        if (empty($title) && !is_numeric($title) || !is_string($title)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['title'] = $title;
        return $this;
    }
    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function get_authors()
    {
        if (!array_key_exists('authors', $this->data)) {
            return;
        }
        return $this->data['authors'];
    }
    /**
     * Get the entry content
     *
     * @return string
     */
    public function get_content()
    {
        if (!array_key_exists('content', $this->data)) {
            return;
        }
        return $this->data['content'];
    }
    /**
     * Get the entry copyright information
     *
     * @return string
     */
    public function get_copyright()
    {
        if (!array_key_exists('copyright', $this->data)) {
            return;
        }
        return $this->data['copyright'];
    }
    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function get_date_created()
    {
        if (!array_key_exists('dateCreated', $this->data)) {
            return;
        }
        return $this->data['dateCreated'];
    }
    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function get_date_modified()
    {
        if (!array_key_exists('dateModified', $this->data)) {
            return;
        }
        return $this->data['dateModified'];
    }
    /**
     * Get the entry description
     *
     * @return string
     */
    public function get_description()
    {
        if (!array_key_exists('description', $this->data)) {
            return;
        }
        return $this->data['description'];
    }
    /**
     * Get the entry ID
     *
     * @return string
     */
    public function get_id()
    {
        if (!array_key_exists('id', $this->data)) {
            return;
        }
        return $this->data['id'];
    }
    /**
     * Get a link to the HTML source
     *
     * @return null|string
     */
    public function get_link()
    {
        if (!array_key_exists('link', $this->data)) {
            return;
        }
        return $this->data['link'];
    }
    /**
     * Get all links
     *
     * @return array
     */
    public function get_links()
    {
        if (!array_key_exists('links', $this->data)) {
            return;
        }
        return $this->data['links'];
    }
    /**
     * Get the entry title
     *
     * @return string
     */
    public function get_title()
    {
        if (!array_key_exists('title', $this->data)) {
            return;
        }
        return $this->data['title'];
    }
    /**
     * Get the number of comments/replies for current entry
     *
     * @return int
     */
    public function get_comment_count()
    {
        if (!array_key_exists('commentCount', $this->data)) {
            return;
        }
        return $this->data['commentCount'];
    }
    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function get_comment_link()
    {
        if (!array_key_exists('commentLink', $this->data)) {
            return;
        }
        return $this->data['commentLink'];
    }
    /**
     * Returns an array of URIs pointing to a feed of all comments for this entry
     * where the array keys indicate the feed type (atom, rss or rdf).
     *
     * @return string
     */
    public function get_comment_feed_links()
    {
        if (!array_key_exists('commentFeedLinks', $this->data)) {
            return;
        }
        return $this->data['commentFeedLinks'];
    }
    /**
     * Add an entry category
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function add_category(array $category): static
    {
        if (!isset($category['term'])) {
            throw new Exception\InvalidArgumentException('Each category must be an array and contain at least a "term" element' . ' containing the machine readable category name');
        }
        if (isset($category['scheme'])) {
            if (empty($category['scheme']) || !is_string($category['scheme']) || !Uri::factory($category['scheme'])->is_valid()) {
                throw new Exception\InvalidArgumentException('The Atom scheme or RSS domain of a category must be a valid URI');
            }
        }
        if (!isset($this->data['categories'])) {
            $this->data['categories'] = [];
        }
        $this->data['categories'][] = $category;
        return $this;
    }
    /**
     * Set an array of entry categories
     *
     * @return $this
     */
    public function add_categories(array $categories): static
    {
        foreach ($categories as $category) {
            $this->add_category($category);
        }
        return $this;
    }
    /**
     * Get the entry categories
     *
     * @return null|string
     */
    public function get_categories()
    {
        if (!array_key_exists('categories', $this->data)) {
            return;
        }
        return $this->data['categories'];
    }
    /**
     * Adds an enclosure to the entry. The array parameter may contain the
     * keys 'uri', 'type' and 'length'. Only 'uri' is required for Atom, though the
     * others must also be provided or RSS rendering (where they are required)
     * will throw an Exception.
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_enclosure(array $enclosure): static
    {
        if (!isset($enclosure['uri'])) {
            throw new Exception\InvalidArgumentException('Enclosure "uri" is not set');
        }
        if (!Uri::factory($enclosure['uri'])->is_valid()) {
            throw new Exception\InvalidArgumentException('Enclosure "uri" is not a valid URI/IRI');
        }
        $this->data['enclosure'] = $enclosure;
        return $this;
    }
    /**
     * Retrieve an array of all enclosures to be added to entry.
     *
     * @return array
     */
    public function get_enclosure()
    {
        if (!array_key_exists('enclosure', $this->data)) {
            return;
        }
        return $this->data['enclosure'];
    }
    /**
     * Unset a specific data point
     *
     * @param  string $name
     * @return $this
     */
    public function remove($name): static
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
        return $this;
    }
    /**
     * Get registered extensions
     *
     * @return array
     */
    public function get_extensions()
    {
        return $this->extensions;
    }
    /**
     * Return an Extension object with the matching name (postfixed with _Entry)
     */
    public function get_extension(string $name): ?object
    {
        $extension_class_name = $name . '\Entry';
        if (!isset($this->extensions[$extension_class_name])) {
            return null;
        }
        if (!is_object($this->extensions[$extension_class_name])) {
            throw new Exception\RuntimeException(sprintf('Extension is of invalid type; expected object, received "%s"', gettype($this->extensions[$extension_class_name])));
        }
        return $this->extensions[$extension_class_name];
    }
    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param  string $type
     * @return $this
     */
    public function set_type($type): static
    {
        $this->type = $type;
        return $this;
    }
    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function get_type()
    {
        return $this->type;
    }
    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException If no extensions implements the method.
     */
    public function __call(string $method, array $args)
    {
        foreach ($this->extensions as $extension) {
            try {
                $callback = [$extension, $method];
                return $callback(...$args);
            } catch (BadMethodCallException) {
            }
        }
        throw new Exception\BadMethodCallException('Method: ' . $method . ' does not exist and could not be located on a registered Extension');
    }
    /**
     * Creates a new Laminas\Feed\Writer\Source data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     */
    public function create_source(): \Laminas\Feed\Writer\Source
    {
        $source = new Source();
        if ($this->get_encoding()) {
            $source->set_encoding($this->get_encoding());
        }
        $source->set_type($this->get_type());
        return $source;
    }
    /**
     * Appends a Laminas\Feed\Writer\Entry object representing a new entry/item
     * the feed data container's internal group of entries.
     *
     * @return $this
     */
    public function set_source(Source $source): static
    {
        $this->data['source'] = $source;
        return $this;
    }
    public function get_source(): ?\Laminas\Feed\Writer\Source
    {
        if (!isset($this->data['source'])) {
            return null;
        }
        if (!$this->data['source'] instanceof Source) {
            throw new Exception\RuntimeException(sprintf('Entry source is of invalid type ("%s")', get_debug_type($this->data['source'])));
        }
        return $this->data['source'];
    }
    /**
     * Load extensions from Laminas\Feed\Writer\Writer
     *
     * @return void
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _load_extensions()
    {
        $all = Writer::get_extensions();
        $manager = Writer::get_extension_manager();
        $exts = $all['entry'];
        foreach ($exts as $ext) {
            $this->extensions[$ext] = $manager->get($ext);
            $this->extensions[$ext]->set_encoding($this->get_encoding());
        }
    }
}