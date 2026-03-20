<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Extension\Syndication;

use function array_key_exists;
use DateTime;
use Laminas\Feed\Reader;
use Laminas\Feed\Reader\Extension;
class Feed extends Extension\Abstract_Feed
{
    /**
     * Get update period
     *
     * @throws Reader\Exception\InvalidArgumentException
     */
    public function get_update_period(): string
    {
        $name = 'updatePeriod';
        $period = $this->get_data($name);
        if ($period === null) {
            $this->data[$name] = 'daily';
            return 'daily';
            //Default specified by spec
        }
        return match ($period) {
            'hourly', 'daily', 'weekly', 'yearly' => $period,
            default => throw new Reader\Exception\InvalidArgumentException("Feed specified invalid update period: '{$period}'." . ' Must be one of hourly, daily, weekly or yearly'),
        };
    }
    /**
     * Get update frequency
     *
     * @return int
     */
    public function get_update_frequency()
    {
        $name = 'updateFrequency';
        $freq = $this->get_data($name, 'number');
        if (!$freq || $freq < 1) {
            $this->data[$name] = 1;
            return 1;
        }
        return $freq;
    }
    /**
     * Get update frequency as ticks
     *
     * @return int
     */
    public function get_update_frequency_as_ticks(): int|float
    {
        $name = 'updateFrequency';
        $freq = $this->get_data($name, 'number');
        if (!$freq || $freq < 1) {
            $this->data[$name] = 1;
            $freq = 1;
        }
        $period = $this->get_update_period();
        $ticks = 1;
        switch ($period) {
            case 'yearly':
                $ticks *= 52;
            //TODO: fix generalisation, how?
            // no break
            case 'weekly':
                $ticks *= 7;
            // no break
            case 'daily':
                $ticks *= 24;
            // no break
            case 'hourly':
                $ticks *= 3600;
                break;
            default:
                //Never arrive here, exception thrown in getPeriod()
                break;
        }
        return $ticks / $freq;
    }
    /**
     * Get update base
     *
     * @return null|DateTime
     */
    public function get_update_base()
    {
        $update_base = $this->get_data('updateBase');
        if ($update_base) {
            return DateTime::create_from_format(DateTime::W3C, $update_base);
        }
        return null;
    }
    /**
     * Get the entry data specified by name
     *
     * @return null|mixed
     */
    private function get_data(string $name, string $type = 'string')
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $data = $this->xpath->evaluate($type . '(' . $this->get_xpath_prefix() . '/syn10:' . $name . ')');
        if (!$data) {
            $data = null;
        }
        $this->data[$name] = $data;
        return $data;
    }
    /**
     * Register Syndication namespaces
     *
     * @return void
     */
    protected function register_namespaces()
    {
        $this->xpath->register_namespace('syn10', 'http://purl.org/rss/1.0/modules/syndication/');
    }
}