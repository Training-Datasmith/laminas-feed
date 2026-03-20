<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Dublin_Core;

use function array_key_exists;
use DateTime;
use Dom_Node_List;
use function is_array;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Collection;
use Laminas\Feed\Reader\Extension;
class Entry extends Extension\Abstract_Entry
{
    /**
     * Get an author entry
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
        $list = $this->get_xpath()->evaluate($this->get_xpath_prefix() . '//dc11:creator');
        if (!$list instanceof Dom_Node_List || !$list->length) {
            $list = $this->get_xpath()->evaluate($this->get_xpath_prefix() . '//dc10:creator');
        }
        if (!$list instanceof Dom_Node_List || !$list->length) {
            $list = $this->get_xpath()->evaluate($this->get_xpath_prefix() . '//dc11:publisher');
            if (!$list instanceof Dom_Node_List || !$list->length) {
                $list = $this->get_xpath()->evaluate($this->get_xpath_prefix() . '//dc10:publisher');
            }
        }
        if ($list instanceof Dom_Node_List && $list->length) {
            foreach ($list as $author) {
                $authors[] = ['name' => $author->node_value];
            }
            $authors = new Collection\Author(Reader\Reader::array_unique($authors));
        } else {
            $authors = null;
        }
        $this->data['authors'] = $authors;
        return $this->data['authors'];
    }
    /**
     * Get categories (subjects under DC)
     *
     * @return Collection\Category
     */
    public function get_categories()
    {
        if (array_key_exists('categories', $this->data)) {
            return $this->data['categories'];
        }
        $list = $this->get_xpath()->evaluate($this->get_xpath_prefix() . '//dc11:subject');
        if (!$list instanceof Dom_Node_List || !$list->length) {
            $list = $this->get_xpath()->evaluate($this->get_xpath_prefix() . '//dc10:subject');
        }
        if ($list instanceof Dom_Node_List && $list->length) {
            $category_collection = new Collection\Category();
            foreach ($list as $category) {
                $category_collection[] = ['term' => $category->node_value, 'scheme' => null, 'label' => $category->node_value];
            }
        } else {
            $category_collection = new Collection\Category();
        }
        $this->data['categories'] = $category_collection;
        return $this->data['categories'];
    }
    /**
     * Get the entry content
     *
     * @return string
     */
    public function get_content()
    {
        return $this->get_description();
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
        $description = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc11:description)');
        if (!$description) {
            $description = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc10:description)');
        }
        if (!$description) {
            $description = null;
        }
        $this->data['description'] = $description;
        return $this->data['description'];
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
        $id = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc11:identifier)');
        if (!$id) {
            $id = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc10:identifier)');
        }
        $this->data['id'] = $id;
        return $this->data['id'];
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
        $title = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc11:title)');
        if (!$title) {
            $title = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc10:title)');
        }
        if (!$title) {
            $title = null;
        }
        $this->data['title'] = $title;
        return $this->data['title'];
    }
    /**
     * @return null|DateTime
     */
    public function get_date()
    {
        if (array_key_exists('date', $this->data)) {
            return $this->data['date'];
        }
        $d = null;
        $date = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc11:date)');
        if (!$date) {
            $date = $this->get_xpath()->evaluate('string(' . $this->get_xpath_prefix() . '/dc10:date)');
        }
        if ($date) {
            $d = new DateTime($date);
        }
        $this->data['date'] = $d;
        return $this->data['date'];
    }
    /**
     * Register DC namespaces
     *
     * @return void
     */
    protected function register_namespaces()
    {
        $this->get_xpath()->register_namespace('dc10', 'http://purl.org/dc/elements/1.0/');
        $this->get_xpath()->register_namespace('dc11', 'http://purl.org/dc/elements/1.1/');
    }
}