<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader;

use Laminas\Feed\Reader\Extension\Abstract_Entry;
use Laminas\Feed\Reader\Extension\Abstract_Feed;
use Laminas\Service_Manager\Abstract_Plugin_Manager;
use Laminas\Service_Manager\Exception\Invalid_Service_Exception;
use Laminas\Service_Manager\Factory\Invokable_Factory;
use function sprintf;
/**
 * Plugin manager implementation for feed reader extensions based on the
 * AbstractPluginManager.
 *
 * Validation checks that we have an Extension\AbstractEntry or
 * Extension\AbstractFeed.
 *
 * @final this class wasn't designed to be inherited from, but we can't assume that consumers haven't already
 *        extended it, therefore we cannot add the final marker without a new major release.
 * @template InstanceType of AbstractEntry|AbstractFeed
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
        'atomentry' => Extension\Atom\Entry::class,
        'atomEntry' => Extension\Atom\Entry::class,
        'AtomEntry' => Extension\Atom\Entry::class,
        'Atom\Entry' => Extension\Atom\Entry::class,
        'atomfeed' => Extension\Atom\Feed::class,
        'atomFeed' => Extension\Atom\Feed::class,
        'AtomFeed' => Extension\Atom\Feed::class,
        'Atom\Feed' => Extension\Atom\Feed::class,
        'contententry' => Extension\Content\Entry::class,
        'contentEntry' => Extension\Content\Entry::class,
        'ContentEntry' => Extension\Content\Entry::class,
        'Content\Entry' => Extension\Content\Entry::class,
        'creativecommonsentry' => Extension\Creative_Commons\Entry::class,
        'creativeCommonsEntry' => Extension\Creative_Commons\Entry::class,
        'CreativeCommonsEntry' => Extension\Creative_Commons\Entry::class,
        'CreativeCommons\Entry' => Extension\Creative_Commons\Entry::class,
        'creativecommonsfeed' => Extension\Creative_Commons\Feed::class,
        'creativeCommonsFeed' => Extension\Creative_Commons\Feed::class,
        'CreativeCommonsFeed' => Extension\Creative_Commons\Feed::class,
        'CreativeCommons\Feed' => Extension\Creative_Commons\Feed::class,
        'dublincoreentry' => Extension\Dublin_Core\Entry::class,
        'dublinCoreEntry' => Extension\Dublin_Core\Entry::class,
        'DublinCoreEntry' => Extension\Dublin_Core\Entry::class,
        'DublinCore\Entry' => Extension\Dublin_Core\Entry::class,
        'dublincorefeed' => Extension\Dublin_Core\Feed::class,
        'dublinCoreFeed' => Extension\Dublin_Core\Feed::class,
        'DublinCoreFeed' => Extension\Dublin_Core\Feed::class,
        'DublinCore\Feed' => Extension\Dublin_Core\Feed::class,
        'googleplaypodcastentry' => Extension\Google_Play_Podcast\Entry::class,
        'googlePlayPodcastEntry' => Extension\Google_Play_Podcast\Entry::class,
        'GooglePlayPodcastEntry' => Extension\Google_Play_Podcast\Entry::class,
        'GooglePlayPodcast\Entry' => Extension\Google_Play_Podcast\Entry::class,
        'googleplaypodcastfeed' => Extension\Google_Play_Podcast\Feed::class,
        'googlePlayPodcastFeed' => Extension\Google_Play_Podcast\Feed::class,
        'GooglePlayPodcastFeed' => Extension\Google_Play_Podcast\Feed::class,
        'GooglePlayPodcast\Feed' => Extension\Google_Play_Podcast\Feed::class,
        'podcastentry' => Extension\Podcast\Entry::class,
        'podcastEntry' => Extension\Podcast\Entry::class,
        'PodcastEntry' => Extension\Podcast\Entry::class,
        'Podcast\Entry' => Extension\Podcast\Entry::class,
        'podcastfeed' => Extension\Podcast\Feed::class,
        'podcastFeed' => Extension\Podcast\Feed::class,
        'PodcastFeed' => Extension\Podcast\Feed::class,
        'Podcast\Feed' => Extension\Podcast\Feed::class,
        'podcastindexentry' => Extension\Podcast_Index\Entry::class,
        'podcastIndexEntry' => Extension\Podcast_Index\Entry::class,
        'PodcastIndexEntry' => Extension\Podcast_Index\Entry::class,
        'PodcastIndex\Entry' => Extension\Podcast_Index\Entry::class,
        'podcastindexfeed' => Extension\Podcast_Index\Feed::class,
        'podcastIndexFeed' => Extension\Podcast_Index\Feed::class,
        'PodcastIndexFeed' => Extension\Podcast_Index\Feed::class,
        'PodcastIndex\Feed' => Extension\Podcast_Index\Feed::class,
        'slashentry' => Extension\Slash\Entry::class,
        'slashEntry' => Extension\Slash\Entry::class,
        'SlashEntry' => Extension\Slash\Entry::class,
        'Slash\Entry' => Extension\Slash\Entry::class,
        'syndicationfeed' => Extension\Syndication\Feed::class,
        'syndicationFeed' => Extension\Syndication\Feed::class,
        'SyndicationFeed' => Extension\Syndication\Feed::class,
        'Syndication\Feed' => Extension\Syndication\Feed::class,
        'threadentry' => Extension\Thread\Entry::class,
        'threadEntry' => Extension\Thread\Entry::class,
        'ThreadEntry' => Extension\Thread\Entry::class,
        'Thread\Entry' => Extension\Thread\Entry::class,
        'wellformedwebentry' => Extension\Well_Formed_Web\Entry::class,
        'wellFormedWebEntry' => Extension\Well_Formed_Web\Entry::class,
        'WellFormedWebEntry' => Extension\Well_Formed_Web\Entry::class,
        'WellFormedWeb\Entry' => Extension\Well_Formed_Web\Entry::class,
        // Legacy Zend Framework aliases
        'Zend\Feed\Reader\Extension\Atom\Entry' => Extension\Atom\Entry::class,
        'Zend\Feed\Reader\Extension\Atom\Feed' => Extension\Atom\Feed::class,
        'Zend\Feed\Reader\Extension\Content\Entry' => Extension\Content\Entry::class,
        'Zend\Feed\Reader\Extension\CreativeCommons\Entry' => Extension\Creative_Commons\Entry::class,
        'Zend\Feed\Reader\Extension\CreativeCommons\Feed' => Extension\Creative_Commons\Feed::class,
        'Zend\Feed\Reader\Extension\DublinCore\Entry' => Extension\Dublin_Core\Entry::class,
        'Zend\Feed\Reader\Extension\DublinCore\Feed' => Extension\Dublin_Core\Feed::class,
        'Zend\Feed\Reader\Extension\GooglePlayPodcast\Entry' => Extension\Google_Play_Podcast\Entry::class,
        'Zend\Feed\Reader\Extension\GooglePlayPodcast\Feed' => Extension\Google_Play_Podcast\Feed::class,
        'Zend\Feed\Reader\Extension\Podcast\Entry' => Extension\Podcast\Entry::class,
        'Zend\Feed\Reader\Extension\Podcast\Feed' => Extension\Podcast\Feed::class,
        'Zend\Feed\Reader\Extension\Slash\Entry' => Extension\Slash\Entry::class,
        'Zend\Feed\Reader\Extension\Syndication\Feed' => Extension\Syndication\Feed::class,
        'Zend\Feed\Reader\Extension\Thread\Entry' => Extension\Thread\Entry::class,
        'Zend\Feed\Reader\Extension\WellFormedWeb\Entry' => Extension\Well_Formed_Web\Entry::class,
        // v2 normalized FQCNs
        'zendfeedreaderextensionatomentry' => Extension\Atom\Entry::class,
        'zendfeedreaderextensionatomfeed' => Extension\Atom\Feed::class,
        'zendfeedreaderextensioncontententry' => Extension\Content\Entry::class,
        'zendfeedreaderextensioncreativecommonsentry' => Extension\Creative_Commons\Entry::class,
        'zendfeedreaderextensioncreativecommonsfeed' => Extension\Creative_Commons\Feed::class,
        'zendfeedreaderextensiondublincoreentry' => Extension\Dublin_Core\Entry::class,
        'zendfeedreaderextensiondublincorefeed' => Extension\Dublin_Core\Feed::class,
        'zendfeedreaderextensiongoogleplaypodcastentry' => Extension\Google_Play_Podcast\Entry::class,
        'zendfeedreaderextensiongoogleplaypodcastfeed' => Extension\Google_Play_Podcast\Feed::class,
        'zendfeedreaderextensionpodcastentry' => Extension\Podcast\Entry::class,
        'zendfeedreaderextensionpodcastfeed' => Extension\Podcast\Feed::class,
        'zendfeedreaderextensionslashentry' => Extension\Slash\Entry::class,
        'zendfeedreaderextensionsyndicationfeed' => Extension\Syndication\Feed::class,
        'zendfeedreaderextensionthreadentry' => Extension\Thread\Entry::class,
        'zendfeedreaderextensionwellformedwebentry' => Extension\Well_Formed_Web\Entry::class,
    ];
    /**
     * Factories for default set of extension classes
     *
     * @inheritDoc
     */
    protected $factories = [
        Extension\Atom\Entry::class => Invokable_Factory::class,
        Extension\Atom\Feed::class => Invokable_Factory::class,
        Extension\Content\Entry::class => Invokable_Factory::class,
        Extension\Creative_Commons\Entry::class => Invokable_Factory::class,
        Extension\Creative_Commons\Feed::class => Invokable_Factory::class,
        Extension\Dublin_Core\Entry::class => Invokable_Factory::class,
        Extension\Dublin_Core\Feed::class => Invokable_Factory::class,
        Extension\Google_Play_Podcast\Entry::class => Invokable_Factory::class,
        Extension\Google_Play_Podcast\Feed::class => Invokable_Factory::class,
        Extension\Podcast\Entry::class => Invokable_Factory::class,
        Extension\Podcast\Feed::class => Invokable_Factory::class,
        Extension\Podcast_Index\Entry::class => Invokable_Factory::class,
        Extension\Podcast_Index\Feed::class => Invokable_Factory::class,
        Extension\Slash\Entry::class => Invokable_Factory::class,
        Extension\Syndication\Feed::class => Invokable_Factory::class,
        Extension\Thread\Entry::class => Invokable_Factory::class,
        Extension\Well_Formed_Web\Entry::class => Invokable_Factory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'laminasfeedreaderextensionatomentry' => Invokable_Factory::class,
        'laminasfeedreaderextensionatomfeed' => Invokable_Factory::class,
        'laminasfeedreaderextensioncontententry' => Invokable_Factory::class,
        'laminasfeedreaderextensioncreativecommonsentry' => Invokable_Factory::class,
        'laminasfeedreaderextensioncreativecommonsfeed' => Invokable_Factory::class,
        'laminasfeedreaderextensiondublincoreentry' => Invokable_Factory::class,
        'laminasfeedreaderextensiondublincorefeed' => Invokable_Factory::class,
        'laminasfeedreaderextensiongoogleplaypodcastentry' => Invokable_Factory::class,
        'laminasfeedreaderextensiongoogleplaypodcastfeed' => Invokable_Factory::class,
        'laminasfeedreaderextensionpodcastentry' => Invokable_Factory::class,
        'laminasfeedreaderextensionpodcastfeed' => Invokable_Factory::class,
        'laminasfeedreaderextensionpodcastindexentry' => Invokable_Factory::class,
        'laminasfeedreaderextensionpodcastindexfeed' => Invokable_Factory::class,
        'laminasfeedreaderextensionslashentry' => Invokable_Factory::class,
        'laminasfeedreaderextensionsyndicationfeed' => Invokable_Factory::class,
        'laminasfeedreaderextensionthreadentry' => Invokable_Factory::class,
        'laminasfeedreaderextensionwellformedwebentry' => Invokable_Factory::class,
    ];
    /**
     * Do not share instances (v2)
     *
     * @deprecated
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
        if ($instance instanceof Abstract_Entry || $instance instanceof Abstract_Feed) {
            // we're okay
            return;
        }
        throw new Invalid_Service_Exception(sprintf('Plugin of type %s is invalid; must implement %s or %s', get_debug_type($instance), Abstract_Entry::class, Abstract_Feed::class));
    }
    /**
     * Validate the plugin (v2)
     *
     * @deprecated Since 2.18.0 This component is no longer compatible with service manager v2 series.
     *             This method will be removed in version 3.0 of this component
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidArgumentException If invalid.
     */
    public function validate_plugin($plugin): void
    {
        try {
            $this->validate($plugin);
        } catch (Invalid_Service_Exception) {
            throw new Exception\InvalidArgumentException(sprintf('Plugin of type %s is invalid; must implement %s or %s', get_debug_type($plugin), Abstract_Entry::class, Abstract_Feed::class));
        }
    }
}