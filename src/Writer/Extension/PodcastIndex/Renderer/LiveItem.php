<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Podcast_Index\Renderer;

use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer\Extension\Podcast_Index\Live_Item as LiveItemWriter;
use Laminas\Feed\Writer\Renderer\Entry;
use Laminas\Feed\Writer\Writer;
/**
 * Renders PodcastIndex LiveItem data in a RSS Feed
 */
final class Live_Item extends Entry\Rss
{
    public function __construct(Live_Item_Writer $container, Dom_Document $dom, Dom_Element $root_element)
    {
        parent::__construct($container);
        $this->dom = $dom;
        $this->root_element = $root_element;
    }
    /**
     * Render live item
     */
    public function render(): self
    {
        /** @psalm-var string $encoding */
        $encoding = $this->container->get_encoding();
        $this->dom = new Dom_Document('1.0', $encoding);
        $this->dom->format_output = true;
        $this->dom->substitute_entities = false;
        /** @psalm-var LiveItemWriter $liveItemWriter */
        $live_item_writer = $this->get_data_container();
        $attributes = ['status' => $live_item_writer->get_status(), 'start' => $live_item_writer->get_start(), 'end' => $live_item_writer->get_end()];
        $live_item = Element_Generator::create_podcast_index_element($this->dom, $attributes, 'liveItem');
        $this->dom->append_child($live_item);
        $this->_set_title($this->dom, $live_item);
        $this->_set_description($this->dom, $live_item);
        $this->_set_date_created($this->dom, $live_item);
        $this->_set_date_modified($this->dom, $live_item);
        $this->_set_link($this->dom, $live_item);
        $this->_set_id($this->dom, $live_item);
        $this->_set_authors($this->dom, $live_item);
        $this->_set_enclosure($this->dom, $live_item);
        $this->_set_comment_link($this->dom, $live_item);
        $this->_set_categories($this->dom, $live_item);
        foreach ($this->extensions as $ext) {
            $ext->set_type($this->get_type());
            $ext->set_root_element($this->get_root_element());
            $ext->set_dom_document($this->get_dom_document(), $live_item);
            $ext->render();
        }
        return $this;
    }
    /**
     * Load extensions from Laminas\Feed\Writer\Entry
     * Override abstract renderer method to only fetch entry extensions
     */
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    protected function _load_extensions(): void
    {
        Writer::register_core_extensions();
        $manager = Writer::get_extension_manager();
        $all = Writer::get_extensions();
        /** @var array<array-key,string> $exts */
        $exts = $all['entryRenderer'];
        foreach ($exts as $extension) {
            /** @var mixed $plugin */
            $plugin = $manager->get($extension);
            $plugin->set_data_container($this->get_data_container());
            $plugin->set_encoding($this->get_encoding());
            $this->extensions[$extension] = $plugin;
        }
    }
}