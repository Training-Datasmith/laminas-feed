<?php

declare (strict_types=1);
namespace Laminas\Feed\Reader\Exception;

use Laminas\Feed\Exception;
class Invalid_Http_Client_Exception extends Exception\InvalidArgumentException implements Exception_Interface
{
}