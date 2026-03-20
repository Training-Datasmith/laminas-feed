<?php

declare (strict_types=1);
namespace Laminas\Feed\Writer\Extension\Podcast_Index;

use Laminas\Feed\Writer\Entry;
/**
 * Describes LiveItem data in a RSS Feed
 *
 * @psalm-import-type LiveItemArray from Validator
 */
final class Live_Item extends Entry
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
    protected string $status;
    protected string $start;
    protected string $end;
    /**
     * The used string wrapper supporting encoding
     *
     * @param LiveItemArray $value
     */
    public function __construct(array $value)
    {
        parent::__construct();
        $this->status = $this->data['status'] = $value['status'];
        $this->start = $this->data['start'] = $value['start'];
        $this->end = $this->data['end'] = $value['end'] ?? '';
        $this->type = 'rss';
    }
    /**
     * Set the podcast index live item status
     */
    public function set_status(string $status): void
    {
        $this->status = $this->data['status'] = $status;
    }
    /**
     * Get the podcast index live item status
     */
    public function get_status(): string
    {
        return $this->status;
    }
    /**
     * Set the podcast index live item start time
     */
    public function set_start(string $start): void
    {
        $this->status = $this->data['status'] = $start;
    }
    /**
     * Get the podcast index live item start time
     */
    public function get_start(): string
    {
        return $this->start;
    }
    /**
     * Set the podcast index live item end time
     */
    public function set_end(string $end): void
    {
        $this->status = $this->data['end'] = $end;
    }
    /**
     * Get the podcast index live item end time
     */
    public function get_end(): string
    {
        return $this->end;
    }
}