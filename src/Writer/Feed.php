<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function array_values;
use function count;
// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use Countable;
use Iterator;
use function krsort;
use Return_Type_Will_Change;
use const SORT_NUMERIC;
use function strtolower;
use function time;
use function ucfirst;
/** @template-implements Iterator<int, Entry> */
class Feed extends Abstract_Feed implements Iterator, Countable
{
    /**
     * Contains all entry objects
     *
     * @var array<int, Entry>
     */
    protected $entries = [];
    /**
     * A pointer for the iterator to keep track of the entries array
     *
     * @var int
     */
    protected $entries_key = 0;
    /**
     * Creates a new Laminas\Feed\Writer\Entry data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     */
    public function create_entry(): \Laminas\Feed\Writer\Entry
    {
        $entry = new Entry();
        if ($this->get_encoding()) {
            $entry->set_encoding($this->get_encoding());
        }
        $entry->set_type($this->get_type());
        return $entry;
    }
    /**
     * Appends a Laminas\Feed\Writer\Deleted object representing a new entry tombstone
     * to the feed data container's internal group of entries.
     */
    public function add_tombstone(Deleted $deleted): void
    {
        $this->entries[] = $deleted;
    }
    /**
     * Creates a new Laminas\Feed\Writer\Deleted data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     */
    public function create_tombstone(): \Laminas\Feed\Writer\Deleted
    {
        $deleted = new Deleted();
        $encoding = $this->get_encoding();
        if (null !== $encoding) {
            $deleted->set_encoding($encoding);
        }
        $deleted->set_type($this->get_type());
        return $deleted;
    }
    /**
     * Appends a Laminas\Feed\Writer\Entry object representing a new entry/item
     * the feed data container's internal group of entries.
     *
     * @return $this
     */
    public function add_entry(Entry $entry): static
    {
        $this->entries[] = $entry;
        return $this;
    }
    /**
     * Removes a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param  int $index
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function remove_entry($index): static
    {
        if (!isset($this->entries[$index])) {
            throw new Exception\InvalidArgumentException('Undefined index: ' . $index . '. Entry does not exist.');
        }
        unset($this->entries[$index]);
        return $this;
    }
    /**
     * Retrieve a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param  int $index
     * @return Entry
     * @throws Exception\InvalidArgumentException
     */
    public function get_entry($index = 0)
    {
        if (isset($this->entries[$index])) {
            return $this->entries[$index];
        }
        throw new Exception\InvalidArgumentException('Undefined index: ' . $index . '. Entry does not exist.');
    }
    /**
     * Orders all indexed entries by date, thus offering date ordered readable
     * content where a parser (or Homo Sapien) ignores the generic rule that
     * XML element order is irrelevant and has no intrinsic meaning.
     *
     * Using this method will alter the original indexation.
     *
     * @return $this
     */
    public function order_by_date(): static
    {
        /**
         * Could do with some improvement for performance perhaps
         */
        $timestamp = time();
        $entries = [];
        foreach ($this->entries as $entry) {
            if ($entry->get_date_modified()) {
                $timestamp = (int) $entry->get_date_modified()->get_timestamp();
            } elseif ($entry->get_date_created()) {
                $timestamp = (int) $entry->get_date_created()->get_timestamp();
            }
            $entries[$timestamp] = $entry;
        }
        krsort($entries, SORT_NUMERIC);
        $this->entries = array_values($entries);
        return $this;
    }
    /**
     * Get the number of feed entries.
     * Required by the Iterator interface.
     *
     * @return positive-int|0
     */
    #[Return_Type_Will_Change]
    public function count()
    {
        return count($this->entries);
    }
    /**
     * Return the current entry
     *
     * @return Entry
     */
    #[Return_Type_Will_Change]
    public function current()
    {
        return $this->entries[$this->key()];
    }
    /**
     * Return the current feed key
     *
     * @return int
     */
    #[Return_Type_Will_Change]
    public function key()
    {
        return $this->entries_key;
    }
    /**
     * Move the feed pointer forward
     */
    #[Return_Type_Will_Change]
    public function next(): void
    {
        ++$this->entries_key;
    }
    /**
     * Reset the pointer in the feed object
     */
    #[Return_Type_Will_Change]
    public function rewind(): void
    {
        $this->entries_key = 0;
    }
    /**
     * Check to see if the iterator is still valid
     *
     * @return bool
     */
    #[Return_Type_Will_Change]
    public function valid()
    {
        return 0 <= $this->entries_key && $this->entries_key < $this->count();
    }
    /**
     * Attempt to build and return the feed resulting from the data set
     *
     * @param  string $type The feed type "rss" or "atom" to export as
     * @param  bool $ignoreExceptions
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function export($type, $ignore_exceptions = false)
    {
        $this->set_type(strtolower($type));
        $type = ucfirst($this->get_type());
        if ($type !== 'Rss' && $type !== 'Atom') {
            throw new Exception\InvalidArgumentException('Invalid feed type specified: ' . $type . '. Should be one of "rss" or "atom".');
        }
        $render_class = 'Laminas\Feed\Writer\Renderer\Feed\\' . $type;
        $renderer = new $render_class($this);
        if ($ignore_exceptions) {
            $renderer->ignore_exceptions();
        }
        return $renderer->render()->save_xml();
    }
}