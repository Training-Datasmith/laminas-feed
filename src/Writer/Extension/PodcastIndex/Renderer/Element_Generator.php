<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Podcast_Index\Renderer;

use DateTime;
use DateTimeInterface;
use Dom_Document;
use Dom_Element;
use function gettype;
use function is_string;
use function number_format;
/**
 * Creates PodcastIndex elements for feed and entry renderer.
 * This class is internal to the library and should not be referenced by consumer code.
 * Backwards Incompatible changes can occur in Minor and Patch Releases.
 *
 * @internal
 *
 * @psalm-internal Laminas\Feed
 * @psalm-internal LaminasTest\Feed
 */
final class Element_Generator
{
    /**
     * Create PodcastIndex element
     *
     * @psalm-param DOMDocument $dom
     * @psalm-param array $data
     * @psalm-param string $name
     * @psalm-param string $nodeValue
     */
    public static function create_podcast_index_element(Dom_Document $dom, array $data, string $name, string $node_value = ''): Dom_Element
    {
        $tag_name = 'podcast:' . $name;
        $element = $dom->create_element($tag_name);
        /**
         * @psalm-var string $key
         * @psalm-var mixed $value
         */
        foreach ($data as $key => $value) {
            if ($key === $node_value) {
                if (!is_string($value)) {
                    $value = (string) $value;
                }
                $text = $dom->create_text_node($value);
                $element->append_child($text);
                continue;
            }
            if ($key === 'aspectRatio') {
                $key = 'aspect-ratio';
            }
            switch (gettype($value)) {
                case 'string':
                    if ($value !== '') {
                        $element->set_attribute($key, $value);
                    }
                    break;
                case 'integer':
                    $element->set_attribute($key, (string) $value);
                    break;
                case 'double':
                    // ensure decimal number instead of scientific notation, and remove thousands comma seperator
                    if ($name === 'value' && $key === 'suggested') {
                        $num = number_format($value, 11, '.', '');
                    } else {
                        $num = number_format($value, 2, '.', '');
                    }
                    $element->set_attribute($key, $num);
                    break;
                case 'boolean':
                    $bool = $value ? 'true' : 'false';
                    $element->set_attribute($key, $bool);
                    break;
                case 'object':
                    if ($value instanceof DateTime) {
                        $date = $value->format(DateTimeInterface::ATOM);
                        $element->set_attribute($key, $date);
                    }
                    break;
                default:
                    break;
            }
        }
        return $element;
    }
}