<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Http;

interface Response_Interface
{
    /**
     * Retrieve the response body
     *
     * @return string
     */
    public function get_body();
    /**
     * Retrieve the HTTP response status code
     *
     * @return int
     */
    public function get_status_code();
}