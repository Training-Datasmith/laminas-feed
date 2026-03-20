<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Podcast_Index;

use function array_key_exists;
use function assert;
use Dom_Element;
use Laminas\Feed\Reader\Extension;
use Laminas\Feed\Reader\Extension\Podcast_Index\Live_Item as LiveItemReader;
use stdClass;
/**
 * Describes PodcastIndex data of a RSS Feed
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
 * @psalm-import-type ValueObject from AttributesReader
 * @psalm-import-type ImagesObject from AttributesReader
 * @psalm-import-type DetailedImageObject from AttributesReader
 * @psalm-import-type SocialInteractObject from AttributesReader
 * @psalm-import-type ChatObject from AttributesReader
 */
class Feed extends Extension\Abstract_Feed
{
    /**
     * Is the podcast locked (not available for indexing)?
     */
    public function is_locked(): bool
    {
        return $this->is_podcast_index_locked();
    }
    /**
     * Is the podcast locked (not available for indexing)?
     */
    public function is_podcast_index_locked(): bool
    {
        if (isset($this->data['locked'])) {
            return $this->data['locked'];
        }
        $locked = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/podcast:locked)');
        if (!$locked) {
            $locked = false;
        }
        $this->data['locked'] = $locked === 'yes';
        return $this->data['locked'];
    }
    /**
     * Get the owner of the podcast (for verification)
     */
    public function get_lock_owner(): ?string
    {
        return $this->get_podcast_index_lock_owner();
    }
    /**
     * Get the owner of the podcast (for verification)
     */
    public function get_podcast_index_lock_owner(): ?string
    {
        if (isset($this->data['owner'])) {
            return $this->data['owner'];
        }
        $owner = $this->xpath->evaluate('string(' . $this->get_xpath_prefix() . '/podcast:locked/@owner)');
        if (!$owner) {
            $owner = null;
        }
        $this->data['owner'] = $owner;
        return $this->data['owner'];
    }
    /**
     * Get a single feed funding
     *
     * @deprecated Multiple `funding` tags are allowed now. Use `getPodcastIndexFundings()` instead.
     */
    public function get_funding(): object|null
    {
        return $this->get_podcast_index_funding();
    }
    /**
     * Get a single feed funding
     *
     * @deprecated Multiple `funding` tags are allowed now. Use `getPodcastIndexFundings()` instead.
     */
    public function get_podcast_index_funding(): object|null
    {
        if (array_key_exists('funding', $this->data)) {
            /** @psalm-var null|FundingObject */
            return $this->data['funding'];
        }
        $funding = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:funding');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $funding = Attributes_Reader::read_funding($item);
        }
        $this->data['funding'] = $funding;
        return $this->data['funding'];
    }
    /**
     * Get multiple feed fundings
     *
     * @psalm-return list<FundingObject>
     */
    public function get_podcast_index_fundings(): array
    {
        $fundings = [];
        // include deprecated single funding entry if exists
        if (array_key_exists('fundings', $this->data) || array_key_exists('funding', $this->data)) {
            /** @var list<FundingObject> $fundings */
            $fundings = $this->data['fundings'] ?? [];
            if (isset($this->data['funding'])) {
                /** @var FundingObject $single */
                $single = $this->data['funding'];
                $fundings[] = $single;
            }
            return $fundings;
        }
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
     * Get the podcast license
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
     * Get the podcast location
     */
    public function get_podcast_index_location(): object|null
    {
        if (array_key_exists('location', $this->data)) {
            /** @psalm-var null|LocationObject */
            return $this->data['location'];
        }
        $location = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:location');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $location = Attributes_Reader::read_location($item);
        }
        $this->data['location'] = $location;
        return $this->data['location'];
    }
    /**
     * Get multiple feed locations
     *
     * @psalm-return list<LocationObject>
     */
    public function get_podcast_index_locations(): array
    {
        $locations = [];
        // include deprecated single location entry if exists
        if (array_key_exists('locations', $this->data) || array_key_exists('location', $this->data)) {
            /** @var list<LocationObject> $locations */
            $locations = $this->data['locations'] ?? [];
            if (isset($this->data['location'])) {
                /** @var LocationObject $single */
                $single = $this->data['location'];
                $locations[] = $single;
            }
            return $locations;
        }
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
     * Get the podcast images.
     * Returns the content of a single `<podcast:images>` tag.
     *
     * @deprecated
     *
     * @psalm-return null|ImagesObject
     */
    public function get_podcast_index_images(): object|null
    {
        if (array_key_exists('images', $this->data)) {
            /** @psalm-var null|ImagesObject */
            return $this->data['images'];
        }
        $images = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:images');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $images = Attributes_Reader::read_images($item);
        }
        $this->data['images'] = $images;
        return $this->data['images'];
    }
    /**
     * Get the podcast detailed images.
     * Returns the contents of one or more `<podcast:image>` tags.
     *
     * @psalm-return list<DetailedImageObject>
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
     * Get the podcast update frequency
     *
     * @psalm-return null|UpdateFrequencyObject
     */
    public function get_podcast_index_update_frequency(): object|null
    {
        if (array_key_exists('updateFrequency', $this->data)) {
            /** @psalm-var null|UpdateFrequencyObject */
            return $this->data['updateFrequency'];
        }
        $update_frequency = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:updateFrequency');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $update_frequency = Attributes_Reader::read_update_frequency($item);
        }
        $this->data['updateFrequency'] = $update_frequency;
        return $this->data['updateFrequency'];
    }
    /**
     * Get the podcast people
     *
     * @psalm-return list<PersonObject>
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
     * Get the podcast persons (alias of getPodcastIndexPeople)
     *
     * @psalm-return list<PersonObject>
     */
    public function get_podcast_index_persons(): array
    {
        return $this->get_podcast_index_people();
    }
    /**
     * Get the podcast trailer
     *
     * @return null|TrailerObject
     */
    public function get_podcast_index_trailer(): object|null
    {
        if (array_key_exists('trailer', $this->data)) {
            /** @psalm-var null|TrailerObject */
            return $this->data['trailer'];
        }
        $object = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:trailer');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $object = Attributes_Reader::read_trailer($item);
        }
        $this->data['trailer'] = $object;
        return $this->data['trailer'];
    }
    /**
     * Get the podcast guid
     *
     * @return null|object{value: string}
     */
    public function get_podcast_index_guid(): object|null
    {
        if (array_key_exists('guid', $this->data)) {
            /** @psalm-var null|object{value: string} */
            return $this->data['guid'];
        }
        $object = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:guid');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $object = Attributes_Reader::read_guid($item);
        }
        $this->data['guid'] = $object;
        return $this->data['guid'];
    }
    /**
     * Get the podcast medium
     *
     * @return null|object{value: string}
     */
    public function get_podcast_index_medium(): object|null
    {
        if (array_key_exists('medium', $this->data)) {
            /** @psalm-var null|object{value: string} */
            return $this->data['medium'];
        }
        $object = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:medium');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $object = Attributes_Reader::read_medium($item);
        }
        $this->data['medium'] = $object;
        return $this->data['medium'];
    }
    /**
     * Get the podcast blocks
     *
     * @return list<object{value: string, id?: string}>
     */
    public function get_podcast_index_blocks(): array
    {
        if (array_key_exists('blocks', $this->data)) {
            /** @psalm-var list<object{value: string, id?: string}> */
            return $this->data['blocks'];
        }
        $blocks = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:block');
        foreach ($node_list as $entry) {
            assert($entry instanceof Dom_Element);
            $object = Attributes_Reader::read_block($entry);
            $blocks[] = $object;
        }
        $this->data['blocks'] = $blocks;
        return $this->data['blocks'];
    }
    /**
     * Get the podcast txts
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
     * Get the podcast podping
     *
     * @return null|object{usesPodping: bool}
     */
    public function get_podcast_index_podping(): object|null
    {
        if (array_key_exists('podping', $this->data)) {
            /** @psalm-var null|object{usesPodping: bool} */
            return $this->data['podping'];
        }
        $object = null;
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:podping');
        if ($node_list->length > 0) {
            $item = $node_list->item(0);
            assert($item instanceof Dom_Element);
            $object = new stdClass();
            $object->uses_podping = $item->get_attribute('usesPodping') === 'true';
        }
        $this->data['podping'] = $object;
        return $this->data['podping'];
    }
    /**
     * Get the podcast remoteItems
     *
     * @return list<RemoteItemObject>
     */
    public function get_podcast_index_remote_items(): array
    {
        if (array_key_exists('remoteItems', $this->data)) {
            /** @var list<RemoteItemObject> $remoteItems */
            $remote_items = $this->data['remoteItems'];
            return $remote_items;
        }
        $remote_items = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:remoteItem');
        foreach ($node_list as $entry) {
            assert($entry instanceof Dom_Element);
            $object = Attributes_Reader::read_remote_item($entry);
            $remote_items[] = $object;
        }
        $this->data['remoteItems'] = $remote_items;
        return $this->data['remoteItems'];
    }
    /**
     * Get the podcast podroll remote items
     *
     * @return list<RemoteItemObject>
     */
    public function get_podcast_index_podroll(): array
    {
        if (array_key_exists('podroll', $this->data)) {
            /** @var list<RemoteItemObject> $podrollItems */
            $podroll_items = $this->data['podroll'];
            return $podroll_items;
        }
        $podroll_items = [];
        $podroll_node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:podroll');
        if ($podroll_node_list->length > 0) {
            $podroll_node = $podroll_node_list->item(0);
            assert($podroll_node instanceof Dom_Element);
            $remote_items = $this->xpath->query('podcast:remoteItem', $podroll_node);
            foreach ($remote_items as $entry) {
                assert($entry instanceof Dom_Element);
                $object = Attributes_Reader::read_remote_item($entry);
                $podroll_items[] = $object;
            }
        }
        $this->data['podroll'] = $podroll_items;
        return $this->data['podroll'];
    }
    /**
     * Get the podcast publisher remote items
     *
     * @return RemoteItemObject|null
     */
    public function get_podcast_index_publisher(): object|null
    {
        if (array_key_exists('publisher', $this->data)) {
            /** @var null|RemoteItemObject $publisherItem */
            $publisher_item = $this->data['publisher'];
            return $publisher_item;
        }
        $publisher_item = null;
        $publisher_node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:publisher');
        if ($publisher_node_list->length > 0) {
            $publisher_node = $publisher_node_list->item(0);
            assert($publisher_node instanceof Dom_Element);
            $remote_item_list = $this->xpath->query('podcast:remoteItem', $publisher_node);
            if ($remote_item_list->length > 0) {
                $remote_item = $remote_item_list->item(0);
                assert($remote_item instanceof Dom_Element);
                $publisher_item = Attributes_Reader::read_remote_item($remote_item);
            }
        }
        $this->data['publisher'] = $publisher_item;
        return $this->data['publisher'];
    }
    /**
     * Get the podcast values
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
            $values[] = $value_object;
        }
        $this->data['values'] = $values;
        return $this->data['values'];
    }
    /**
     * Get the podcast social interacts
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
     * Get the podcast live items
     *
     * @psalm-return list<LiveItemReader>
     */
    public function get_podcast_index_live_items(): array
    {
        if (array_key_exists('liveItems', $this->data)) {
            /** @psalm-var list<LiveItemReader> */
            return $this->data['liveItems'];
        }
        $live_items = [];
        $node_list = $this->xpath->query($this->get_xpath_prefix() . '/podcast:liveItem');
        if ($node_list->length > 0) {
            $index = 0;
            foreach ($node_list as $entry) {
                assert($entry instanceof Dom_Element);
                $reader = new Live_Item_Reader($entry, (string) $index, $this->get_type());
                $reader->set_xpath($this->xpath);
                $reader->set_xpath_prefix('//podcast:liveItem[' . ($index + 1) . ']');
                $live_items[] = $reader;
                $index++;
            }
        }
        $this->data['liveItems'] = $live_items;
        return $this->data['liveItems'];
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