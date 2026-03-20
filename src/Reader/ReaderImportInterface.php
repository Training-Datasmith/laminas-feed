<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader;

interface Reader_Import_Interface
{
    /**
     * Import a feed by providing a URI
     *
     * @param  string $uri The URI to the feed
     * @param  null|string $etag OPTIONAL Last received ETag for this resource
     * @param  null|string $lastModified OPTIONAL Last-Modified value for this resource
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException
     */
    public static function import($uri, $etag = null, $last_modified = null);
    /**
     * Import a feed from a remote URI
     *
     * Performs similarly to import(), except it uses the HTTP client passed to
     * the method, and does not take into account cached data.
     *
     * Primary purpose is to make it possible to use the Reader with alternate
     * HTTP client implementations.
     *
     * @param  string $uri
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException If response is not an Http\ResponseInterface.
     */
    public static function import_remote_feed($uri, Http\Client_Interface $client);
    /**
     * Import a feed from a string
     *
     * @param  string $string
     * @return Feed\FeedInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public static function import_string($string);
    /**
     * Imports a feed from a file located at $filename.
     *
     * @param  string $filename
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException
     */
    public static function import_file($filename);
}