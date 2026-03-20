<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function array_key_exists;
use function date;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use function in_array;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;
use Laminas\Feed\Uri;
use Laminas\Validator;
use function preg_match;
use function sprintf;
use function strlen;
use function strtolower;
use function strtotime;
class Abstract_Feed
{
    /**
     * Contains all Feed level date to append in feed output
     *
     * @var array
     */
    protected $data = [];
    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $type;
    /** @var Extension\RendererInterface[] */
    protected $extensions;
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
     * Set the feed creation date
     *
     * @param DateTime|DateTimeImmutable|int|null|string $date
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
     * @param DateTime|DateTimeImmutable|int|null|string $date
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
     * Set the feed last-build date. Ignored for Atom 1.0.
     *
     * @param DateTime|DateTimeImmutable|int|null|string $date
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_last_build_date($date = null): static
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
        $this->data['lastBuildDate'] = $date;
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
     * Set the feed generator entry
     *
     * @param  array|string $name
     * @param  null|string $version
     * @param  null|string $uri
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_generator($name, $version = null, $uri = null): static
    {
        if (is_array($name)) {
            $data = $name;
            if (empty($data['name']) || !is_string($data['name'])) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "name" must be a non-empty string');
            }
            $generator = ['name' => $data['name']];
            if (isset($data['version'])) {
                if (empty($data['version']) || !is_string($data['version'])) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "version" must be a non-empty string');
                }
                $generator['version'] = $data['version'];
            }
            if (isset($data['uri'])) {
                if (empty($data['uri']) || !is_string($data['uri']) || !Uri::factory($data['uri'])->is_valid()) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI');
                }
                $generator['uri'] = $data['uri'];
            }
        } else {
            if (empty($name) || !is_string($name)) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "name" must be a non-empty string');
            }
            $generator = ['name' => $name];
            if (isset($version)) {
                if (empty($version) || !is_string($version)) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "version" must be a non-empty string');
                }
                $generator['version'] = $version;
            }
            if (isset($uri)) {
                if (empty($uri) || !is_string($uri) || !Uri::factory($uri)->is_valid()) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI');
                }
                $generator['uri'] = $uri;
            }
        }
        $this->data['generator'] = $generator;
        return $this;
    }
    /**
     * Set the feed ID - URI or URN (via PCRE pattern) supported
     *
     * @param  string $id
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_id($id): static
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        if ((empty($id) || !is_string($id) || !Uri::factory($id)->is_valid()) && !preg_match("#^urn:[a-zA-Z0-9][a-zA-Z0-9\\-]{1,31}:([a-zA-Z0-9\\(\\)\\+\\,\\.\\:\\=\\@\\;\$\\_\\!\\*\\-]|%[0-9a-fA-F]{2})*#", $id) && !$this->_validate_tag_uri($id)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        // phpcs:enable Generic.Files.LineLength.TooLong
        $this->data['id'] = $id;
        return $this;
    }
    /**
     * Set a feed image (URI at minimum). Parameter is a single array with the
     * required key 'uri'. When rendering as RSS, the required keys are 'uri',
     * 'title' and 'link'. RSS also specifies three optional parameters 'width',
     * 'height' and 'description'. Only 'uri' is required and used for Atom rendering.
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_image(array $data): static
    {
        if (empty($data['uri']) || !is_string($data['uri']) || !Uri::factory($data['uri'])->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter \'uri\' must be a non-empty string and valid URI/IRI');
        }
        $this->data['image'] = $data;
        return $this;
    }
    /**
     * Set the feed language
     *
     * @param  string $language
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_language($language): static
    {
        if (empty($language) || !is_string($language)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['language'] = $language;
        return $this;
    }
    /**
     * Set a link to the HTML source
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
     * Set a link to an XML feed for any feed type/version
     *
     * @param  string $link
     * @param  string $type
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_feed_link($link, $type): static
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "link"" must be a non-empty string and valid URI/IRI');
        }
        if (!in_array(strtolower($type), ['rss', 'rdf', 'atom'])) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "type"; You must declare the type of feed the link points to, i.e. RSS, RDF or Atom');
        }
        $this->data['feedLinks'][strtolower($type)] = $link;
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
     * Set the feed's base URL
     *
     * @param  string $url
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_base_url($url): static
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "url" array value must be a non-empty string and valid URI/IRI');
        }
        $this->data['baseUrl'] = $url;
        return $this;
    }
    /**
     * Add a Pubsubhubbub hub endpoint URL
     *
     * @param  string $url
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function add_hub($url): static
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->is_valid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "url" array value must be a non-empty string and valid URI/IRI');
        }
        if (!isset($this->data['hubs'])) {
            $this->data['hubs'] = [];
        }
        $this->data['hubs'][] = $url;
        return $this;
    }
    /**
     * Add Pubsubhubbub hub endpoint URLs
     *
     * @return $this
     */
    public function add_hubs(array $urls): static
    {
        foreach ($urls as $url) {
            $this->add_hub($url);
        }
        return $this;
    }
    /**
     * Add a feed category
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
     * Set an array of feed categories
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
     * Get a single author
     *
     * @param  int $index
     * @return null|string
     */
    public function get_author($index = 0)
    {
        return $this->data['authors'][$index] ?? null;
    }
    /**
     * Get an array with feed authors
     *
     * @return null|array
     */
    public function get_authors()
    {
        if (!array_key_exists('authors', $this->data)) {
            return null;
        }
        return $this->data['authors'];
    }
    /**
     * Get the copyright entry
     *
     * @return null|string
     */
    public function get_copyright()
    {
        if (!array_key_exists('copyright', $this->data)) {
            return null;
        }
        return $this->data['copyright'];
    }
    /**
     * Get the feed creation date
     *
     * @return null|string
     */
    public function get_date_created()
    {
        if (!array_key_exists('dateCreated', $this->data)) {
            return null;
        }
        return $this->data['dateCreated'];
    }
    /**
     * Get the feed modification date
     *
     * @return null|string
     */
    public function get_date_modified()
    {
        if (!array_key_exists('dateModified', $this->data)) {
            return null;
        }
        return $this->data['dateModified'];
    }
    /**
     * Get the feed last-build date
     *
     * @return null|string
     */
    public function get_last_build_date()
    {
        if (!array_key_exists('lastBuildDate', $this->data)) {
            return null;
        }
        return $this->data['lastBuildDate'];
    }
    /**
     * Get the feed description
     *
     * @return null|string
     */
    public function get_description()
    {
        if (!array_key_exists('description', $this->data)) {
            return null;
        }
        return $this->data['description'];
    }
    /**
     * Get the feed generator entry
     *
     * @return null|string
     */
    public function get_generator()
    {
        if (!array_key_exists('generator', $this->data)) {
            return null;
        }
        return $this->data['generator'];
    }
    /**
     * Get the feed ID
     *
     * @return null|string
     */
    public function get_id()
    {
        if (!array_key_exists('id', $this->data)) {
            return null;
        }
        return $this->data['id'];
    }
    /**
     * Get the feed image URI
     *
     * @return null|array
     */
    public function get_image()
    {
        if (!array_key_exists('image', $this->data)) {
            return null;
        }
        return $this->data['image'];
    }
    /**
     * Get the feed language
     *
     * @return null|string
     */
    public function get_language()
    {
        if (!array_key_exists('language', $this->data)) {
            return null;
        }
        return $this->data['language'];
    }
    /**
     * Get a link to the HTML source
     *
     * @return null|string
     */
    public function get_link()
    {
        if (!array_key_exists('link', $this->data)) {
            return null;
        }
        return $this->data['link'];
    }
    /**
     * Get a link to the XML feed
     *
     * @return null|string
     */
    public function get_feed_links()
    {
        if (!array_key_exists('feedLinks', $this->data)) {
            return null;
        }
        return $this->data['feedLinks'];
    }
    /**
     * Get the feed title
     *
     * @return null|string
     */
    public function get_title()
    {
        if (!array_key_exists('title', $this->data)) {
            return null;
        }
        return $this->data['title'];
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
     * Get the feed's base url
     *
     * @return null|string
     */
    public function get_base_url()
    {
        if (!array_key_exists('baseUrl', $this->data)) {
            return null;
        }
        return $this->data['baseUrl'];
    }
    /**
     * Get the URLs used as Pubsubhubbub hubs endpoints
     *
     * @return null|string
     */
    public function get_hubs()
    {
        if (!array_key_exists('hubs', $this->data)) {
            return null;
        }
        return $this->data['hubs'];
    }
    /**
     * Get the feed categories
     *
     * @return null|string
     */
    public function get_categories()
    {
        if (!array_key_exists('categories', $this->data)) {
            return null;
        }
        return $this->data['categories'];
    }
    /**
     * Resets the instance and deletes all data
     */
    public function reset(): void
    {
        $this->data = [];
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
            } catch (Exception\BadMethodCallException) {
            }
        }
        throw new Exception\BadMethodCallException('Method: ' . $method . ' does not exist and could not be located on a registered Extension');
    }
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Validate a URI using the tag scheme (RFC 4151)
     *
     * @param  string $id
     * @return bool
     */
    protected function _validate_tag_uri($id)
    {
        if (preg_match('/^tag:(?P<name>.*),(?P<date>\d{4}-?\d{0,2}-?\d{0,2}):(?P<specific>.*)(.*:)*$/', $id, $matches)) {
            $dvalid = false;
            $date = $matches['date'];
            $d6 = strtotime($date);
            if (strlen($date) === 4 && $date <= date('Y')) {
                $dvalid = true;
            } elseif (strlen($date) === 7 && $d6 < strtotime('now')) {
                $dvalid = true;
            } elseif (strlen($date) === 10 && $d6 < strtotime('now')) {
                $dvalid = true;
            }
            $validator = new Validator\Email_Address();
            if ($validator->is_valid($matches['name'])) {
                $nvalid = true;
            } else {
                $nvalid = $validator->is_valid('info@' . $matches['name']);
            }
            return $dvalid && $nvalid;
        }
        return false;
    }
    /**
     * Load extensions from Laminas\Feed\Writer\Writer
     *
     * @throws Exception\RuntimeException
     * @return void
     */
    protected function _load_extensions()
    {
        $all = Writer::get_extensions();
        $manager = Writer::get_extension_manager();
        $exts = $all['feed'];
        foreach ($exts as $ext) {
            if (!$manager->has($ext)) {
                throw new Exception\RuntimeException(sprintf('Unable to load extension "%s"; could not resolve to class', $ext));
            }
            $this->extensions[$ext] = $manager->get($ext);
            $this->extensions[$ext]->set_encoding($this->get_encoding());
        }
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}