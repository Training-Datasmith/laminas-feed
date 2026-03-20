<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Feed;

use Countable;
use DateTime;
use Iterator;
use Laminas\Feed\Reader\Collection\Category;
use Laminas\Feed\Reader\Entry\Entry_Interface;
/**
 * @template TItem of EntryInterface
 * @template-extends Iterator<int, TItem>
 */
interface Feed_Interface extends Iterator, Countable
{
    /**
     * Get a single author
     *
     * @param  int $index
     * @return null|string
     */
    public function get_author($index = 0);
    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function get_authors();
    /**
     * Get the copyright entry
     *
     * @return null|string
     */
    public function get_copyright();
    /**
     * Get the feed creation date
     *
     * @return null|DateTime
     */
    public function get_date_created();
    /**
     * Get the feed modification date
     *
     * @return null|DateTime
     */
    public function get_date_modified();
    /**
     * Get the feed description
     *
     * @return null|string
     */
    public function get_description();
    /**
     * Get the feed generator entry
     *
     * @return null|string
     */
    public function get_generator();
    /**
     * Get the feed ID
     *
     * @return null|string
     */
    public function get_id();
    /**
     * Get the feed language
     *
     * @return null|string
     */
    public function get_language();
    /**
     * Get a link to the HTML source
     *
     * @return null|string
     */
    public function get_link();
    /**
     * Get a link to the XML feed
     *
     * @return null|string
     */
    public function get_feed_link();
    /**
     * Get the feed title
     *
     * @return null|string
     */
    public function get_title();
    /**
     * Get all categories
     *
     * @return Category
     */
    public function get_categories();
}