<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader;

use function array_key_exists;
use function is_a;
use function is_string;
use Laminas\Feed\Reader\Exception\InvalidArgumentException;
use function sprintf;
/**
 * @final this class wasn't designed to be inherited from, but we can't assume that consumers haven't already
 *        extended it, therefore we cannot add the final marker without a new major release.
 */
class Standalone_Extension_Manager implements Extension_Manager_Interface
{
    /** @var array<string, class-string> */
    private array $extensions = ['Atom\Entry' => Extension\Atom\Entry::class, 'Atom\Feed' => Extension\Atom\Feed::class, 'Content\Entry' => Extension\Content\Entry::class, 'CreativeCommons\Entry' => Extension\Creative_Commons\Entry::class, 'CreativeCommons\Feed' => Extension\Creative_Commons\Feed::class, 'DublinCore\Entry' => Extension\Dublin_Core\Entry::class, 'DublinCore\Feed' => Extension\Dublin_Core\Feed::class, 'GooglePlayPodcast\Entry' => Extension\Google_Play_Podcast\Entry::class, 'GooglePlayPodcast\Feed' => Extension\Google_Play_Podcast\Feed::class, 'Podcast\Entry' => Extension\Podcast\Entry::class, 'Podcast\Feed' => Extension\Podcast\Feed::class, 'PodcastIndex\Entry' => Extension\Podcast_Index\Entry::class, 'PodcastIndex\Feed' => Extension\Podcast_Index\Feed::class, 'Slash\Entry' => Extension\Slash\Entry::class, 'Syndication\Feed' => Extension\Syndication\Feed::class, 'Thread\Entry' => Extension\Thread\Entry::class, 'WellFormedWeb\Entry' => Extension\Well_Formed_Web\Entry::class];
    /**
     * Do we have the extension?
     *
     * @param  string $extension
     */
    public function has($extension): bool
    {
        return array_key_exists($extension, $this->extensions);
    }
    /**
     * Retrieve the extension
     *
     * @return Extension\AbstractEntry|Extension\AbstractFeed
     */
    public function get(string $extension)
    {
        $class = $this->extensions[$extension];
        return new $class();
    }
    /**
     * Add an extension.
     *
     * @param string $class
     */
    public function add(string $name, $class): void
    {
        if (is_string($class) && (is_a($class, Extension\Abstract_Entry::class, true) || is_a($class, Extension\Abstract_Feed::class, true))) {
            $this->extensions[$name] = $class;
            return;
        }
        throw new InvalidArgumentException(sprintf('Plugin of type %s is invalid; must implement %2$s\Extension\AbstractFeed ' . 'or %2$s\Extension\AbstractEntry', $class, __NAMESPACE__));
    }
    /**
     * Remove an extension.
     */
    public function remove(string $name): void
    {
        unset($this->extensions[$name]);
    }
}