<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader;

use function array_filter;
use function array_pop;
use ArrayObject;
use Dom_Element;
// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use Dom_Node_List;
use function explode;
use function implode;
use Laminas\Feed\Uri;
use function ltrim;
use Return_Type_Will_Change;
use function sprintf;
use function strtolower;
use function trim;
/** @template-extends ArrayObject<array-key, FeedSet|Feed\FeedInterface|string|null> */
class Feed_Set extends ArrayObject
{
    /** @var null|string */
    public $rss;
    /** @var null|string */
    public $rdf;
    /** @var null|string */
    public $atom;
    /**
     * Import a DOMNodeList from any document containing a set of links
     * for alternate versions of a document, which will normally refer to
     * RSS/RDF/Atom feeds for the current document.
     *
     * All such links are stored internally, however the first instance of
     * each RSS, RDF or Atom type has its URI stored as a public property
     * as a shortcut where the use case is simply to get a quick feed ref.
     *
     * Note that feeds are not loaded at this point, but will be lazy
     * loaded automatically when each links 'feed' array key is accessed.
     *
     * @param string $uri
     */
    public function add_links(Dom_Node_List $links, $uri): void
    {
        foreach ($links as $link) {
            /** @var DOMElement $link */
            if (strtolower($link->get_attribute('rel')) !== 'alternate') {
                continue;
            }
            if (!$link->get_attribute('type')) {
                continue;
            }
            if (!$link->get_attribute('href')) {
                continue;
            }
            if (null === $this->rss && $link->get_attribute('type') === 'application/rss+xml') {
                $this->rss = $this->absolutise_uri(trim($link->get_attribute('href')), $uri);
            } elseif (null === $this->atom && $link->get_attribute('type') === 'application/atom+xml') {
                $this->atom = $this->absolutise_uri(trim($link->get_attribute('href')), $uri);
            } elseif (null === $this->rdf && $link->get_attribute('type') === 'application/rdf+xml') {
                $this->rdf = $this->absolutise_uri(trim($link->get_attribute('href')), $uri);
            }
            $this[] = new static(['rel' => 'alternate', 'type' => $link->get_attribute('type'), 'href' => $this->absolutise_uri(trim($link->get_attribute('href')), $uri), 'title' => $link->get_attribute('title')]);
        }
    }
    /**
     * Attempt to turn a relative URI into an absolute URI
     *
     * @param  string $link
     * @param  null|string $uri OPTIONAL
     * @return null|string absolutised link or null if invalid
     */
    protected function absolutise_uri($link, $uri = null)
    {
        $link_uri = Uri::factory($link);
        if ($link_uri->is_absolute()) {
            // invalid absolute link can not be recovered
            return $link_uri->is_valid() ? $link : null;
        }
        $scheme = 'http';
        if ($uri !== null) {
            $uri = Uri::factory($uri);
            $scheme = $uri->get_scheme() ?: $scheme;
        }
        if ($link_uri->get_host()) {
            $link = $this->resolve_scheme_relative_uri($link, $scheme);
        } elseif ($uri !== null) {
            $link = $this->resolve_relative_uri($link, $scheme, $uri->get_host(), $uri->get_path());
        }
        if (!Uri::factory($link)->is_valid()) {
            return null;
        }
        return $link;
    }
    /**
     * Resolves scheme relative link to absolute
     *
     * @param  string $link
     */
    private function resolve_scheme_relative_uri($link, string $scheme): string
    {
        $link = ltrim($link, '/');
        return sprintf('%s://%s', $scheme, $link);
    }
    /**
     * Resolves relative link to absolute
     *
     * @param  string $link
     * @param  string $host
     */
    private function resolve_relative_uri($link, string $scheme, $host, string $uri_path): string
    {
        if ($link[0] !== '/') {
            $link = $uri_path . '/' . $link;
        }
        return sprintf('%s://%s/%s', $scheme, $host, $this->canonicalize_path($link));
    }
    /**
     * Canonicalize relative path
     *
     * @param  string $path
     */
    protected function canonicalize_path($path): string
    {
        $parts = array_filter(explode('/', $path));
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' === $part) {
                continue;
            }
            if ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode('/', $absolutes);
    }
    /**
     * @inheritDoc
     *
     * Supports lazy loading of feeds using Reader::import() but
     * delegates any other operations to the parent class.
     */
    #[Return_Type_Will_Change]
    public function offsetGet($offset)
    {
        if ($offset === 'feed' && !$this->offsetExists('feed')) {
            if (!$this->offsetExists('href')) {
                return;
            }
            $feed = Reader::import($this->offsetGet('href'));
            $this->offsetSet('feed', $feed);
            return $feed;
        }
        return parent::offsetGet($offset);
    }
}