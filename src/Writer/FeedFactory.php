<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function array_key_exists;
use function get_debug_type;
use function is_array;
use function method_exists;
use function sprintf;
use function str_replace;
use function strtolower;
use Traversable;
// phpcs:ignore WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix
abstract class Feed_Factory
{
    /**
     * Create and return a Feed based on data provided.
     *
     * @param  array|Traversable $data
     * @return Feed
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($data)
    {
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf('%s expects an array or Traversable argument; received "%s"', __METHOD__, get_debug_type($data)));
        }
        $feed = new Feed();
        foreach ($data as $key => $value) {
            // Setters
            $key = static::convert_key($key);
            $method = 'set' . $key;
            if (method_exists($feed, $method)) {
                switch ($method) {
                    case 'setfeedlink':
                        if (!is_array($value)) {
                            // Need an array
                            break;
                        }
                        if (!array_key_exists('link', $value) || !array_key_exists('type', $value)) {
                            // Need both keys to set this correctly
                            break;
                        }
                        $feed->set_feed_link($value['link'], $value['type']);
                        break;
                    default:
                        $feed->{$method}($value);
                        break;
                }
                continue;
            }
            // Entries
            if ('entries' === $key) {
                static::create_entries($value, $feed);
                continue;
            }
        }
        return $feed;
    }
    /**
     * Normalize a key
     *
     * @param  string $key
     * @return string
     */
    protected static function convert_key($key)
    {
        return str_replace('_', '', strtolower($key));
    }
    /**
     * Create and attach entries to a feed
     *
     * @param  array|Traversable $entries
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    protected static function create_entries($entries, Feed $feed)
    {
        if (!is_array($entries) && !$entries instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf('%s::factory expects the "entries" value to be an array or Traversable; received "%s"', static::class, get_debug_type($entries)));
        }
        foreach ($entries as $data) {
            if (!is_array($data) && !$data instanceof Traversable && !$data instanceof Entry) {
                throw new Exception\InvalidArgumentException(sprintf('%s expects an array, Traversable, or Laminas\Feed\Writer\Entry argument; received "%s"', __METHOD__, get_debug_type($data)));
            }
            // Use case 1: Entry item
            if ($data instanceof Entry) {
                $feed->add_entry($data);
                continue;
            }
            // Use case 2: iterate item and populate entry
            $entry = $feed->create_entry();
            foreach ($data as $key => $value) {
                $key = static::convert_key($key);
                $method = 'set' . $key;
                if (!method_exists($entry, $method)) {
                    continue;
                }
                $entry->{$method}($value);
            }
            $feed->add_entry($entry);
        }
    }
}