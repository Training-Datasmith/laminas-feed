<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Podcast_Index;

use function array_key_exists;
use function count;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Exception\InvalidArgumentException;
use Laminas\Stdlib\String_Utils;
use Laminas\Stdlib\String_Wrapper\String_Wrapper_Interface;
use function lcfirst;
use function method_exists;
use function rtrim;
use function substr;
use function ucfirst;
/**
 * Describes PodcastIndex data of a RSS Feed
 *
 * @psalm-import-type LockedArray from Validator
 * @psalm-import-type FundingArray from Validator
 * @psalm-import-type LicenseArray from Validator
 * @psalm-import-type LocationArray from Validator
 * @psalm-import-type BlockArray from Validator
 * @psalm-import-type TxtArray from Validator
 * @psalm-import-type PersonArray from Validator
 * @psalm-import-type UpdateFrequencyArray from Validator
 * @psalm-import-type TrailerArray from Validator
 * @psalm-import-type RemoteItemArray from Validator
 * @psalm-import-type ValueRecipientArray from Validator
 * @psalm-import-type ValueArray from Validator
 * @psalm-import-type ImagesArray from Validator
 * @psalm-import-type DetailedImageArray from Validator
 * @psalm-import-type SocialInteractArray from Validator
 * @psalm-import-type LiveItemArray from Validator
 * @psalm-import-type ChatArray from Validator
 */
class Feed
{
    /**
     * Array of Feed data for rendering by Extension's renderers
     *
     * @var array
     */
    protected $data = [];
    /**
     * Contains all live item objects
     *
     * @var array<int, LiveItem>
     */
    protected $live_items = [];
    /**
     * A pointer for the iterator to keep track of the live items array
     *
     * @var int
     */
    protected $live_item_key = 0;
    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $encoding = 'UTF-8';
    /**
     * The used string wrapper supporting encoding
     *
     * @var StringWrapperInterface
     */
    protected $string_wrapper;
    public function __construct()
    {
        $this->string_wrapper = String_Utils::get_wrapper($this->encoding);
    }
    /**
     * Set feed encoding
     */
    public function set_encoding(string $enc): Feed
    {
        $this->string_wrapper = String_Utils::get_wrapper($enc);
        $this->encoding = $enc;
        return $this;
    }
    /**
     * Get feed encoding
     */
    public function get_encoding(): string
    {
        return $this->encoding;
    }
    /**
     * Set a locked value of "yes" or "no" with an "owner" field.
     *
     * @param LockedArray $value
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_locked(array $value): Feed
    {
        $this->data['locked'] = Validator::validate_locked($value);
        return $this;
    }
    /**
     * Sets a single feed funding tag.
     *
     * @deprecated Use `setPodcastIndexFundings()` or `addPodcastIndexFunding()` instead.
     *
     * @param FundingArray $value
     */
    public function set_podcast_index_funding(array $value): Feed
    {
        $this->data['funding'] = Validator::validate_funding($value);
        return $this;
    }
    /**
     * Adds a feed funding tag.
     *
     * @param FundingArray $value
     * @return $this
     */
    public function add_podcast_index_funding(array $value): self
    {
        if (!isset($this->data['fundings'])) {
            $this->data['fundings'] = [];
        }
        /** @var list<FundingArray> $this->data['fundings'] */
        $this->data['fundings'][] = Validator::validate_funding($value);
        return $this;
    }
    /**
     * Set multiple funding tags
     *
     * @param list<FundingArray> $values
     * @return $this
     */
    public function set_podcast_index_fundings(array $values = []): self
    {
        $this->data['fundings'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_funding($value);
        }
        return $this;
    }
    /**
     * Set feed license
     *
     * @param LicenseArray $value
     * @return $this
     */
    public function set_podcast_index_license(array $value): self
    {
        $this->data['license'] = Validator::validate_license($value);
        return $this;
    }
    /**
     * Sets a single feed location tag
     *
     * @deprecated Use `setPodcastIndexLocations()` or `addPodcastIndexLocation()` instead.
     *
     * @param LocationArray $value
     * @return $this
     */
    public function set_podcast_index_location(array $value): self
    {
        $this->data['location'] = Validator::validate_location($value);
        return $this;
    }
    /**
     * Adds a feed location tag.
     *
     * @param LocationArray $value
     * @return $this
     */
    public function add_podcast_index_location(array $value): self
    {
        if (!isset($this->data['locations'])) {
            $this->data['locations'] = [];
        }
        /** @var list<LocationArray> $this->data['locations'] */
        $this->data['locations'][] = Validator::validate_location($value);
        return $this;
    }
    /**
     * Sets multiple location tags
     *
     * @param list<LocationArray> $values
     * @return $this
     */
    public function set_podcast_index_locations(array $values = []): self
    {
        $this->data['locations'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_location($value);
        }
        return $this;
    }
    /**
     * Sets a single `images` element with a srcset value.
     * _Note: The namespace `images` is deprecated in PodcastIndex.
     * Instead, you may set one or more `image` tags using the `setPodcastIndexDetailedImages()` method._
     *
     * @deprecated
     *
     * @param ImagesArray $value
     * @return $this
     */
    public function set_podcast_index_images(array $value): self
    {
        $this->data['images'] = Validator::validate_images($value);
        return $this;
    }
    /**
     * Adds a feed `image` element.
     *
     * @param DetailedImageArray $value
     * @return $this
     */
    public function add_podcast_index_detailed_image(array $value): self
    {
        if (!isset($this->data['detailedImages'])) {
            $this->data['detailedImages'] = [];
        }
        /** @var list<DetailedImageArray> $this->data['detailedImages'] */
        $this->data['detailedImages'][] = Validator::validate_detailed_image($value);
        return $this;
    }
    /**
     * Sets multiple feed `image` elements.
     * If no argument is passed, all existing image entries are removed.
     *
     * @param list<DetailedImageArray> $values
     * @return $this
     */
    public function set_podcast_index_detailed_images(array $values = []): self
    {
        $this->data['detailedImages'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_detailed_image($value);
        }
        return $this;
    }
    /**
     * Set feed update frequency
     *
     * @param UpdateFrequencyArray $value
     * @return $this
     */
    public function set_podcast_index_update_frequency(array $value): self
    {
        $this->data['updateFrequency'] = Validator::validate_update_frequency($value);
        return $this;
    }
    /**
     * Add feed person
     *
     * @psalm-param PersonArray $value
     * @return $this
     */
    public function add_podcast_index_person(array $value): self
    {
        if (!isset($this->data['people'])) {
            $this->data['people'] = [];
        }
        /** @var list<PersonArray> $this->data['people'] */
        $this->data['people'][] = Validator::validate_person($value);
        return $this;
    }
    /**
     * Set a new array of people.
     * If no argument is passed, all existing person entries are removed.
     *
     * @psalm-param list<PersonArray> $values
     * @return $this
     */
    public function set_podcast_index_people(array $values = []): self
    {
        $this->data['people'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_person($value);
        }
        return $this;
    }
    /**
     * Set a new array of persons. (alias of setPodcastIndexPeople)
     *  If no argument is passed, all existing person entries are removed.
     *
     * @psalm-param list<PersonArray> $values
     * @return $this
     */
    public function set_podcast_index_persons(array $values = []): self
    {
        return $this->set_podcast_index_people($values);
    }
    /**
     * Set feed trailer
     *
     * @param TrailerArray $value
     * @return $this
     */
    public function set_podcast_index_trailer(array $value): self
    {
        $this->data['trailer'] = Validator::validate_trailer($value);
        return $this;
    }
    /**
     * Set feed guid
     *
     * @param array{value: string} $value
     * @return $this
     */
    public function set_podcast_index_guid(array $value): self
    {
        $this->data['guid'] = Validator::validate_guid($value);
        return $this;
    }
    /**
     * Set feed medium
     *
     * @param array{value: string} $value
     * @return $this
     */
    public function set_podcast_index_medium(array $value): self
    {
        $this->data['medium'] = Validator::validate_medium($value);
        return $this;
    }
    /**
     * Add feed block
     *
     * @param BlockArray $value
     * @return $this
     */
    public function add_podcast_index_block(array $value): self
    {
        if (!isset($this->data['blocks'])) {
            $this->data['blocks'] = [];
        }
        /** @var list<BlockArray> $this->data['blocks'] */
        $this->data['blocks'][] = Validator::validate_block($value);
        return $this;
    }
    /**
     * Set a new array of blocks.
     * If no argument is passed, it will just remove all existing block entries.
     *
     * @psalm-param list<BlockArray> $values
     * @return $this
     */
    public function set_podcast_index_blocks(array $values = []): self
    {
        $this->data['blocks'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_block($value);
        }
        return $this;
    }
    /**
     * Add feed txt
     *
     * @param TxtArray $value
     * @return $this
     */
    public function add_podcast_index_txt(array $value): self
    {
        if (!isset($this->data['txts'])) {
            $this->data['txts'] = [];
        }
        /** @var list<TxtArray> $this->data['txts'] */
        $this->data['txts'][] = Validator::validate_txt($value);
        return $this;
    }
    /**
     * Set a new array of txts.
     * If no argument is passed, it will just remove all existing txt entries.
     *
     * @psalm-param list<TxtArray> $values
     * @return $this
     */
    public function set_podcast_index_txts(array $values = []): self
    {
        $this->data['txts'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_txt($value);
        }
        return $this;
    }
    /**
     * Set feed podping
     *
     * @param array{usesPodping: bool} $value
     * @return $this
     */
    public function set_podcast_index_podping(array $value): self
    {
        $this->data['podping'] = Validator::validate_podping($value);
        return $this;
    }
    /**
     * Add a feed remote item.
     * The remote item will be treated as a direct child of the current channel element.
     * To create remote items as nested children of other elements, use their respective methods instead.
     *
     * @param RemoteItemArray $value
     * @return $this
     */
    public function add_podcast_index_remote_item(array $value): self
    {
        if (!isset($this->data['remoteItems'])) {
            $this->data['remoteItems'] = [];
        }
        /** @var list<RemoteItemArray> $this->data['remoteItems'] */
        $this->data['remoteItems'][] = Validator::validate_remote_item($value);
        return $this;
    }
    /**
     * Create a new set of remote items for the feed.
     * If no argument is passed, it will just remove all existing remote items of this feed.
     * The remote items will be treated as direct children of the current channel element.
     * If they should be treated as nested children of other elements, use their respective methods instead.
     *
     * @psalm-param list<RemoteItemArray> $values
     * @return $this
     */
    public function set_podcast_index_remote_items(array $values = []): self
    {
        $this->data['remoteItems'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_remote_item($value);
        }
        return $this;
    }
    /**
     * Set a podroll element with and array of remote items
     * that will be set as the podroll's child elements.
     * If no argument is passed, it will remove the entire podroll entry and all its nested remote items.
     *
     * @psalm-param list<RemoteItemArray> $values
     * @return $this
     */
    public function set_podcast_index_podroll(array $values = []): self
    {
        $this->data['podroll'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_podroll_remote_item($value);
        }
        return $this;
    }
    /**
     * Add a remote item to the podroll element.
     *
     * @psalm-param RemoteItemArray $value
     * @return $this
     */
    public function add_podcast_index_podroll_remote_item(array $value): self
    {
        if (!isset($this->data['podroll'])) {
            $this->data['podroll'] = [];
        }
        /** @var list<RemoteItemArray> $this->data['podroll'] */
        $this->data['podroll'][] = Validator::validate_remote_item($value);
        return $this;
    }
    /**
     * Set a publisher element.
     * It contains exactly one remote item as child element
     * and expects only an array of the remote item attributes.
     *
     * @psalm-param RemoteItemArray $value
     * @return $this
     */
    public function set_podcast_index_publisher(array $value): self
    {
        $this->data['publisher'] = Validator::validate_remote_item($value);
        return $this;
    }
    /**
     * Reset all value elements.
     * All value entries will be removed, including their nested valueRecipients.
     *
     * @return $this
     */
    public function reset_podcast_index_values(): self
    {
        $this->data['values'] = [];
        return $this;
    }
    /**
     * Add a value element with one or more valueRecipients as children.
     * The method expects one array with the value attributes as first argument
     * and an array of arrays with the valueRecipients' attributes as second argument.
     *
     * @psalm-param ValueArray $value
     * @psalm-param list<ValueRecipientArray> $valueRecipients
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function add_podcast_index_value(array $value, array $value_recipients): self
    {
        if (count($value_recipients) < 1) {
            throw new Writer\Exception\InvalidArgumentException('invalid parameter: the second argument of "value" must be an array ' . 'containing one or more "valueRecipients"');
        }
        $value = Validator::validate_value($value);
        foreach ($value_recipients as $value_recipient) {
            $value['valueRecipients'][] = Validator::validate_value_recipient($value_recipient);
        }
        if (!isset($this->data['values'])) {
            $this->data['values'] = [];
        }
        /** @var list<ValueArray> $this->data['values'] */
        $this->data['values'][] = $value;
        return $this;
    }
    /**
     * Add a social interact for the feed.
     *
     * @param SocialInteractArray $value
     * @return $this
     */
    public function add_podcast_index_social_interact(array $value): self
    {
        if (!isset($this->data['socialInteracts'])) {
            $this->data['socialInteracts'] = [];
        }
        /** @var list<SocialInteractArray> $this->data['socialInteracts'] */
        $this->data['socialInteracts'][] = Validator::validate_social_interact($value);
        return $this;
    }
    /**
     * Create a new set of social interacts for the feed.
     * If no argument is passed, any existing social interact entry will be removed.
     *
     * @psalm-param list<SocialInteractArray> $values
     * @return $this
     */
    public function set_podcast_index_social_interacts(array $values = []): self
    {
        $this->data['socialInteracts'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_social_interact($value);
        }
        return $this;
    }
    /**
     * Creates a new Laminas\Feed\Writer\Extension\PodcastIndex\LiveItem data container for use.
     * This is NOT added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     *
     * @param LiveItemArray $value
     */
    public function create_podcast_index_live_item(array $value): Live_Item
    {
        $value = Validator::validate_live_item($value);
        $live_item = new Live_Item($value);
        if ($this->get_encoding()) {
            $live_item->set_encoding($this->get_encoding());
        }
        return $live_item;
    }
    /**
     * Appends a Laminas\Feed\Writer\Extension\PodcastIndex\LiveItem object.
     *
     * @return $this
     */
    public function add_podcast_index_live_item(Live_Item $live_item): self
    {
        $this->live_items[] = $live_item;
        return $this;
    }
    /**
     * Removes a specific indexed liveItem from the internal queue. LiveItems must be
     * added to a feed container in order to be indexed.
     *
     * @param  int $index
     * @return $this
     * @throws InvalidArgumentException
     */
    public function remove_podcast_index_live_item($index): static
    {
        if (!isset($this->live_items[$index])) {
            throw new InvalidArgumentException('Undefined index: ' . $index . '. LiveItem does not exist.');
        }
        unset($this->live_items[$index]);
        return $this;
    }
    /**
     * Set a chat element.
     *
     * @psalm-param ChatArray $value
     * @return $this
     */
    public function set_podcast_index_chat(array $value): self
    {
        $this->data['chat'] = Validator::validate_chat($value);
        return $this;
    }
    /**
     * Overloading: proxy to internal setters
     *
     * @return mixed
     * @throws Writer\Exception\BadMethodCallException
     */
    public function __call(string $method, array $params)
    {
        $point = lcfirst(substr($method, 15));
        if (!method_exists($this, 'setPodcastIndex' . ucfirst($point)) && !method_exists($this, 'addPodcastIndex' . ucfirst($point)) && !method_exists($this, 'addPodcastIndex' . rtrim(ucfirst($point), 's'))) {
            throw new Writer\Exception\BadMethodCallException('invalid method: ' . $method);
        }
        if (!array_key_exists($point, $this->data) || empty($this->data[$point])) {
            return;
        }
        return $this->data[$point];
    }
    /**
     * Is locked.
     * Specific get call for non-default naming.
     */
    public function is_locked(): bool
    {
        return $this->is_podcast_index_locked();
    }
    /**
     * Is locked.
     * Specific get call for non-default naming.
     */
    public function is_podcast_index_locked(): bool
    {
        if (isset($this->data['locked'], $this->data['locked']['value'])) {
            return $this->data['locked']['value'] === 'yes';
        }
        return false;
    }
    /**
     * Get lock owner.
     * Specific get call for non-default naming.
     */
    public function get_lock_owner(): string|null
    {
        return $this->get_podcast_index_lock_owner();
    }
    /**
     * Get lock owner.
     * Specific get call for non-default naming.
     */
    public function get_podcast_index_lock_owner(): string|null
    {
        if (isset($this->data['locked'], $this->data['locked']['owner'])) {
            /** @psalm-var string $this->data['locked']['owner'] */
            return $this->data['locked']['owner'];
        }
        return null;
    }
    /**
     * Get persons.
     * Specific get call for non-default naming.
     */
    public function get_podcast_index_persons(): array|null
    {
        /** @var list<PersonArray> $persons */
        $persons = $this->get_podcast_index_people();
        return $persons;
    }
    /**
     * Get live items.
     * Specific get call for non-default naming.
     */
    public function get_podcast_index_live_items(): array|null
    {
        if (count($this->live_items) > 0) {
            return $this->live_items;
        }
        return null;
    }
    /**
     * Get multiple funding tags
     * Specific get call for non-default naming.
     *
     * @return null|list<FundingArray>
     */
    public function get_podcast_index_fundings(): array|null
    {
        $fundings = null;
        if (isset($this->data['fundings'])) {
            /** @var list<FundingArray> $fundings */
            $fundings = $this->data['fundings'];
        }
        if (isset($this->data['funding'])) {
            /** @var FundingArray $single */
            $single = $this->data['funding'];
            $fundings[] = $single;
        }
        return $fundings;
    }
    /**
     * Get multiple location tags
     * Specific get call for non-default naming.
     *
     * @return null|list<LocationArray>
     */
    public function get_podcast_index_locations(): array|null
    {
        $locations = null;
        if (isset($this->data['locations'])) {
            /** @var list<LocationArray> $locations */
            $locations = $this->data['locations'];
        }
        if (isset($this->data['location'])) {
            /** @var LocationArray $single */
            $single = $this->data['location'];
            $locations[] = $single;
        }
        return $locations;
    }
}