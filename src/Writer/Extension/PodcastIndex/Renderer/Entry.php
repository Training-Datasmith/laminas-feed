<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Podcast_Index\Renderer;

use function assert;
use Dom_Document;
use Dom_Element;
use Laminas\Feed\Writer\Entry as EntryWriter;
use Laminas\Feed\Writer\Extension;
use Laminas\Feed\Writer\Extension\Podcast_Index\Validator;
/**
 * Renders PodcastIndex data of an entry in a RSS Feed
 *
 * @psalm-import-type TranscriptArray from Validator
 * @psalm-import-type ChaptersArray from Validator
 * @psalm-import-type SoundbiteArray from Validator
 * @psalm-import-type LicenseArray from Validator
 * @psalm-import-type LocationArray from Validator
 * @psalm-import-type TxtArray from Validator
 * @psalm-import-type PersonArray from Validator
 * @psalm-import-type ValueRecipientArray from Validator
 * @psalm-import-type ValueArray from Validator
 * @psalm-import-type DetailedImageArray from Validator
 * @psalm-import-type SocialInteractArray from Validator
 * @psalm-import-type SeasonArray from Validator
 * @psalm-import-type EpisodeArray from Validator
 * @psalm-import-type SourceArray from Validator
 * @psalm-import-type IntegrityArray from Validator
 * @psalm-import-type AlternateEnclosureArray from Validator
 * @psalm-import-type ContentLinkArray from Validator
 * @psalm-import-type FundingArray from Validator
 * @psalm-import-type ChatArray from Validator
 */
class Entry extends Extension\Abstract_Renderer
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
     * Render entry
     */
    public function render(): void
    {
        $this->set_transcript($this->dom, $this->base);
        $this->set_chapters($this->dom, $this->base);
        $this->set_soundbites($this->dom, $this->base);
        $this->set_locations($this->dom, $this->base);
        $this->set_license($this->dom, $this->base);
        $this->set_people($this->dom, $this->base);
        $this->set_txts($this->dom, $this->base);
        $this->set_social_interacts($this->dom, $this->base);
        $this->set_values($this->dom, $this->base);
        $this->set_season($this->dom, $this->base);
        $this->set_episode($this->dom, $this->base);
        $this->set_alternate_enclosures($this->dom, $this->base);
        $this->set_detailed_images($this->dom, $this->base);
        $this->set_content_links($this->dom, $this->base);
        $this->set_fundings($this->dom, $this->base);
        $this->set_chat($this->dom, $this->base);
        if ($this->called) {
            $this->_append_namespaces();
        }
    }
    /**
     * Append namespaces to entry root
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _append_namespaces(): void
    {
        $this->get_root_element()->set_attribute('xmlns:podcast', 'https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/1.0.md');
    }
    private function get_entry_writer(): Entry_Writer
    {
        $container = $this->get_data_container();
        assert($container instanceof Entry_Writer);
        return $container;
    }
    /**
     * Set entry transcript
     */
    protected function set_transcript(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
        /** @psalm-var null|TranscriptArray $transcript */
        $transcript = $container->get_podcast_index_transcript();
        if ($transcript === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $transcript, 'transcript');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry chapters
     */
    protected function set_chapters(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
        /** @psalm-var null|ChaptersArray $chapters */
        $chapters = $container->get_podcast_index_chapters();
        if ($chapters === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $chapters, 'chapters');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry soundbites
     */
    protected function set_soundbites(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
        /** @psalm-var null|list<SoundbiteArray> $soundbites */
        $soundbites = $container->get_podcast_index_soundbites();
        if (!$soundbites) {
            return;
        }
        foreach ($soundbites as $soundbite) {
            $el = Element_Generator::create_podcast_index_element($dom, $soundbite, 'soundbite', 'title');
            $root->append_child($el);
            $this->called = true;
        }
    }
    /**
     * Set multiple location tags
     */
    protected function set_locations(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
     * Set feed license
     */
    private function set_license(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
     * Set feed people
     */
    private function set_people(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
     * Set entry txts
     */
    private function set_txts(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
     * Set feed social interacts
     */
    private function set_social_interacts(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
     * Set the values
     */
    private function set_values(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
            foreach ($value['valueRecipients'] as $recipient) {
                $recipient_element = Element_Generator::create_podcast_index_element($dom, $recipient, 'valueRecipient');
                $value_element->append_child($recipient_element);
            }
            if (isset($value['valueTimeSplits'])) {
                foreach ($value['valueTimeSplits'] as $split) {
                    $split_element = Element_Generator::create_podcast_index_element($dom, $split, 'valueTimeSplit');
                    // set 1-n child nodes: valueRecipients
                    if (isset($split['valueRecipients'])) {
                        foreach ($split['valueRecipients'] as $recip) {
                            $element = Element_Generator::create_podcast_index_element($dom, $recip, 'valueRecipient');
                            $split_element->append_child($element);
                        }
                    }
                    // set 1 child node: value remote item
                    if (isset($split['remoteItem'])) {
                        $el = Element_Generator::create_podcast_index_element($dom, $split['remoteItem'], 'remoteItem');
                        $split_element->append_child($el);
                    }
                    $value_element->append_child($split_element);
                }
            }
            $root->append_child($value_element);
        }
        $this->called = true;
    }
    /**
     * Set entry season
     */
    protected function set_season(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
        /** @psalm-var null|SeasonArray $season */
        $season = $container->get_podcast_index_season();
        if ($season === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $season, 'season', 'value');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set entry episode
     */
    protected function set_episode(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
        /** @psalm-var null|EpisodeArray $episode */
        $episode = $container->get_podcast_index_episode();
        if ($episode === null) {
            return;
        }
        $el = Element_Generator::create_podcast_index_element($dom, $episode, 'episode', 'value');
        $root->append_child($el);
        $this->called = true;
    }
    /**
     * Set the alternate enclosures
     */
    private function set_alternate_enclosures(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
        /** @psalm-var list<AlternateEnclosureArray>|null $enclosures */
        $enclosures = $container->get_podcast_index_alternate_enclosures();
        if ($enclosures === null || $enclosures === []) {
            return;
        }
        foreach ($enclosures as $enclosure) {
            if (!isset($enclosure['sources'])) {
                continue;
            }
            $enclosure_element = Element_Generator::create_podcast_index_element($dom, $enclosure, 'alternateEnclosure');
            foreach ($enclosure['sources'] as $source) {
                $el = Element_Generator::create_podcast_index_element($dom, $source, 'source');
                $enclosure_element->append_child($el);
            }
            if (isset($enclosure['integrity'])) {
                $el = Element_Generator::create_podcast_index_element($dom, $enclosure['integrity'], 'integrity');
                $enclosure_element->append_child($el);
            }
            $root->append_child($enclosure_element);
        }
        $this->called = true;
    }
    /**
     * Set episode detailed images
     */
    private function set_detailed_images(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
     * Set episode content links
     */
    private function set_content_links(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
        /** @psalm-var list<ContentLinkArray>|null $contentLinks */
        $content_links = $container->get_podcast_index_content_links();
        if ($content_links === null || $content_links === []) {
            return;
        }
        foreach ($content_links as $content_link) {
            $el = Element_Generator::create_podcast_index_element($dom, $content_link, 'contentLink', 'description');
            $root->append_child($el);
        }
        $this->called = true;
    }
    /**
     * Set episode funding
     */
    protected function set_fundings(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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
     * Set chat element
     */
    private function set_chat(Dom_Document $dom, Dom_Element $root): void
    {
        $container = $this->get_entry_writer();
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