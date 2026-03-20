<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Podcast_Index;

use function array_key_exists;
use function assert;
use Dom_Attr;
use Dom_Element;
use Laminas\Feed\Reader\Entry\Rss as EntryReader;
/**
 * Describes PodcastIndex LiveItem data in a RSS Feed
 */
final class Live_Item extends Entry_Reader
{
    public function __construct(Dom_Element $live_item, string $live_item_key, ?string $type = null)
    {
        parent::__construct($live_item, $live_item_key, $type);
        // override xpath queries to fetch liveItems, not items
        $index = $this->entry_key + 1;
        $this->xpath_query_rss = '//podcast:liveItem[' . $index . ']';
        $this->xpath_query_rdf = '//podcast:liveItem[' . $index . ']';
        // also ensure that for the PodcastIndex extension entries
        $prefix = $this->xpath_query_rss;
        /** @psalm-var mixed $extension */
        foreach ($this->extensions as $extension) {
            if ($extension instanceof Entry) {
                $extension->set_xpath_prefix($prefix);
            }
        }
    }
    public function get_status(): ?string
    {
        if (array_key_exists('status', $this->data)) {
            /** @psalm-var string */
            return $this->data['status'];
        }
        $status = null;
        /** @var string $prefix*/
        $prefix = $this->get_xpath_prefix();
        $node_list = $this->xpath->query($prefix . '/@status');
        if ($node_list->length > 0) {
            $node = $node_list->item(0);
            assert($node instanceof Dom_Attr);
            $status = $node->value;
        }
        $this->data['status'] = $status;
        return $this->data['status'];
    }
    public function get_start(): ?string
    {
        if (array_key_exists('start', $this->data)) {
            /** @psalm-var string */
            return $this->data['start'];
        }
        $start = null;
        /** @var string $prefix*/
        $prefix = $this->get_xpath_prefix();
        $node_list = $this->xpath->query($prefix . '/@start');
        if ($node_list->length > 0) {
            $node = $node_list->item(0);
            assert($node instanceof Dom_Attr);
            $start = $node->value;
        }
        $this->data['start'] = $start;
        return $this->data['start'];
    }
    public function get_end(): ?string
    {
        if (array_key_exists('end', $this->data)) {
            /** @psalm-var string */
            return $this->data['end'];
        }
        $end = null;
        /** @var string $prefix*/
        $prefix = $this->get_xpath_prefix();
        $node_list = $this->xpath->query($prefix . '/@end');
        if ($node_list->length > 0) {
            $node = $node_list->item(0);
            assert($node instanceof Dom_Attr);
            $end = $node->value;
        }
        $this->data['end'] = $end;
        return $this->data['end'];
    }
    /**
     * Register PodcastIndex namespace
     */
    protected function register_namespaces(): void
    {
        $this->get_xpath()->register_namespace('podcast', 'https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/1.0.md');
    }
}