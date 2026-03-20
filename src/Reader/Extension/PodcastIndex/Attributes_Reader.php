<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Podcast_Index;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use DateTimeInterface;
// phpcs:enable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use Dom_Element;
use stdClass;
/**
 * Reads PodcastIndex data that exists for both, Feeds and Entries.
 * This class is internal to the library and should not be referenced by consumer code.
 * Backwards Incompatible changes can occur in Minor and Patch Releases.
 *
 * @internal
 *
 * @psalm-internal Laminas\Feed
 * @psalm-internal LaminasTest\Feed
 *
 * @psalm-type FundingObject = object{
 *         title: string,
 *         url: string
 *       }
 * @psalm-type LicenseObject = object{
 *        identifier: string,
 *        url: string
 *      }
 * @psalm-type LocationObject = object{
 *        description: string,
 *        geo: string,
 *        osm: string,
 *        rel: string,
 *        country: string,
 *      }
 * @psalm-type BlockObject = object{
 *        value: string,
 *        id: string
 *      }
 * @psalm-type TxtObject = object{
 *        value: string,
 *        purpose: string
 *      }
 * @psalm-type UpdateFrequencyObject = object{
 *        description: string,
 *        complete: bool,
 *        dtstart: DateTimeInterface,
 *        rrule: string
 *      }
 * @psalm-type PersonObject = object{
 *       name: string,
 *       role: string,
 *       group: string,
 *       img: string,
 *       href: string
 *     }
 * @psalm-type TrailerObject = object{
 *       title: string,
 *       pubdate: string,
 *       url: string,
 *       length: int,
 *       type: string,
 *       season: int
 *     }
 * @psalm-type RemoteItemObject = object{
 *       feedGuid: string,
 *       feedUrl: string,
 *       itemGuid: string,
 *       medium: string,
 *       title: string
 *     }
 * @psalm-type ValueRecipientObject = object{
 *       type: string,
 *       address: string,
 *       split: int,
 *       name: string,
 *       customKey: string,
 *       customValue: string,
 *       fee: bool,
 *     }
 * @psalm-type ValueTimeSplitObject = object{
 *        startTime: int,
 *        duration: int,
 *        remoteStartTime: int,
 *        remotePercentage: int,
 *        valueRecipients: list<ValueRecipientObject>,
 *        remoteItem: RemoteItemObject
 *      }
 * @psalm-type ValueObject = object{
 *       type: string,
 *       method: string,
 *       suggested: float,
 *       valueRecipients: list<ValueRecipientObject>,
 *       valueTimeSplits: list<ValueTimeSplitObject>,
 *     }
 * @psalm-type ImagesObject = object{
 *        srcset: string,
 *      }
 * @psalm-type DetailedImageObject = object{
 *        href: string,
 *        alt: string,
 *        purpose: string,
 *        type: string,
 *        aspectRatio: string,
 *        width: int,
 *        height: int,
 *      }
 * @psalm-type SocialInteractObject = object{
 *       protocol: string,
 *       uri: string,
 *       priority: int,
 *       accountId: string,
 *       accountUrl: string,
 *     }
 * @psalm-type TranscriptObject = object{
 *       url: string,
 *       type: string,
 *       language: string,
 *       rel: string
 *     }
 * @psalm-type ChaptersObject = object{
 *       url: string,
 *       type: string
 *     }
 * @psalm-type SoundbiteObject = object{
 *       title: string,
 *       startTime: string,
 *       duration: string
 *     }
 * @psalm-type SeasonObject = object{
 *         value: int,
 *         name: string
 *       }
 * @psalm-type EpisodeObject = object{
 *         value: int|float,
 *         display: string
 *       }
 * @psalm-type SourceObject = object{
 *        uri: string,
 *        contentType: string
 *      }
 * @psalm-type IntegrityObject = object{
 *        type: string,
 *        value: string
 *      }
 * @psalm-type AlternateEnclosureObject = object{
 *        type: string,
 *        length: int,
 *        bitrate: int|float,
 *        height: int,
 *        lang: string,
 *        title: string,
 *        rel: string,
 *        codecs: string,
 *        default: bool,
 *        sources: list<SourceObject>,
 *        integrity: IntegrityObject,
 *     }
 * @psalm-type ContentLinkObject = object{
 *        href: string,
 *        description: string,
 *     }
 * @psalm-type ChatObject = object{
 *        server: string,
 *        protocol: string,
 *        accountId: string,
 *        space: string,
 *      }
 */
final class Attributes_Reader
{
    /**
     * Read feed or item license
     *
     * @psalm-return LicenseObject
     */
    public static function read_license(Dom_Element $item): \stdClass
    {
        $license = new stdClass();
        $license->identifier = $item->node_value;
        $license->url = $item->get_attribute('url');
        return $license;
    }
    /**
     * Read podcast location
     *
     * @psalm-return LocationObject
     */
    public static function read_location(Dom_Element $item): \stdClass
    {
        $location = new stdClass();
        $location->description = $item->node_value;
        $location->geo = $item->get_attribute('geo');
        $location->osm = $item->get_attribute('osm');
        $location->rel = $item->get_attribute('rel');
        $location->country = $item->get_attribute('country');
        return $location;
    }
    /**
     * Read podcast images
     *
     * @psalm-return ImagesObject
     */
    public static function read_images(Dom_Element $item): \stdClass
    {
        $images = new stdClass();
        $images->srcset = $item->get_attribute('srcset');
        return $images;
    }
    /**
     * Read podcast images
     *
     * @psalm-return DetailedImageObject
     */
    public static function read_detailed_image(Dom_Element $item): \stdClass
    {
        $image = new stdClass();
        $image->href = $item->get_attribute('href');
        $image->alt = $item->get_attribute('alt');
        $image->aspect_ratio = $item->get_attribute('aspect-ratio');
        $image->width = $item->get_attribute('width');
        $image->height = $item->get_attribute('height');
        $image->type = $item->get_attribute('type');
        $image->purpose = $item->get_attribute('purpose');
        return $image;
    }
    /**
     * Read podcast update frequency
     *
     * @psalm-return UpdateFrequencyObject
     */
    public static function read_update_frequency(Dom_Element $item): \stdClass
    {
        $update_frequency = new stdClass();
        $update_frequency->description = $item->node_value;
        $update_frequency->complete = $item->get_attribute('complete');
        $update_frequency->dtstart = $item->get_attribute('dtstart');
        $update_frequency->rrule = $item->get_attribute('rrule');
        return $update_frequency;
    }
    /**
     * Read podcast people
     *
     * @psalm-return PersonObject
     */
    public static function read_person(Dom_Element $item): \stdClass
    {
        $person = new stdClass();
        $person->name = $item->node_value;
        $person->role = $item->get_attribute('role');
        $person->group = $item->get_attribute('group');
        $person->img = $item->get_attribute('img');
        $person->href = $item->get_attribute('href');
        return $person;
    }
    /**
     * Read podcast trailer
     *
     * @psalm-return TrailerObject
     */
    public static function read_trailer(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->title = $item->node_value;
        $object->pubdate = $item->get_attribute('pubdate');
        $object->url = $item->get_attribute('url');
        $object->length = $item->get_attribute('length');
        $object->type = $item->get_attribute('type');
        $object->season = $item->get_attribute('season');
        return $object;
    }
    /**
     * Read podcast guid
     *
     * @psalm-return object{value: string}
     */
    public static function read_guid(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->value = $item->node_value;
        return $object;
    }
    /**
     * Read podcast medium
     *
     * @psalm-return object{value: string}
     */
    public static function read_medium(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->value = $item->node_value;
        return $object;
    }
    /**
     * Read podcast blocks
     *
     * @psalm-return BlockObject
     */
    public static function read_block(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->value = $item->node_value;
        $object->id = $item->get_attribute('id');
        return $object;
    }
    /**
     * Read podcast txts
     *
     * @psalm-return TxtObject
     */
    public static function read_txt(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->value = $item->node_value;
        $object->purpose = $item->get_attribute('purpose');
        return $object;
    }
    /**
     * Read podcast remote item
     *
     * @psalm-return RemoteItemObject
     */
    public static function read_remote_item(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->feed_guid = $item->get_attribute('feedGuid');
        $object->feed_url = $item->get_attribute('feedUrl');
        $object->item_guid = $item->get_attribute('itemGuid');
        $object->medium = $item->get_attribute('medium');
        $object->title = $item->get_attribute('title');
        return $object;
    }
    /**
     * Read podcast podroll remote items
     *
     * @psalm-return ValueObject
     */
    public static function read_value(Dom_Element $item): \stdClass
    {
        $value_object = new stdClass();
        $value_object->type = $item->get_attribute('type');
        $value_object->method = $item->get_attribute('method');
        $value_object->suggested = $item->get_attribute('suggested');
        return $value_object;
    }
    /**
     * Read single remote item
     *
     * @psalm-return ValueRecipientObject
     */
    public static function read_value_recipient(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->name = $item->get_attribute('name');
        $object->type = $item->get_attribute('type');
        $object->address = $item->get_attribute('address');
        $object->split = $item->get_attribute('split');
        $object->custom_key = $item->get_attribute('customKey');
        $object->custom_value = $item->get_attribute('customValue');
        $object->fee = $item->get_attribute('fee');
        return $object;
    }
    /**
     * Read single value time split
     */
    public static function read_value_time_split(Dom_Element $entry): \stdClass
    {
        $object = new stdClass();
        $object->start_time = $entry->get_attribute('startTime');
        $object->duration = $entry->get_attribute('duration');
        $object->remote_start_time = $entry->get_attribute('remoteStartTime');
        $object->remote_percentage = $entry->get_attribute('remotePercentage');
        return $object;
    }
    /**
     * Read podcast social interacts
     *
     * @psalm-return SocialInteractObject
     */
    public static function read_social_interact(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->protocol = $item->get_attribute('protocol');
        $object->uri = $item->get_attribute('uri');
        $object->priority = $item->get_attribute('priority');
        $object->account_id = $item->get_attribute('accountId');
        $object->account_url = $item->get_attribute('accountUrl');
        return $object;
    }
    /**
     * Read podcast alternate enclosure
     *
     * @psalm-return AlternateEnclosureObject
     */
    public static function read_alternate_enclosure(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->type = $item->get_attribute('type');
        $object->length = $item->get_attribute('length');
        $object->bitrate = $item->get_attribute('bitrate');
        $object->height = $item->get_attribute('height');
        $object->lang = $item->get_attribute('lang');
        $object->title = $item->get_attribute('title');
        $object->rel = $item->get_attribute('rel');
        $object->codecs = $item->get_attribute('codecs');
        $object->default = $item->get_attribute('default');
        return $object;
    }
    /**
     * Read podcast source
     *
     * @psalm-return SourceObject
     */
    public static function read_source(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->uri = $item->get_attribute('uri');
        $object->content_type = $item->get_attribute('contentType');
        return $object;
    }
    /**
     * Read podcast integrity
     *
     * @psalm-return SourceObject
     */
    public static function read_integrity(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->type = $item->get_attribute('type');
        $object->value = $item->get_attribute('value');
        return $object;
    }
    /**
     * Read content link
     *
     * @psalm-return ContentLinkObject
     */
    public static function read_content_link(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->href = $item->get_attribute('href');
        $object->description = $item->node_value;
        return $object;
    }
    /**
     * Read podcast funding
     *
     * @psalm-return FundingObject
     */
    public static function read_funding(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->url = $item->get_attribute('url');
        $object->title = $item->node_value;
        return $object;
    }
    /**
     * Read podcast chat
     *
     * @psalm-return ChatObject
     */
    public static function read_chat(Dom_Element $item): \stdClass
    {
        $object = new stdClass();
        $object->server = $item->get_attribute('server');
        $object->protocol = $item->get_attribute('protocol');
        $object->account_id = $item->get_attribute('accountId');
        $object->space = $item->get_attribute('space');
        return $object;
    }
}