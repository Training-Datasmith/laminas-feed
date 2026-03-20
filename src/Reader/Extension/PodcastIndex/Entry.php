<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Podcast_Index;

use function array_key_exists;
use function assert;
use Dom_Element;
use Laminas\Feed\Reader\Extension;
use stdClass;
/**
 * Describes PodcastIndex data of an entry in a RSS Feed
 *
 * @psalm-import-type FundingObject from AttributesReader
 * @psalm-import-type LicenseObject from AttributesReader
 * @psalm-import-type LocationObject from AttributesReader
 * @psalm-import-type BlockObject from AttributesReader
 * @psalm-import-type TxtObject from AttributesReader
 * @psalm-import-type PersonObject from AttributesReader
 * @psalm-import-type UpdateFrequencyObject from AttributesReader
 * @psalm-import-type TrailerObject from AttributesReader
 * @psalm-import-type RemoteItemObject from AttributesReader
 * @psalm-import-type ValueRecipientObject from AttributesReader
 * @psalm-import-type ValueTimeSplitObject from AttributesReader
 * @psalm-import-type ValueObject from AttributesReader
 * @psalm-import-type DetailedImageObject from AttributesReader
 * @psalm-import-type SocialInteractObject from AttributesReader
 * @psalm-import-type TranscriptObject from AttributesReader
 * @psalm-import-type ChaptersObject from AttributesReader
 * @psalm-import-type SoundbiteObject from AttributesReader
 * @psalm-import-type SeasonObject from AttributesReader
 * @psalm-import-type EpisodeObject from AttributesReader
 * @psalm-import-type SourceObject from AttributesReader
 * @psalm-import-type IntegrityObject from AttributesReader
 * @psalm-import-type AlternateEnclosureObject from AttributesReader
 * @psalm-import-type ContentLinkObject from AttributesReader
 * @psalm-import-type ChatObject from AttributesReader
 */
class Entry extends Extension\Abstract_Entry
{
    /**
     * Get the entry transcript
     *
     * @return null|TranscriptObject
     */
    public function get_transcript(): ?stdClass
    {
        if (array_key_exists('transcript', $this->data)) {
            /** @psalm-var null|TranscriptObject */
            return $this->data['transcript'];
        }
        $transcript = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:transcript');
        if ($node_list->length > 0) {
            $node = $node_list->item(0);
            assert($node instanceof Dom_Element);
            $transcript = new stdClass();
            $transcript->url = $node->get_attribute('url');
            $transcript->type = $node->get_attribute('type');
            $transcript->language = $node->get_attribute('language');
            $transcript->rel = $node->get_attribute('rel');
        }
        $this->data['transcript'] = $transcript;
        return $this->data['transcript'];
    }
    /**
     * Get the entry transcript
     *
     * @return null|TranscriptObject
     */
    public function get_podcast_index_transcript(): object|null
    {
        return $this->get_transcript();
    }
    /**
     * Get the entry chapters
     *
     * @return null|ChaptersObject
     */
    public function get_chapters(): ?stdClass
    {
        if (array_key_exists('chapters', $this->data)) {
            /** @psalm-var null|ChaptersObject */
            return $this->data['chapters'];
        }
        $chapters = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:chapters');
        if ($node_list->length > 0) {
            $node = $node_list->item(0);
            assert($node instanceof Dom_Element);
            $chapters = new stdClass();
            $chapters->url = $node->get_attribute('url');
            $chapters->type = $node->get_attribute('type');
        }
        $this->data['chapters'] = $chapters;
        return $this->data['chapters'];
    }
    /**
     * Get the entry chapters
     *
     * @return null|ChaptersObject
     */
    public function get_podcast_index_chapters(): object|null
    {
        return $this->get_chapters();
    }
    /**
     * Get the entry soundbites
     *
     * @return list<SoundbiteObject>
     */
    public function get_soundbites(): array
    {
        if (array_key_exists('soundbites', $this->data)) {
            /** @psalm-var list<SoundbiteObject> */
            return $this->data['soundbites'];
        }
        $soundbites = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:soundbite');
        if ($node_list->length > 0) {
            foreach ($node_list as $node) {
                /** @var DOMElement $node */
                $soundbite = new stdClass();
                $soundbite->title = $node->node_value;
                $soundbite->start_time = $node->get_attribute('startTime');
                $soundbite->duration = $node->get_attribute('duration');
                $soundbites[] = $soundbite;
            }
        }
        $this->data['soundbites'] = $soundbites;
        return $this->data['soundbites'];
    }
    /**
     * Get the entry soundbites
     */
    public function get_podcast_index_soundbites(): array
    {
        return $this->get_soundbites();
    }
    /**
     * Get the episode locations
     *
     * @psalm-return list<LocationObject>
     */
    public function get_podcast_index_locations(): array
    {
        if (array_key_exists('locations', $this->data)) {
            /** @psalm-var list<LocationObject> */
            return $this->data['locations'];
        }
        $locations = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:location');
        if ($node_list->length > 0) {
            foreach ($node_list as $entry) {
                assert($entry instanceof Dom_Element);
                $location = Attributes_Reader::read_location($entry);
                $locations[] = $location;
            }
        }
        $this->data['locations'] = $locations;
        return $this->data['locations'];
    }
    /**
     * Get the entry license
     *
     * @return null|LicenseObject
     */
    public function get_podcast_index_license(): object|null
    {
        if (array_key_exists('license', $this->data)) {
            /** @psalm-var null|LicenseObject */
            return $this->data['license'];
        }
        $license = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:license');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $license = Attributes_Reader::read_license($item);
        }
        $this->data['license'] = $license;
        return $this->data['license'];
    }
    /**
     * Get the entry people
     *
     * @return list<PersonObject>
     */
    public function get_podcast_index_people(): array
    {
        if (array_key_exists('people', $this->data)) {
            /** @psalm-var list<PersonObject> */
            return $this->data['people'];
        }
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:person');
        $person_collection = [];
        if ($node_list->length > 0) {
            foreach ($node_list as $entry) {
                assert($entry instanceof Dom_Element);
                $person = Attributes_Reader::read_person($entry);
                $person_collection[] = $person;
            }
        }
        $this->data['people'] = $person_collection;
        return $this->data['people'];
    }
    /**
     * Get the entry persons (alias of getPodcastIndexPeople)
     *
     * @return list<PersonObject>
     */
    public function get_podcast_index_persons(): array
    {
        return $this->get_podcast_index_people();
    }
    /**
     * Get the entry txts
     *
     * @return list<TxtObject>
     */
    public function get_podcast_index_txts(): array
    {
        if (array_key_exists('txts', $this->data)) {
            /** @psalm-var list<TxtObject> */
            return $this->data['txts'];
        }
        $txts = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:txt');
        foreach ($node_list as $entry) {
            assert($entry instanceof Dom_Element);
            $object = Attributes_Reader::read_txt($entry);
            $txts[] = $object;
        }
        $this->data['txts'] = $txts;
        return $this->data['txts'];
    }
    /**
     * Get the entry social interacts
     *
     * @return list<SocialInteractObject>
     */
    public function get_podcast_index_social_interacts(): array
    {
        if (array_key_exists('socialInteracts', $this->data)) {
            /** @var list<SocialInteractObject> $socialInteracts */
            $social_interacts = $this->data['socialInteracts'];
            return $social_interacts;
        }
        $social_interacts = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:socialInteract');
        foreach ($node_list as $entry) {
            assert($entry instanceof Dom_Element);
            $object = Attributes_Reader::read_social_interact($entry);
            $social_interacts[] = $object;
        }
        $this->data['socialInteracts'] = $social_interacts;
        return $this->data['socialInteracts'];
    }
    /**
     * Get the entry values
     *
     * @return list<ValueObject>
     */
    public function get_podcast_index_values(): array
    {
        if (array_key_exists('values', $this->data)) {
            /** @var list<ValueObject> $values */
            $values = $this->data['values'];
            return $values;
        }
        $values = [];
        $values_node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:value');
        foreach ($values_node_list as $value_node) {
            assert($value_node instanceof Dom_Element);
            $value_object = Attributes_Reader::read_value($value_node);
            $value_recipients_node_list = $this->xpath->query('podcast:valueRecipient', $value_node);
            $value_recipients = [];
            foreach ($value_recipients_node_list as $entry) {
                assert($entry instanceof Dom_Element);
                $object = Attributes_Reader::read_value_recipient($entry);
                $value_recipients[] = $object;
            }
            $value_object->value_recipients = $value_recipients;
            $time_splits_node_list = $this->xpath->query('podcast:valueTimeSplit', $value_node);
            if ($time_splits_node_list->length > 0) {
                $value_time_splits = [];
                foreach ($time_splits_node_list as $entry) {
                    assert($entry instanceof Dom_Element);
                    $object = $this->get_value_time_split($entry);
                    $value_time_splits[] = $object;
                }
                $value_object->value_time_splits = $value_time_splits;
            }
            $values[] = $value_object;
        }
        $this->data['values'] = $values;
        return $this->data['values'];
    }
    /**
     * Get value time split
     *
     * @return ValueTimeSplitObject
     */
    private function get_value_time_split(Dom_Element $entry): object
    {
        $object = Attributes_Reader::read_value_time_split($entry);
        $items_node_list = $this->xpath->query('podcast:remoteItem', $entry);
        if ($items_node_list->length > 0) {
            assert($items_node_list[0] instanceof Dom_Element);
            $items_object = Attributes_Reader::read_remote_item($items_node_list[0]);
            $object->remote_item = $items_object;
        }
        $recipients_node_list = $this->xpath->query('podcast:valueRecipient', $entry);
        if ($recipients_node_list->length > 0) {
            $value_recipients = [];
            foreach ($recipients_node_list as $node) {
                assert($node instanceof Dom_Element);
                $recipient_object = Attributes_Reader::read_value_recipient($node);
                $value_recipients[] = $recipient_object;
            }
            $object->value_recipients = $value_recipients;
        }
        return $object;
    }
    /**
     * Get the entry season
     *
     * @return null|SeasonObject
     */
    public function get_podcast_index_season(): object|null
    {
        if (array_key_exists('season', $this->data)) {
            /** @psalm-var SeasonObject */
            return $this->data['season'];
        }
        $season = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:season');
        if ($node_list->length > 0) {
            $node = $node_list->item(0);
            assert($node instanceof Dom_Element);
            $season = new stdClass();
            $season->value = $node->node_value;
            $season->name = $node->get_attribute('name');
        }
        $this->data['season'] = $season;
        return $this->data['season'];
    }
    /**
     * Get the entry episode
     *
     * @return null|EpisodeObject
     */
    public function get_podcast_index_episode(): object|null
    {
        if (array_key_exists('episode', $this->data)) {
            /** @psalm-var EpisodeObject */
            return $this->data['episode'];
        }
        $episode = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:episode');
        if ($node_list->length > 0) {
            $node = $node_list->item(0);
            assert($node instanceof Dom_Element);
            $episode = new stdClass();
            $episode->value = $node->node_value;
            $episode->display = $node->get_attribute('display');
        }
        $this->data['episode'] = $episode;
        return $this->data['episode'];
    }
    /**
     * Get the entry alternateEnclosures
     *
     * @return list<AlternateEnclosureObject>
     */
    public function get_podcast_index_alternate_enclosures(): array
    {
        if (array_key_exists('alternateEnclosures', $this->data)) {
            /** @var list<AlternateEnclosureObject> $enclosures */
            $enclosures = $this->data['alternateEnclosures'];
            return $enclosures;
        }
        $enclosures = [];
        $enclosures_node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:alternateEnclosure');
        foreach ($enclosures_node_list as $enclosure_node) {
            assert($enclosure_node instanceof Dom_Element);
            $enclosure_object = Attributes_Reader::read_alternate_enclosure($enclosure_node);
            $sources_node_list = $this->xpath->query('podcast:source', $enclosure_node);
            $sources = [];
            if ($sources_node_list->length > 0) {
                foreach ($sources_node_list as $entry) {
                    assert($entry instanceof Dom_Element);
                    $object = Attributes_Reader::read_source($entry);
                    $sources[] = $object;
                }
                $enclosure_object->sources = $sources;
            }
            $integrity_node_list = $this->xpath->query('podcast:integrity', $enclosure_node);
            if ($integrity_node_list->length > 0) {
                $node = $integrity_node_list->item(0);
                assert($node instanceof Dom_Element);
                $integrity = Attributes_Reader::read_integrity($node);
                $enclosure_object->integrity = $integrity;
            }
            $enclosures[] = $enclosure_object;
        }
        $this->data['alternateEnclosures'] = $enclosures;
        return $this->data['alternateEnclosures'];
    }
    /**
     * Get the episode detailed images.
     * Returns the contents of one or more `<podcast:image>` tags.
     *
     * @return list<DetailedImageObject>
     */
    public function get_podcast_index_detailed_images(): array
    {
        if (array_key_exists('detailedImages', $this->data)) {
            /** @psalm-var list<DetailedImageObject> */
            return $this->data['detailedImages'];
        }
        $images = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:image');
        if ($node_list->length > 0) {
            foreach ($node_list as $entry) {
                assert($entry instanceof Dom_Element);
                $image = Attributes_Reader::read_detailed_image($entry);
                $images[] = $image;
            }
        }
        $this->data['detailedImages'] = $images;
        return $this->data['detailedImages'];
    }
    /**
     * Get the episode content links.
     *
     * @psalm-return list<ContentLinkObject>
     */
    public function get_podcast_index_content_links(): array
    {
        if (array_key_exists('contentLinks', $this->data)) {
            /** @psalm-var list<ContentLinkObject> */
            return $this->data['contentLinks'];
        }
        $content_links = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:contentLink');
        if ($node_list->length > 0) {
            foreach ($node_list as $entry) {
                assert($entry instanceof Dom_Element);
                $content_link = Attributes_Reader::read_content_link($entry);
                $content_links[] = $content_link;
            }
        }
        $this->data['contentLinks'] = $content_links;
        return $this->data['contentLinks'];
    }
    /**
     * Get the episode fundings
     *
     * @psalm-return list<FundingObject>
     */
    public function get_podcast_index_fundings(): array
    {
        if (array_key_exists('fundings', $this->data)) {
            /** @psalm-var list<FundingObject> */
            return $this->data['fundings'];
        }
        $fundings = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:funding');
        if ($node_list->length > 0) {
            foreach ($node_list as $entry) {
                assert($entry instanceof Dom_Element);
                $funding = Attributes_Reader::read_funding($entry);
                $fundings[] = $funding;
            }
        }
        $this->data['fundings'] = $fundings;
        return $this->data['fundings'];
    }
    /**
     * Get the podcast chat
     *
     * @return null|ChatObject
     */
    public function get_podcast_index_chat(): object|null
    {
        if (array_key_exists('chat', $this->data)) {
            /** @psalm-var null|ChatObject */
            return $this->data['chat'];
        }
        $object = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:chat');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $object = Attributes_Reader::read_chat($item);
        }
        $this->data['chat'] = $object;
        return $this->data['chat'];
    }
    /**
     * Register PodcastIndex namespace
     */
    protected function register_namespaces(): void
    {
        $this->xpath->register_namespace('podcast', 'https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/1.0.md');
    }
}