<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub\Model;

interface Subscription_Persistence_Interface
{
    /**
     * Save subscription to RDMBS
     *
     * @param  array $data The key must be stored here as a $data['id'] entry
     * @return bool
     */
    public function set_subscription(array $data);
    /**
     * Get subscription by ID/key
     *
     * @param  string $key
     * @return array
     */
    public function get_subscription($key);
    /**
     * Determine if a subscription matching the key exists
     *
     * @param  string $key
     * @return bool
     */
    public function has_subscription($key);
    /**
     * Delete a subscription
     *
     * @param  string $key
     * @return bool
     */
    public function delete_subscription($key);
}