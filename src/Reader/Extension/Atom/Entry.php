<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Atom;

use function array_key_exists;
use function count;
use DateTime;
use Dom_Document;
use Dom_Element;
use Dom_Node_List;
use function is_string;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Collection;
use Laminas\Feed\Reader\Extension;
use Laminas\Feed\Uri;
use function preg_replace;
use stdClass;
use function strlen;
use function trim;
class Entry extends Extension\Abstract_Entry
{
    /**
     * Get the specified author
     *
     * @param  int $index
     * @return null|string
     */
    public function get_author($index = 0): null
    {
        $authors = $this->get_authors();
        return isset($authors[$index]) && is_string($authors[$index]) ? $authors[$index] : null;
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
        $authors = [];
        $list = $this->get_xpath()->query($this->get_xpath_prefix() . '//atom:author');
        if (!$list instanceof Dom_Node_List || !$list->length) {
            /**
             * TODO: Limit query to feed level els only!
             */
            $list = $this->get_xpath()->query('//atom:author');
        }
        if ($list instanceof Dom_Node_List && $list->length) {
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
     * Get the entry content
     *
     * @return string
     */
    public function get_content()
    {
        if (array_key_exists('content', $this->data)) {
            return $this->data['content'];
        }
        $content = null;
        $el = $this->get_xpath()->query($this->get_xpath_prefix() . '/atom:content');
        if ($el->length > 0) {
            $el = $el->item(0);
            $type = $el->get_attribute('type');
            switch ($type) {
                case '':
                case 'text':
                case 'text/plain':
                case 'html':
                case 'text/html':
                    $content = $el->node_value;
                    break;
                case 'xhtml':
                    $this->get_xpath()->register_namespace('xhtml', 'http://www.w3.org/1999/xhtml');
                    $xhtml = $this->get_xpath()->query($this->get_xpath_prefix() . '/atom:content/xhtml:div')->item(0);
                    $d = new Dom_Document('1.0', $this->get_encoding());
                    $xhtmls = $d->import_node($xhtml, true);
                    $d->append_child($xhtmls);
                    $content = $this->collect_xhtml($d->save_xml(), $d->lookup_prefix('http://www.w3.org/1999/xhtml'));
                    break;
            }
        }
        if (!$content) {
            $content = $this->get_description();
        }
        $this->data['content'] = trim($content ?? '');
        return $this->data['content'];
    }
    /**
     * Parse out XHTML to remove the namespacing
     *
     * @param  string $xhtml
     * @param  string $prefix
     */
    protected function collect_xhtml($xhtml, $prefix): string|array|null
    {
        if (!empty($prefix)) {
            $prefix .= ':';
        }
        $matches = ['/<\?xml[^<]*>[^<]*<' . $prefix . 'div[^>]*>/', '/<\/' . $prefix . 'div>\s*$/'];
        $xhtml = preg_replace($matches, '', $xhtml);
        if (!empty($prefix)) {
            return preg_replace('/(<[\/]?)' . $prefix . '([a-zA-Z]+)/', '$1$2', (string) $xhtml);
        }
        return $xhtml;
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
        $date = null;
        if ($this->get_atom_type() === Reader\Reader::TYPE_ATOM_03) {
            $date_created = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/atom:created)');
        } else {
            $date_created = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/atom:published)');
        }
        if ($date_created) {
            $date = new DateTime($date_created);
        }
        $this->data['datecreated'] = $date;
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
        $date = null;
        if ($this->get_atom_type() === Reader\Reader::TYPE_ATOM_03) {
            $date_modified = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/atom:modified)');
        } else {
            $date_modified = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/atom:updated)');
        }
        if ($date_modified) {
            $date = new DateTime($date_modified);
        }
        $this->data['datemodified'] = $date;
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
        $description = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/atom:summary)');
        if (!$description) {
            $description = null;
        }
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
        $enclosure = null;
        $node_list = $this->get_xpath()->query($this->get_xpath_prefix() . '/atom:link[@rel="enclosure"]');
        if ($node_list instanceof Dom_Node_List && $node_list->length > 0) {
            /** @var DOMElement $node */
            $node = $node_list->item(0);
            $enclosure = new stdClass();
            $enclosure->url = $node->get_attribute('href');
            $enclosure->length = $node->get_attribute('length');
            $enclosure->type = $node->get_attribute('type');
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
        $id = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/atom:id)');
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
     * Get the base URI of the feed (if set).
     *
     * @return null|string
     */
    public function get_base_url()
    {
        if (array_key_exists('baseUrl', $this->data)) {
            return $this->data['baseUrl'];
        }
        $base_url = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/@xml:base[1])');
        if (!$base_url) {
            $base_url = $this->get_xpath()->evaluate('string(//@xml:base[1])');
        }
        if (!$base_url) {
            $base_url = null;
        }
        $this->data['baseUrl'] = $base_url;
        return $this->data['baseUrl'];
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
        return isset($this->data['links']) && is_string($this->data['links']) ? $this->data['links'] : null;
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
        $list = $this->get_xpath()->query($this->get_xpath_prefix() . '//atom:link[@rel="alternate"]/@href|' . $this->get_xpath_prefix() . '//atom:link[not(@rel)]/@href');
        if ($list instanceof Dom_Node_List && $list->length) {
            foreach ($list as $link) {
                $links[] = $this->absolutise_uri($link->value);
            }
        }
        $this->data['links'] = $links;
        return $this->data['links'];
    }
    /**
     * Get a permalink to the entry
     */
    public function get_permalink(): string
    {
        $permalink = $this->get_link(0);
        return is_string($permalink) ? $permalink : '';
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
        $title = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/atom:title)');
        if (!is_string($title)) {
            $title = null;
        }
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
        $count = null;
        $this->get_xpath()->register_namespace('thread10', 'http://purl.org/syndication/thread/1.0');
        $list = $this->get_xpath()->query($this->get_xpath_prefix() . '//atom:link[@rel="replies"]/@thread10:count');
        if ($list instanceof Dom_Node_List && $list->length) {
            $count = $list->item(0)->value;
        }
        $this->data['commentcount'] = $count;
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
        $link = null;
        $list = $this->get_xpath()->query($this->get_xpath_prefix() . '//atom:link[@rel="replies" and @type="text/html"]/@href');
        if ($list instanceof Dom_Node_List && $list->length) {
            $link = $list->item(0)->value;
            $link = $this->absolutise_uri($link);
        }
        $this->data['commentlink'] = $link;
        return $this->data['commentlink'];
    }
    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function get_comment_feed_link(string $type = 'atom')
    {
        if (array_key_exists('commentfeedlink', $this->data)) {
            return $this->data['commentfeedlink'];
        }
        $link = null;
        $list = $this->get_xpath()->query($this->get_xpath_prefix() . '//atom:link[@rel="replies" and @type="application/' . $type . '+xml"]/@href');
        if ($list instanceof Dom_Node_List && $list->length) {
            $link = $list->item(0)->value;
            $link = $this->absolutise_uri($link);
        }
        $this->data['commentfeedlink'] = $link;
        return $this->data['commentfeedlink'];
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
        if ($this->get_atom_type() === Reader\Reader::TYPE_ATOM_10) {
            $list = $this->get_xpath()->query($this->get_xpath_prefix() . '//atom:category');
        } else {
            /**
             * Since Atom 0.3 did not support categories, it would have used the
             * Dublin Core extension. However there is a small possibility Atom 0.3
             * may have been retrofitted to use Atom 1.0 instead.
             */
            $this->get_xpath()->register_namespace('atom10', Reader\Reader::NAMESPACE_ATOM_10);
            $list = $this->get_xpath()->query($this->get_xpath_prefix() . '//atom10:category');
        }
        if ($list instanceof Dom_Node_List && $list->length) {
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
     * Get source feed metadata from the entry
     *
     * @return null|Reader\Feed\Atom\Source
     */
    public function get_source()
    {
        if (array_key_exists('source', $this->data)) {
            return $this->data['source'];
        }
        $source = null;
        // TODO: Investigate why _getAtomType() fails here. Is it even needed?
        if ($this->get_type() === Reader\Reader::TYPE_ATOM_10) {
            $list = $this->get_xpath()->query($this->get_xpath_prefix() . '/atom:source[1]');
            if ($list instanceof Dom_Node_List && $list->length) {
                $element = $list->item(0);
                $source = new Reader\Feed\Atom\Source($element, $this->get_xpath_prefix());
            }
        }
        $this->data['source'] = $source;
        return $this->data['source'];
    }
    /**
     * Attempt to absolutise the URI, i.e. if a relative URI apply the
     *  xml:base value as a prefix to turn into an absolute URI.
     *
     * @param string $link
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
     * Get an author entry
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
     * Register the default namespaces for the current feed format
     */
    protected function register_namespaces()
    {
        match ($this->get_atom_type()) {
            Reader\Reader::TYPE_ATOM_03 => $this->get_xpath()->register_namespace('atom', Reader\Reader::NAMESPACE_ATOM_03),
            default => $this->get_xpath()->register_namespace('atom', Reader\Reader::NAMESPACE_ATOM_10),
        };
    }
    /**
     * Detect the presence of any Atom namespaces in use
     */
    protected function get_atom_type(): ?string
    {
        $dom = $this->get_dom_document();
        $prefix_atom03 = $dom->lookup_prefix(Reader\Reader::NAMESPACE_ATOM_03);
        $prefix_atom10 = $dom->lookup_prefix(Reader\Reader::NAMESPACE_ATOM_10);
        if ($dom->is_default_namespace(Reader\Reader::NAMESPACE_ATOM_03) || !empty($prefix_atom03)) {
            return Reader\Reader::TYPE_ATOM_03;
        }
        if ($dom->is_default_namespace(Reader\Reader::NAMESPACE_ATOM_10) || !empty($prefix_atom10)) {
            return Reader\Reader::TYPE_ATOM_10;
        }
        return null;
    }
}