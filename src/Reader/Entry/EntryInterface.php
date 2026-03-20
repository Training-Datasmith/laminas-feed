<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Entry;

use DateTime;
use Laminas\Feed\Reader\Collection\Category;
interface Entry_Interface
{
    /**
     * Get the specified author
     *
     * @param  int $index
     * @return array<string, string>|null
     */
    public function get_author($index = 0);
    /**
     * Get an array with feed authors
     *
     * @return null|array|iterable
     */
    public function get_authors();
    /**
     * Get the entry content
     *
     * @return string
     */
    public function get_content();
    /**
     * Get the entry creation date
     *
     * @return null|DateTime
     */
    public function get_date_created();
    /**
     * Get the entry modification date
     *
     * @return null|DateTime
     */
    public function get_date_modified();
    /**
     * Get the entry description
     *
     * @return string
     */
    public function get_description();
    /**
     * Get the entry enclosure
     *
     * @return null|object{url?: string, href?: string, length: int, type: string}
     */
    public function get_enclosure();
    /**
     * Get the entry ID
     *
     * @return string
     */
    public function get_id();
    /**
     * Get a specific link
     *
     * @param  int $index
     * @return string
     */
    public function get_link($index = 0);
    /**
     * Get all links
     *
     * @return array
     */
    public function get_links();
    /**
     * Get a permalink to the entry
     *
     * @return string
     */
    public function get_permalink();
    /**
     * Get the entry title
     *
     * @return string
     */
    public function get_title();
    /**
     * Get the number of comments/replies for current entry
     *
     * @return int
     */
    public function get_comment_count();
    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function get_comment_link();
    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function get_comment_feed_link();
    /**
     * Get all categories
     *
     * @return Category
     */
    public function get_categories();
}