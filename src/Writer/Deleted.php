<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer;

use function array_key_exists;
use DateTime;
use DateTimeInterface;
use function is_int;
use function is_string;
use Laminas\Feed\Uri;
class Deleted
{
    /**
     * Internal array containing all data associated with this entry or item.
     *
     * @var array
     */
    protected $data = [];
    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $type;
    /**
     * Set the feed character encoding
     *
     * @param  null|string $encoding
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_encoding($encoding): static
    {
        if (empty($encoding)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['encoding'] = $encoding;
        return $this;
    }
    /**
     * Get the feed character encoding
     *
     * @return null|string
     */
    public function get_encoding()
    {
        if (!array_key_exists('encoding', $this->data)) {
            return 'UTF-8';
        }
        return $this->data['encoding'];
    }
    /**
     * Unset a specific data point
     *
     * @param  string $name
     * @return $this
     */
    public function remove($name): static
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
        return $this;
    }
    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param  string $type
     * @return $this
     */
    public function set_type($type): static
    {
        $this->type = $type;
        return $this;
    }
    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function get_type()
    {
        return $this->type;
    }
    /**
     * Set reference
     *
     * @param  string $reference
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_reference($reference): static
    {
        if (empty($reference) || !is_string($reference)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: reference must be a non-empty string');
        }
        $this->data['reference'] = $reference;
        return $this;
    }
    /**
     * @return string
     */
    public function get_reference()
    {
        if (!array_key_exists('reference', $this->data)) {
            return;
        }
        return $this->data['reference'];
    }
    /**
     * Set when
     *
     * @param  null|int|DateTimeInterface $date
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_when($date = null): static
    {
        if ($date === null) {
            $date = new DateTime();
        }
        if (is_int($date)) {
            $date = new DateTime('@' . $date);
        }
        if (!$date instanceof DateTimeInterface) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp passed as parameter');
        }
        $this->data['when'] = $date;
        return $this;
    }
    /**
     * @return DateTime
     */
    public function get_when()
    {
        if (!array_key_exists('when', $this->data)) {
            return;
        }
        return $this->data['when'];
    }
    /**
     * Set by
     *
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function set_by(array $by): static
    {
        $author = [];
        if (!array_key_exists('name', $by) || empty($by['name']) || !is_string($by['name'])) {
            throw new Exception\InvalidArgumentException('Invalid parameter: author array must include a "name" key with a non-empty string value');
        }
        $author['name'] = $by['name'];
        if (isset($by['email'])) {
            if (empty($by['email']) || !is_string($by['email'])) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "email" array value must be a non-empty string');
            }
            $author['email'] = $by['email'];
        }
        if (isset($by['uri'])) {
            if (empty($by['uri']) || !is_string($by['uri']) || !Uri::factory($by['uri'])->is_valid()) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI');
            }
            $author['uri'] = $by['uri'];
        }
        $this->data['by'] = $author;
        return $this;
    }
    /**
     * @return null|string
     */
    public function get_by()
    {
        if (!array_key_exists('by', $this->data)) {
            return null;
        }
        return $this->data['by'];
    }
    /**
     * @param  string $comment
     * @return $this
     */
    public function set_comment($comment): static
    {
        $this->data['comment'] = $comment;
        return $this;
    }
    /**
     * @return null|string
     */
    public function get_comment()
    {
        if (!array_key_exists('comment', $this->data)) {
            return null;
        }
        return $this->data['comment'];
    }
}