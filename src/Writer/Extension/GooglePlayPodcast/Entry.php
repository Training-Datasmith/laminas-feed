<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Google_Play_Podcast;

use function array_key_exists;
use function ctype_alpha;
use function in_array;
use Laminas\Feed\Writer;
use Laminas\Stdlib\String_Utils;
use Laminas\Stdlib\String_Wrapper\String_Wrapper_Interface;
use function lcfirst;
use function method_exists;
use function strlen;
use function substr;
use function ucfirst;
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
     *
     * @param  string $enc
     * @return $this
     */
    public function set_encoding($enc): static
    {
        $this->string_wrapper = String_Utils::get_wrapper($enc);
        $this->encoding = $enc;
        return $this;
    }
    /**
     * Get feed encoding
     *
     * @return string
     */
    public function get_encoding()
    {
        return $this->encoding;
    }
    /**
     * Set a block value of "yes" or "no". You may also set an empty string.
     *
     * @param string $value
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_play_podcast_block($value): void
    {
        if (!ctype_alpha($value) && strlen($value) > 0) {
            throw new Writer\Exception\InvalidArgumentException('invalid parameter: "block" may only contain alphabetic characters');
        }
        if ($this->string_wrapper->strlen($value) > 255) {
            throw new Writer\Exception\InvalidArgumentException('invalid parameter: "block" may only contain a maximum of 255 characters');
        }
        $this->data['block'] = $value;
    }
    /**
     * Set "explicit" flag
     *
     * @param  bool $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_play_podcast_explicit($value): static
    {
        if (!in_array($value, ['yes', 'no', 'clean'], true)) {
            throw new Writer\Exception\InvalidArgumentException('invalid parameter: "explicit" may only be one of "yes", "no" or "clean"');
        }
        $this->data['explicit'] = $value;
        return $this;
    }
    /**
     * Set episode description
     *
     * @param  string $value
     * @return $this
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function set_play_podcast_description($value): static
    {
        if ($this->string_wrapper->strlen($value) > 4000) {
            throw new Writer\Exception\InvalidArgumentException('invalid parameter: "description" may only contain a maximum of 4000 characters');
        }
        $this->data['description'] = $value;
        return $this;
    }
    /**
     * Overloading to itunes specific setters
     *
     * @return mixed
     * @throws Writer\Exception\BadMethodCallException
     */
    public function __call(string $method, array $params)
    {
        $point = lcfirst(substr($method, 14));
        if (!method_exists($this, 'setPlayPodcast' . ucfirst($point)) && !method_exists($this, 'addPlayPodcast' . ucfirst($point))) {
            throw new Writer\Exception\BadMethodCallException('invalid method: ' . $method);
        }
        if (!array_key_exists($point, $this->data) || empty($this->data[$point])) {
            return;
        }
        return $this->data[$point];
    }
}