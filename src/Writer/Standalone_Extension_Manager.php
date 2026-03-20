<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function array_key_exists;
use function is_a;
use function is_string;
use Laminas\Feed\Writer\Exception\InvalidArgumentException;
use function sprintf;
class Standalone_Extension_Manager implements Extension_Manager_Interface
{
    /** @var array<string, class-string> */
    private array $extensions = ['Atom\Renderer\Feed' => Extension\Atom\Renderer\Feed::class, 'Content\Renderer\Entry' => Extension\Content\Renderer\Entry::class, 'DublinCore\Renderer\Entry' => Extension\Dublin_Core\Renderer\Entry::class, 'DublinCore\Renderer\Feed' => Extension\Dublin_Core\Renderer\Feed::class, 'GooglePlayPodcast\Entry' => Extension\Google_Play_Podcast\Entry::class, 'GooglePlayPodcast\Feed' => Extension\Google_Play_Podcast\Feed::class, 'GooglePlayPodcast\Renderer\Entry' => Extension\Google_Play_Podcast\Renderer\Entry::class, 'GooglePlayPodcast\Renderer\Feed' => Extension\Google_Play_Podcast\Renderer\Feed::class, 'ITunes\Entry' => Extension\I_Tunes\Entry::class, 'ITunes\Feed' => Extension\I_Tunes\Feed::class, 'ITunes\Renderer\Entry' => Extension\I_Tunes\Renderer\Entry::class, 'ITunes\Renderer\Feed' => Extension\I_Tunes\Renderer\Feed::class, 'PodcastIndex\Entry' => Extension\Podcast_Index\Entry::class, 'PodcastIndex\Feed' => Extension\Podcast_Index\Feed::class, 'PodcastIndex\Renderer\Entry' => Extension\Podcast_Index\Renderer\Entry::class, 'PodcastIndex\Renderer\Feed' => Extension\Podcast_Index\Renderer\Feed::class, 'Slash\Renderer\Entry' => Extension\Slash\Renderer\Entry::class, 'Threading\Renderer\Entry' => Extension\Threading\Renderer\Entry::class, 'WellFormedWeb\Renderer\Entry' => Extension\Well_Formed_Web\Renderer\Entry::class];
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
     * @return mixed
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
     * @psalm-param class-string $class
     */
    public function add(string $name, $class): void
    {
        if (is_string($class) && (is_a($class, Extension\Abstract_Renderer::class, true) || str_ends_with($class, 'Feed') || str_ends_with($class, 'Entry'))) {
            $this->extensions[$name] = $class;
            return;
        }
        throw new InvalidArgumentException(sprintf('Plugin of type %s is invalid; must implement %s\Extension\RendererInterface ' . 'or the classname must end in "Feed" or "Entry"', $class, __NAMESPACE__));
    }
    /**
     * Remove an extension.
     */
    public function remove(string $name): void
    {
        unset($this->extensions[$name]);
    }
}