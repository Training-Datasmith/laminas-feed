<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function is_object;
use Laminas\Feed\Writer\Extension\Google_Play_Podcast\Feed;
use Laminas\Feed\Writer\Extension\I_Tunes\Entry;
use Laminas\Service_Manager\Abstract_Plugin_Manager;
use Laminas\Service_Manager\Exception\Invalid_Service_Exception;
use Laminas\Service_Manager\Factory\Invokable_Factory;
use function sprintf;
// phpcs:disable Generic.Files.LineLength.TooLong
/**
 * Plugin manager implementation for feed writer extensions
 *
 * Validation checks that we have an Entry, Feed, or Extension\AbstractRenderer.
 *
 * @template InstanceType of Extension\AbstractRenderer|Entry|Feed|Entry|\Laminas\Feed\Writer\Extension\ITunes\Feed|\Laminas\Feed\Writer\Extension\PodcastIndex\Entry|\Laminas\Feed\Writer\Extension\PodcastIndex\Feed
 * @template-extends AbstractPluginManager<InstanceType>
 */
class Extension_Plugin_Manager extends Abstract_Plugin_Manager implements Extension_Manager_Interface
{
    /**
     * Aliases for default set of extension classes
     *
     * @inheritDoc
     */
    protected $aliases = [
        // phpcs:disable Generic.Files.LineLength.TooLong
        'atomrendererfeed' => Extension\Atom\Renderer\Feed::class,
        'atomRendererFeed' => Extension\Atom\Renderer\Feed::class,
        'AtomRendererFeed' => Extension\Atom\Renderer\Feed::class,
        'AtomRenderer\Feed' => Extension\Atom\Renderer\Feed::class,
        'Atom\Renderer\Feed' => Extension\Atom\Renderer\Feed::class,
        'contentrendererentry' => Extension\Content\Renderer\Entry::class,
        'contentRendererEntry' => Extension\Content\Renderer\Entry::class,
        'ContentRendererEntry' => Extension\Content\Renderer\Entry::class,
        'ContentRenderer\Entry' => Extension\Content\Renderer\Entry::class,
        'Content\Renderer\Entry' => Extension\Content\Renderer\Entry::class,
        'dublincorerendererentry' => Extension\Dublin_Core\Renderer\Entry::class,
        'dublinCoreRendererEntry' => Extension\Dublin_Core\Renderer\Entry::class,
        'DublinCoreRendererEntry' => Extension\Dublin_Core\Renderer\Entry::class,
        'DublinCoreRenderer\Entry' => Extension\Dublin_Core\Renderer\Entry::class,
        'DublinCore\Renderer\Entry' => Extension\Dublin_Core\Renderer\Entry::class,
        'dublincorerendererfeed' => Extension\Dublin_Core\Renderer\Feed::class,
        'dublinCoreRendererFeed' => Extension\Dublin_Core\Renderer\Feed::class,
        'DublinCoreRendererFeed' => Extension\Dublin_Core\Renderer\Feed::class,
        'DublinCoreRenderer\Feed' => Extension\Dublin_Core\Renderer\Feed::class,
        'DublinCore\Renderer\Feed' => Extension\Dublin_Core\Renderer\Feed::class,
        'googleplaypodcastentry' => Extension\Google_Play_Podcast\Entry::class,
        'googleplaypodcastEntry' => Extension\Google_Play_Podcast\Entry::class,
        'googlePlayPodcastEntry' => Extension\Google_Play_Podcast\Entry::class,
        'GooglePlayPodcastEntry' => Extension\Google_Play_Podcast\Entry::class,
        'Googleplaypodcast\Entry' => Extension\Google_Play_Podcast\Entry::class,
        'GooglePlayPodcast\Entry' => Extension\Google_Play_Podcast\Entry::class,
        'googleplaypodcastfeed' => Feed::class,
        'googleplaypodcastFeed' => Feed::class,
        'googlePlayPodcastFeed' => Feed::class,
        'GooglePlayPodcastFeed' => Feed::class,
        'Googleplaypodcast\Feed' => Feed::class,
        'GooglePlayPodcast\Feed' => Feed::class,
        'googleplaypodcastrendererentry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'googleplaypodcastRendererEntry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'googlePlayPodcastRendererEntry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'GooglePlayPodcastRendererEntry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'GoogleplaypodcastRenderer\Entry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'GooglePlayPodcast\Renderer\Entry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'googleplaypodcastrendererfeed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'googleplaypodcastRendererFeed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'googlePlayPodcastRendererFeed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'GooglePlayPodcastRendererFeed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'GoogleplaypodcastRenderer\Feed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'GooglePlayPodcast\Renderer\Feed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'itunesentry' => Entry::class,
        'itunesEntry' => Entry::class,
        'iTunesEntry' => Entry::class,
        'ItunesEntry' => Entry::class,
        'Itunes\Entry' => Entry::class,
        'ITunes\Entry' => Entry::class,
        'itunesfeed' => Extension\I_Tunes\Feed::class,
        'itunesFeed' => Extension\I_Tunes\Feed::class,
        'iTunesFeed' => Extension\I_Tunes\Feed::class,
        'ItunesFeed' => Extension\I_Tunes\Feed::class,
        'Itunes\Feed' => Extension\I_Tunes\Feed::class,
        'ITunes\Feed' => Extension\I_Tunes\Feed::class,
        'itunesrendererentry' => Extension\I_Tunes\Renderer\Entry::class,
        'itunesRendererEntry' => Extension\I_Tunes\Renderer\Entry::class,
        'iTunesRendererEntry' => Extension\I_Tunes\Renderer\Entry::class,
        'ItunesRendererEntry' => Extension\I_Tunes\Renderer\Entry::class,
        'ItunesRenderer\Entry' => Extension\I_Tunes\Renderer\Entry::class,
        'ITunes\Renderer\Entry' => Extension\I_Tunes\Renderer\Entry::class,
        'itunesrendererfeed' => Extension\I_Tunes\Renderer\Feed::class,
        'itunesRendererFeed' => Extension\I_Tunes\Renderer\Feed::class,
        'iTunesRendererFeed' => Extension\I_Tunes\Renderer\Feed::class,
        'ItunesRendererFeed' => Extension\I_Tunes\Renderer\Feed::class,
        'ItunesRenderer\Feed' => Extension\I_Tunes\Renderer\Feed::class,
        'ITunes\Renderer\Feed' => Extension\I_Tunes\Renderer\Feed::class,
        'podcastindexentry' => Extension\Podcast_Index\Entry::class,
        'podcastindexEntry' => Extension\Podcast_Index\Entry::class,
        'PodcastIndexEntry' => Extension\Podcast_Index\Entry::class,
        'PodcastIndex\Entry' => Extension\Podcast_Index\Entry::class,
        'podcastindexfeed' => Extension\Podcast_Index\Feed::class,
        'podcastindexFeed' => Extension\Podcast_Index\Feed::class,
        'PodcastIndexFeed' => Extension\Podcast_Index\Feed::class,
        'PodcastIndex\Feed' => Extension\Podcast_Index\Feed::class,
        'podcastindexrendererentry' => Extension\Podcast_Index\Renderer\Entry::class,
        'podcastindexRendererEntry' => Extension\Podcast_Index\Renderer\Entry::class,
        'PodcastIndexRendererEntry' => Extension\Podcast_Index\Renderer\Entry::class,
        'PodcastIndexRenderer\Entry' => Extension\Podcast_Index\Renderer\Entry::class,
        'PodcastIndex\Renderer\Entry' => Extension\Podcast_Index\Renderer\Entry::class,
        'podcastindexrendererfeed' => Extension\Podcast_Index\Renderer\Feed::class,
        'podcastindexRendererFeed' => Extension\Podcast_Index\Renderer\Feed::class,
        'PodcastIndexRendererFeed' => Extension\Podcast_Index\Renderer\Feed::class,
        'PodcastIndexRenderer\Feed' => Extension\Podcast_Index\Renderer\Feed::class,
        'PodcastIndex\Renderer\Feed' => Extension\Podcast_Index\Renderer\Feed::class,
        'slashrendererentry' => Extension\Slash\Renderer\Entry::class,
        'slashRendererEntry' => Extension\Slash\Renderer\Entry::class,
        'SlashRendererEntry' => Extension\Slash\Renderer\Entry::class,
        'SlashRenderer\Entry' => Extension\Slash\Renderer\Entry::class,
        'Slash\Renderer\Entry' => Extension\Slash\Renderer\Entry::class,
        'threadingrendererentry' => Extension\Threading\Renderer\Entry::class,
        'threadingRendererEntry' => Extension\Threading\Renderer\Entry::class,
        'ThreadingRendererEntry' => Extension\Threading\Renderer\Entry::class,
        'ThreadingRenderer\Entry' => Extension\Threading\Renderer\Entry::class,
        'Threading\Renderer\Entry' => Extension\Threading\Renderer\Entry::class,
        'wellformedwebrendererentry' => Extension\Well_Formed_Web\Renderer\Entry::class,
        'wellFormedWebRendererEntry' => Extension\Well_Formed_Web\Renderer\Entry::class,
        'WellFormedWebRendererEntry' => Extension\Well_Formed_Web\Renderer\Entry::class,
        'WellFormedWebRenderer\Entry' => Extension\Well_Formed_Web\Renderer\Entry::class,
        'WellFormedWeb\Renderer\Entry' => Extension\Well_Formed_Web\Renderer\Entry::class,
        // Legacy Zend Framework aliases
        'Zend\Feed\Writer\Extension\Atom\Renderer\Feed' => Extension\Atom\Renderer\Feed::class,
        'Zend\Feed\Writer\Extension\Content\Renderer\Entry' => Extension\Content\Renderer\Entry::class,
        'Zend\Feed\Writer\Extension\DublinCore\Renderer\Entry' => Extension\Dublin_Core\Renderer\Entry::class,
        'Zend\Feed\Writer\Extension\DublinCore\Renderer\Feed' => Extension\Dublin_Core\Renderer\Feed::class,
        'Zend\Feed\Writer\Extension\GooglePlayPodcast\Entry' => Extension\Google_Play_Podcast\Entry::class,
        'Zend\Feed\Writer\Extension\GooglePlayPodcast\Feed' => Feed::class,
        'Zend\Feed\Writer\Extension\GooglePlayPodcast\Renderer\Entry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'Zend\Feed\Writer\Extension\GooglePlayPodcast\Renderer\Feed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'Zend\Feed\Writer\Extension\ITunes\Entry' => Entry::class,
        'Zend\Feed\Writer\Extension\ITunes\Feed' => Extension\I_Tunes\Feed::class,
        'Zend\Feed\Writer\Extension\ITunes\Renderer\Entry' => Extension\I_Tunes\Renderer\Entry::class,
        'Zend\Feed\Writer\Extension\ITunes\Renderer\Feed' => Extension\I_Tunes\Renderer\Feed::class,
        'Zend\Feed\Writer\Extension\Slash\Renderer\Entry' => Extension\Slash\Renderer\Entry::class,
        'Zend\Feed\Writer\Extension\Threading\Renderer\Entry' => Extension\Threading\Renderer\Entry::class,
        'Zend\Feed\Writer\Extension\WellFormedWeb\Renderer\Entry' => Extension\Well_Formed_Web\Renderer\Entry::class,
        // v2 normalized FQCNs
        'zendfeedwriterextensionatomrendererfeed' => Extension\Atom\Renderer\Feed::class,
        'zendfeedwriterextensioncontentrendererentry' => Extension\Content\Renderer\Entry::class,
        'zendfeedwriterextensiondublincorerendererentry' => Extension\Dublin_Core\Renderer\Entry::class,
        'zendfeedwriterextensiondublincorerendererfeed' => Extension\Dublin_Core\Renderer\Feed::class,
        'zendfeedwriterextensiongoogleplaypodcastentry' => Extension\Google_Play_Podcast\Entry::class,
        'zendfeedwriterextensiongoogleplaypodcastfeed' => Feed::class,
        'zendfeedwriterextensiongoogleplaypodcastrendererentry' => Extension\Google_Play_Podcast\Renderer\Entry::class,
        'zendfeedwriterextensiongoogleplaypodcastrendererfeed' => Extension\Google_Play_Podcast\Renderer\Feed::class,
        'zendfeedwriterextensionitunesentry' => Entry::class,
        'zendfeedwriterextensionitunesfeed' => Extension\I_Tunes\Feed::class,
        'zendfeedwriterextensionitunesrendererentry' => Extension\I_Tunes\Renderer\Entry::class,
        'zendfeedwriterextensionitunesrendererfeed' => Extension\I_Tunes\Renderer\Feed::class,
        'zendfeedwriterextensionslashrendererentry' => Extension\Slash\Renderer\Entry::class,
        'zendfeedwriterextensionthreadingrendererentry' => Extension\Threading\Renderer\Entry::class,
        'zendfeedwriterextensionwellformedwebrendererentry' => Extension\Well_Formed_Web\Renderer\Entry::class,
    ];
    /**
     * Factories for default set of extension classes
     *
     * @inheritDoc
     */
    protected $factories = [
        Extension\Atom\Renderer\Feed::class => Invokable_Factory::class,
        Extension\Content\Renderer\Entry::class => Invokable_Factory::class,
        Extension\Dublin_Core\Renderer\Entry::class => Invokable_Factory::class,
        Extension\Dublin_Core\Renderer\Feed::class => Invokable_Factory::class,
        Extension\Google_Play_Podcast\Entry::class => Invokable_Factory::class,
        Feed::class => Invokable_Factory::class,
        Extension\Google_Play_Podcast\Renderer\Entry::class => Invokable_Factory::class,
        Extension\Google_Play_Podcast\Renderer\Feed::class => Invokable_Factory::class,
        Entry::class => Invokable_Factory::class,
        Extension\I_Tunes\Feed::class => Invokable_Factory::class,
        Extension\I_Tunes\Renderer\Entry::class => Invokable_Factory::class,
        Extension\I_Tunes\Renderer\Feed::class => Invokable_Factory::class,
        Extension\Podcast_Index\Entry::class => Invokable_Factory::class,
        Extension\Podcast_Index\Feed::class => Invokable_Factory::class,
        Extension\Podcast_Index\Renderer\Entry::class => Invokable_Factory::class,
        Extension\Podcast_Index\Renderer\Feed::class => Invokable_Factory::class,
        Extension\Slash\Renderer\Entry::class => Invokable_Factory::class,
        Extension\Threading\Renderer\Entry::class => Invokable_Factory::class,
        Extension\Well_Formed_Web\Renderer\Entry::class => Invokable_Factory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'laminasfeedwriterextensionatomrendererfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensioncontentrendererentry' => Invokable_Factory::class,
        'laminasfeedwriterextensiondublincorerendererentry' => Invokable_Factory::class,
        'laminasfeedwriterextensiondublincorerendererfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensiongoogleplaypodcastentry' => Invokable_Factory::class,
        'laminasfeedwriterextensiongoogleplaypodcastfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensiongoogleplaypodcastrendererentry' => Invokable_Factory::class,
        'laminasfeedwriterextensiongoogleplaypodcastrendererfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensionitunesentry' => Invokable_Factory::class,
        'laminasfeedwriterextensionitunesfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensionitunesrendererentry' => Invokable_Factory::class,
        'laminasfeedwriterextensionitunesrendererfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensionpodcastindexentry' => Invokable_Factory::class,
        'laminasfeedwriterextensionpodcastindexfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensionpodcastindexrendererentry' => Invokable_Factory::class,
        'laminasfeedwriterextensionpodcastindexrendererfeed' => Invokable_Factory::class,
        'laminasfeedwriterextensionslashrendererentry' => Invokable_Factory::class,
        'laminasfeedwriterextensionthreadingrendererentry' => Invokable_Factory::class,
        'laminasfeedwriterextensionwellformedwebrendererentry' => Invokable_Factory::class,
    ];
    /**
     * Do not share instances (v2)
     *
     * @var bool
     */
    protected $share_by_default = false;
    /**
     * Do not share instances (v3)
     *
     * @var bool
     */
    protected $shared_by_default = false;
    /** @inheritDoc */
    public function validate(mixed $instance): void
    {
        if ($instance instanceof Extension\Abstract_Renderer) {
            // we're okay
            return;
        }
        if (is_object($instance) && str_ends_with($instance::class, 'Feed')) {
            // we're okay
            return;
        }
        if (is_object($instance) && str_ends_with($instance::class, 'Entry')) {
            // we're okay
            return;
        }
        throw new Invalid_Service_Exception(sprintf('Plugin of type %s is invalid; must implement %s\Extension\RendererInterface ' . 'or the classname must end in "Feed" or "Entry"', get_debug_type($instance), __NAMESPACE__));
    }
    /**
     * Validate plugin (v2)
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidArgumentException When invalid.
     */
    public function validate_plugin($plugin): void
    {
        try {
            $this->validate($plugin);
        } catch (Invalid_Service_Exception) {
            throw new Exception\InvalidArgumentException(sprintf('Plugin of type %s is invalid; must implement %s\Extension\RendererInterface ' . 'or the classname must end in "Feed" or "Entry"', get_debug_type($plugin), __NAMESPACE__));
        }
    }
}