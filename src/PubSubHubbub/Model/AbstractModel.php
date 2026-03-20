<?php

declare (strict_types=1);
namespace Laminas\Feed\Pub_Sub_Hubbub\Model;

use function array_pop;
use function explode;
use Laminas\Db\Table_Gateway\Table_Gateway;
use Laminas\Db\Table_Gateway\Table_Gateway_Interface;
use function strtolower;
class Abstract_Model
{
    /**
     * Laminas\Db\TableGateway\TableGatewayInterface instance to host database methods
     *
     * @var TableGatewayInterface
     */
    protected $db;
    public function __construct(?Table_Gateway_Interface $table_gateway = null)
    {
        if ($table_gateway === null) {
            $parts = explode('\\', static::class);
            $table = strtolower(array_pop($parts));
            $this->db = new Table_Gateway($table, null);
        } else {
            $this->db = $table_gateway;
        }
    }
}