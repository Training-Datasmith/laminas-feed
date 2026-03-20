<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Podcast_Index;

use function array_key_exists;
use function count;
use Laminas\Feed\Writer;
use Laminas\Stdlib\String_Utils;
use Laminas\Stdlib\String_Wrapper\String_Wrapper_Interface;
use function lcfirst;
use function method_exists;
use function rtrim;
use function substr;
use function ucfirst;
/**
 * Describes PodcastIndex data of an entry in a RSS Feed
 *
 * @psalm-import-type TranscriptArray from Validator
 * @psalm-import-type ChaptersArray from Validator
 * @psalm-import-type SoundbiteArray from Validator
 * @psalm-import-type LicenseArray from Validator
 * @psalm-import-type LocationArray from Validator
 * @psalm-import-type TxtArray from Validator
 * @psalm-import-type PersonArray from Validator
 * @psalm-import-type ValueRecipientArray from Validator
 * @psalm-import-type ValueTimeSplitArray from Validator
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
class Entry
{
    /**
     * Array of Feed data for rendering by Extension's renderers
     *
     * @var array
     */
    protected $data = [];
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
    public function set_encoding(string $enc): Entry
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
     * Set entry transcript
     *
     * @param TranscriptArray $value
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_transcript(array $value): Entry
    {
        $this->data['transcript'] = Validator::validate_transcript($value);
        return $this;
    }
    /**
     * Set entry chapters
     *
     * @param ChaptersArray $value
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_chapters(array $value): Entry
    {
        $this->data['chapters'] = Validator::validate_chapters($value);
        return $this;
    }
    /**
     * Add multiple entry soundbites
     *
     * @param list<SoundbiteArray> $values
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function add_podcast_index_soundbites(array $values): Entry
    {
        foreach ($values as $value) {
            $this->add_podcast_index_soundbite($value);
        }
        return $this;
    }
    /**
     * Set entry soundbites.
     * If no argument is passed, the existing soundbite entries get removed.
     *
     * @param list<SoundbiteArray> $values
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_soundbites(array $values = []): self
    {
        $this->data['soundbites'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_soundbite($value);
        }
        return $this;
    }
    /**
     * Add a single entry soundbite
     *
     * @param SoundbiteArray $value
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function add_podcast_index_soundbite(array $value): Entry
    {
        if (!isset($this->data['soundbites'])) {
            $this->data['soundbites'] = [];
        }
        /** @var list<SoundbiteArray> $this->data['soundbites'] */
        $this->data['soundbites'][] = Validator::validate_soundbite($value);
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
     * Set multiple location tags
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
     * Set entry license
     *
     * @param LicenseArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_license(array $value): self
    {
        $this->data['license'] = Validator::validate_license($value);
        return $this;
    }
    /**
     * Add entry person
     *
     * @param PersonArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
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
     * If no argument is passed, it will just remove all existing people.
     *
     * @param list<PersonArray> $values
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
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
     * If no argument is passed, it will just remove all existing persons.
     *
     * @param list<PersonArray> $values
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_persons(array $values = []): self
    {
        return $this->set_podcast_index_people($values);
    }
    /**
     * Add entry txt
     *
     * @param TxtArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
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
     * @param list<TxtArray> $values
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
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
     * Add a social interact for the entry.
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
     * Create a new set of social interacts for the entry.
     * If no argument is passed, existing social interacts will be removed.
     *
     * @param list<SocialInteractArray> $values
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
     * Reset all value elements.
     * All value entries will be removed, including their nested valueRecipients and valueTimeSplits.
     *
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function reset_podcast_index_values(): self
    {
        $this->data['values'] = [];
        return $this;
    }
    /**
     * Adds a value element with one or more valueRecipients as children.
     * Optionally, a set of value time splits can also be attached.
     *
     * @param ValueArray $value
     * @param list<ValueRecipientArray> $valueRecipients
     * @param list<ValueTimeSplitArray> $valueTimeSplits
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function add_podcast_index_value(array $value, array $value_recipients, array $value_time_splits = []): self
    {
        if (count($value_recipients) < 1) {
            throw new Writer\Exception\InvalidArgumentException('invalid parameter: the second argument of "value" must be an array containing ' . 'at least one entry with valueRecipient data');
        }
        $value = Validator::validate_value($value);
        foreach ($value_recipients as $value_recipient) {
            $value['valueRecipients'][] = Validator::validate_value_recipient($value_recipient);
        }
        foreach ($value_time_splits as $split) {
            $value['valueTimeSplits'][] = Validator::validate_value_time_split($split);
        }
        if (!isset($this->data['values'])) {
            $this->data['values'] = [];
        }
        /** @var list<ValueArray> $this->data['values'] */
        $this->data['values'][] = $value;
        return $this;
    }
    /**
     * Set entry season
     *
     * @param SeasonArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_season(array $value): self
    {
        $this->data['season'] = Validator::validate_season($value);
        return $this;
    }
    /**
     * Set entry episode
     *
     * @param EpisodeArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_episode(array $value): self
    {
        $this->data['episode'] = Validator::validate_episode($value);
        return $this;
    }
    /**
     * Set entry alternateEnclosure
     *
     * @param AlternateEnclosureArray $enclosure
     * @param list<SourceArray> $sources
     * @param null|IntegrityArray $integrity
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function add_podcast_index_alternate_enclosure(array $enclosure, array $sources, ?array $integrity = null): self
    {
        if (count($sources) < 1) {
            throw new Writer\Exception\InvalidArgumentException('invalid parameter: the second argument to "alternateEnclosure" must be an array containing ' . 'at least one source entry');
        }
        $enclosure = Validator::validate_alternate_enclosure($enclosure);
        foreach ($sources as $source) {
            $enclosure['sources'][] = Validator::validate_source($source);
        }
        if ($integrity !== null) {
            $enclosure['integrity'] = Validator::validate_integrity($integrity);
        }
        if (!isset($this->data['alternateEnclosures'])) {
            $this->data['alternateEnclosures'] = [];
        }
        /** @var list<AlternateEnclosureArray> $this->data['alternateEnclosures'] */
        $this->data['alternateEnclosures'][] = $enclosure;
        return $this;
    }
    /**
     * Reset all alternate enclosure elements.
     * All alternate enclosure entries will be removed, including their nested sources and integrities.
     *
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function reset_podcast_index_alternate_enclosures(): self
    {
        $this->data['alternateEnclosures'] = [];
        return $this;
    }
    /**
     * Adds an `image` element to the episode.
     *
     * @param DetailedImageArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
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
     * Sets multiple episode `image` elements.
     * If no argument is passed, all existing image entries are removed.
     *
     * @param list<DetailedImageArray> $values
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
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
     * Adds an `contentLink` element to the episode.
     *
     * @param ContentLinkArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function add_podcast_index_content_link(array $value): self
    {
        if (!isset($this->data['contentLinks'])) {
            $this->data['contentLinks'] = [];
        }
        /** @var list<ContentLinkArray> $this->data['contentLinks'] */
        $this->data['contentLinks'][] = Validator::validate_content_link($value);
        return $this;
    }
    /**
     * Sets multiple episode `contentLink` elements.
     * If no argument is passed, all existing entries are removed.
     *
     * @param list<ContentLinkArray> $values
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_podcast_index_content_links(array $values = []): self
    {
        $this->data['contentLinks'] = [];
        foreach ($values as $value) {
            $this->add_podcast_index_content_link($value);
        }
        return $this;
    }
    /**
     * Adds a funding tag.
     *
     * @param FundingArray $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
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
     * @throws Writer\Exception\InvalidArgumentException
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
     * Get persons.
     * Specific get call for non-default naming.
     */
    public function get_podcast_index_persons(): array
    {
        /** @var list<PersonArray> $persons */
        $persons = $this->get_podcast_index_people();
        return $persons;
    }
}