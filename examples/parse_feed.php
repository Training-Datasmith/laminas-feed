<?php

declare(strict_types=1);

/**
 * Example: parsing an Atom/RSS feed and creating a new feed with laminas-feed.
 *
 * Run from the laminas-feed project root:
 *   php examples/parse_feed.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Feed\Reader\Reader;
use Laminas\Feed\Writer\Feed;

// --- Parse an Atom feed from a string ---
$atomXml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Example Blog</title>
  <link href="https://example.com/blog"/>
  <updated>2026-03-20T14:00:00Z</updated>
  <id>https://example.com/blog</id>
  <entry>
    <title>First Post</title>
    <link href="https://example.com/blog/first"/>
    <id>https://example.com/blog/first</id>
    <updated>2026-03-20T14:00:00Z</updated>
    <summary>This is the first post summary.</summary>
    <author><name>Alice</name></author>
  </entry>
  <entry>
    <title>Second Post</title>
    <link href="https://example.com/blog/second"/>
    <id>https://example.com/blog/second</id>
    <updated>2026-03-19T10:00:00Z</updated>
    <summary>This is the second post summary.</summary>
    <author><name>Bob</name></author>
  </entry>
</feed>
XML;

$feed = Reader::importString($atomXml);

echo "Feed title:       " . $feed->get_title() . "\n";
echo "Feed link:        " . $feed->get_link() . "\n";
echo "Entry count:      " . count($feed) . "\n\n";

foreach ($feed as $entry) {
    echo "  Title:   " . $entry->get_title() . "\n";
    echo "  Link:    " . $entry->get_link() . "\n";
    echo "  Author:  " . $entry->get_author()['name'] . "\n";
    echo "  Summary: " . $entry->get_description() . "\n\n";
}

// --- Create a new RSS 2.0 feed ---
$writer = new Feed();
$writer->set_title('My New Feed');
$writer->set_link('https://mynewsite.com');
$writer->set_feed_link('https://mynewsite.com/feed', 'rss');
$writer->set_description('A demonstration RSS feed.');
$writer->set_date_modified(new \DateTime('2026-03-20'));
$writer->set_language('en');

$entry = $writer->create_entry();
$entry->set_title('Hello RSS');
$entry->set_link('https://mynewsite.com/hello-rss');
$entry->set_description('First entry in our new feed.');
$entry->set_date_modified(new \DateTime('2026-03-20'));
$entry->add_author(['name' => 'Carol', 'email' => 'carol@example.com']);
$writer->add_entry($entry);

$rss = $writer->export('rss');
echo "Generated RSS feed length: " . strlen($rss) . " bytes\n";
echo substr($rss, 0, 200) . "\n";
