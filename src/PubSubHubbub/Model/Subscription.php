<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub\Model;

use function array_key_exists;
use function count;
use DateInterval;
use DateTime;
use function is_string;
use Laminas\Feed\Pub_Sub_Hubbub;
class Subscription extends Abstract_Model implements Subscription_Persistence_Interface
{
    /**
     * Common DateTime object to assist with unit testing
     *
     * @var DateTime
     */
    protected $now;
    /**
     * Save subscription to RDMBS
     *
     * @throws PubSubHubbub\Exception\InvalidArgumentException
     */
    public function set_subscription(array $data): bool
    {
        if (!isset($data['id'])) {
            throw new Pub_Sub_Hubbub\Exception\InvalidArgumentException('ID must be set before attempting a save');
        }
        $result = $this->db->select(['id' => $data['id']]);
        if (0 < count($result)) {
            /** @psalm-suppress UndefinedInterfaceMethod */
            $data['created_time'] = $result->current()->created_time;
            $now = $this->get_now();
            if (array_key_exists('lease_seconds', $data) && $data['lease_seconds']) {
                $data['expiration_time'] = $now->add(new DateInterval('PT' . $data['lease_seconds'] . 'S'))->format('Y-m-d H:i:s');
            }
            $this->db->update($data, ['id' => $data['id']]);
            return false;
        }
        $this->db->insert($data);
        return true;
    }
    /**
     * Get subscription by ID/key
     *
     * @param  string $key
     * @return array
     * @throws PubSubHubbub\Exception\InvalidArgumentException
     */
    public function get_subscription($key)
    {
        if (empty($key) || !is_string($key)) {
            throw new Pub_Sub_Hubbub\Exception\InvalidArgumentException('Invalid parameter "key" of "' . $key . '" must be a non-empty string');
        }
        $result = $this->db->select(['id' => $key]);
        if (count($result)) {
            /** @psalm-suppress UndefinedInterfaceMethod */
            return $result->current()->get_array_copy();
        }
        return false;
    }
    /**
     * Determine if a subscription matching the key exists
     *
     * @param  string $key
     * @throws PubSubHubbub\Exception\InvalidArgumentException
     */
    public function has_subscription($key): bool
    {
        if (empty($key) || !is_string($key)) {
            throw new Pub_Sub_Hubbub\Exception\InvalidArgumentException('Invalid parameter "key" of "' . $key . '" must be a non-empty string');
        }
        $result = $this->db->select(['id' => $key]);
        if (count($result)) {
            return true;
        }
        return false;
    }
    /**
     * Delete a subscription
     *
     * @param  string $key
     */
    public function delete_subscription($key): bool
    {
        $result = $this->db->select(['id' => $key]);
        if (count($result)) {
            $this->db->delete(['id' => $key]);
            return true;
        }
        return false;
    }
    /**
     * Get a new DateTime or the one injected for testing
     *
     * @return DateTime
     */
    public function get_now()
    {
        if (null === $this->now) {
            return new DateTime();
        }
        return $this->now;
    }
    /**
     * Set a DateTime instance for assisting with unit testing
     *
     * @return $this
     */
    public function set_now(DateTime $now): static
    {
        $this->now = $now;
        return $this;
    }
}