<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Podcast_Index\Renderer;

use function assert;
use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer\Extension;
use Laminas\Feed\Writer\Extension\Podcast_Index;
use Laminas\Feed\Writer\Feed as FeedWriter;
/**
 * Renders PodcastIndex data of a RSS Feed
 *
 * @psalm-import-type FundingArray from PodcastIndex\Validator
 * @psalm-import-type LicenseArray from PodcastIndex\Validator
 * @psalm-import-type LocationArray from PodcastIndex\Validator
 * @psalm-import-type BlockArray from PodcastIndex\Validator
 * @psalm-import-type TxtArray from PodcastIndex\Validator
 * @psalm-import-type PersonArray from PodcastIndex\Validator
 * @psalm-import-type UpdateFrequencyArray from PodcastIndex\Validator
 * @psalm-import-type TrailerArray from PodcastIndex\Validator
 * @psalm-import-type RemoteItemArray from PodcastIndex\Validator
 * @psalm-import-type ValueRecipientArray from PodcastIndex\Validator
 * @psalm-import-type ValueArray from PodcastIndex\Validator
 * @psalm-import-type ImagesArray from PodcastIndex\Validator
 * @psalm-import-type DetailedImageArray from PodcastIndex\Validator
 * @psalm-import-type SocialInteractArray from PodcastIndex\Validator
 * @psalm-import-type LiveItemArray from PodcastIndex\Validator
 * @psalm-import-type ChatArray from PodcastIndex\Validator
 */
class Feed extends Extension\Abstract_Renderer
{
    /**
     * Set to TRUE if a rendering method actually renders something. This
     * is used to prevent premature appending of a XML namespace declaration
     * until an element which requires it is actually appended.
     *
     * @var bool
     */
    protected $called = false;
    /**
     * Render feed
     */
    public function render(): void
    {
        $this->set_locked($this->dom, $this->base);
        $this->set_funding($this->dom, $this->base);
        $this->set_fundings($this->dom, $this->base);
        $this->set_license($this->dom, $this->base);
        $this->set_location($this->dom, $this->base);
        $this->set_locations($this->dom, $this->base);
        $this->set_images($this->dom, $this->base);
        $this->set_detailed_images($this->dom, $this->base);
        $this->set_update_frequency($this->dom, $this->base);
        $this->set_people($this->dom, $this->base);
        $this->set_trailer($this->dom, $this->base);
        $this->set_guid($this->dom, $this->base);
        $this->set_medium($this->dom, $this->base);
        $this->set_blocks($this->dom, $this->base);
        $this->set_txts($this->dom, $this->base);
        $this->set_podping($this->dom, $this->base);
        $this->set_remote_items($this->dom, $this->base);
        $this->set_podroll($this->dom, $this->base);
        $this->set_publisher($this->dom, $this->base);
        $this->set_values($this->dom, $this->base);
        $this->set_social_interacts($this->dom, $this->base);
        $this->set_chat($this->dom, $this->base);
        /** @var FeedWriter $feedWriter */
        $feed_writer = $this->get_data_container();
        /** @var list<PodcastIndex\LiveItem> $liveItems */
        $live_items = $feed_writer->get_podcast_index_live_items();
        if ($live_items) {
            foreach ($live_items as $live_item) {
                $encoding = $feed_writer->get_encoding();
                if ($encoding) {
                    $live_item->set_encoding($encoding);
                }
                $renderer = new Live_Item($live_item, $this->dom, $this->base);
                $renderer->set_type($this->get_type());
                $renderer->set_root_element($this->dom->document_element);
                $renderer->render();
                $element = $renderer->get_element();
                $imported = $this->dom->import_node($element, true);
                $this->base->append_child($imported);
            }
        }
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    /**
     * Append feed namespaces
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _append_namespaces(): void
    {
        $this->get_root_element()->set_attribute('xmlns:podcast', 'https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/1.0.md');
    }
    private function get_feed_writer(): Feed_Writer
    {
        $container = $this->get_data_container();
        assert($container instanceof Feed_Writer);
        return $container;
    }
    /**
     * Set feed lock
     */
    protected function set_locked(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|array<string, string> $locked */
        $locked = $container->get_podcast_index_locked();
        if ($locked === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $locked, 'locked', 'value');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set a single feed funding tag
     */
    protected function set_funding(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|FundingArray $funding */
        $funding = $container->get_podcast_index_funding();
        if ($funding === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $funding, 'funding', 'title');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set multiple funding tags
     */
    protected function set_fundings(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|list<FundingArray> $fundings */
        $fundings = $container->get_podcast_index_fundings();
        if ($fundings === null) {
            return;
        }
        foreach ($fundings as $funding) {
            $el = Element_Generator::create_podcast_index_element($dom, $funding, 'funding', 'title');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set feed license
     */
    private function set_license(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|LicenseArray $license */
        $license = $container->get_podcast_index_license();
        if ($license === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $license, 'license', 'identifier');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set a single feed location
     */
    private function set_location(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|LocationArray $location */
        $location = $container->get_podcast_index_location();
        if ($location === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $location, 'location', 'description');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set multiple location tags
     */
    protected function set_locations(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|list<LocationArray> $locations */
        $locations = $container->get_podcast_index_locations();
        if ($locations === null) {
            return;
        }
        foreach ($locations as $location) {
            $el = Element_Generator::create_podcast_index_element($dom, $location, 'location', 'description');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set feed images srcset
     */
    private function set_images(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|ImagesArray $images */
        $images = $container->get_podcast_index_images();
        if ($images === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $images, 'images');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed detailed images
     */
    private function set_detailed_images(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var list<DetailedImageArray>|null $detailedImages */
        $detailed_images = $container->get_podcast_index_detailed_images();
        if ($detailed_images === null || $detailed_images === []) {
            return;
        }
        foreach ($detailed_images as $detailed_image) {
            $el = Element_Generator::create_podcast_index_element($dom, $detailed_image, 'image');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set feed update frequency
     */
    private function set_update_frequency(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|UpdateFrequencyArray $updateFrequency */
        $update_frequency = $container->get_podcast_index_update_frequency();
        if ($update_frequency === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $update_frequency, 'updateFrequency', 'description');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed people
     */
    private function set_people(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|list<PersonArray> $people */
        $people = $container->get_podcast_index_people();
        if ($people === null || $people === []) {
            return;
        }
        foreach ($people as $person) {
            $el = Element_Generator::create_podcast_index_element($dom, $person, 'person', 'name');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set feed trailer
     */
    private function set_trailer(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|TrailerArray $trailer */
        $trailer = $container->get_podcast_index_trailer();
        if ($trailer === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $trailer, 'trailer', 'title');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed guid
     */
    private function set_guid(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|array{value: string} $guid */
        $guid = $container->get_podcast_index_guid();
        if ($guid === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $guid, 'guid', 'value');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed medium
     */
    private function set_medium(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|array{value: string} $medium */
        $medium = $container->get_podcast_index_medium();
        if ($medium === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $medium, 'medium', 'value');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed blocks
     */
    private function set_blocks(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var list<BlockArray>|null $blocks */
        $blocks = $container->get_podcast_index_blocks();
        if ($blocks === null || $blocks === []) {
            return;
        }
        foreach ($blocks as $block) {
            $el = Element_Generator::create_podcast_index_element($dom, $block, 'block', 'value');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set feed txts
     */
    private function set_txts(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var list<TxtArray>|null $txts */
        $txts = $container->get_podcast_index_txts();
        if ($txts === null || $txts === []) {
            return;
        }
        foreach ($txts as $txt) {
            $el = Element_Generator::create_podcast_index_element($dom, $txt, 'txt', 'value');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set feed podping
     */
    private function set_podping(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var null|array{usesPodping: bool} $podping */
        $podping = $container->get_podcast_index_podping();
        if ($podping === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $podping, 'podping');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set feed remote items
     */
    private function set_remote_items(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var list<RemoteItemArray>|null $remoteItems */
        $remote_items = $container->get_podcast_index_remote_items();
        if ($remote_items === null || $remote_items === []) {
            return;
        }
        foreach ($remote_items as $remote_item) {
            $el = Element_Generator::create_podcast_index_element($dom, $remote_item, 'remoteItem');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set podroll element with remote items
     */
    private function set_podroll(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var list<RemoteItemArray>|null $podrollItems */
        $podroll_items = $container->get_podcast_index_podroll();
        if ($podroll_items === null || $podroll_items === []) {
            return;
        }
        $podroll = $dom->create_element('podcast:podroll');
        foreach ($podroll_items as $remote_item) {
            $el = Element_Generator::create_podcast_index_element($dom, $remote_item, 'remoteItem');
            $podroll->append_child($el);
        }
        $root->append_child($podroll);
        $this->called = true;
    }
    /**
     * Set publisher element with remote items
     */
    private function set_publisher(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var RemoteItemArray|null $publisherItem */
        $publisher_item = $container->get_podcast_index_publisher();
        if ($publisher_item === null) {
            return;
        }
        $publisher = $dom->create_element('podcast:publisher');
        $el = Element_Generator::create_podcast_index_element($dom, $publisher_item, 'remoteItem');
        $publisher->append_child($el);
        $root->append_child($publisher);
        $this->called = true;
    }
    /**
     * Set values with the valueRecipients
     */
    private function set_values(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var list<ValueArray>|null $values */
        $values = $container->get_podcast_index_values();
        if ($values === null || $values === []) {
            return;
        }
        foreach ($values as $value) {
            if (!isset($value['valueRecipients'])) {
                continue;
            }
            $value_element = Element_Generator::create_podcast_index_element($dom, $value, 'value');
            foreach ($value['valueRecipients'] as $value_recipient) {
                $el = Element_Generator::create_podcast_index_element($dom, $value_recipient, 'valueRecipient');
                $value_element->append_child($el);
            }
            $root->append_child($value_element);
        }
        $this->called = true;
    }
    /**
     * Set feed social interacts
     */
    private function set_social_interacts(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var list<SocialInteractArray>|null $socialInteracts */
        $social_interacts = $container->get_podcast_index_social_interacts();
        if ($social_interacts === null || $social_interacts === []) {
            return;
        }
        foreach ($social_interacts as $social_interact) {
            $el = Element_Generator::create_podcast_index_element($dom, $social_interact, 'socialInteract');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set chat element
     */
    private function set_chat(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_feed_writer();
        /** @psalm-var ChatArray|null $chat */
        $chat = $container->get_podcast_index_chat();
        if ($chat === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $chat, 'chat');
        $root->append_child($el);
        $this->called = true;
    }
}