<?php

/*
 * This file is part of the P13 package.
 *
 * (c) Wagner Sicca <wssicca@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace p13\core\config;

use \ReflectionClass;

/**
 * Description of AbstractDatabaseConfiguration
 *
 * @author Wagner Sicca <wssicca@gmail.com>
 * @namespace p13\core\config
 * @package p13\core\config
 * @abstract
 */
class DatabaseConfiguration extends AbstractConfiguration
{

    /**
     * Nome do banco de dados
     * @var string
     */
    protected $dbname;

    /**
     * Array com os nomes de drivers PDO relativos a cada tipo 
     * de banco aceito pelo P13 Framework
     * @var string[]
     */
    private $drivers;

    /**
     * Nome ou endereço do servidor 
     * @var string
     */
    protected $host;

    /**
     * Senha para conexão
     * @var string
     */
    protected $password;

    /**
     * Porta do servidor na qual será feita a conexão
     * @var string
     */
    protected $port;

    /**
     * Tipo de banco de dados. Os tipos aceitos são:
     * - pgsql
     * - mysql
     * @var string
     */
    protected $type;

    /**
     * Nome de usuário para conexão no banco
     * @var string
     */
    protected $user;

    protected function __construct()
    {
        parent::__construct();
        $this->drivers = [
            'pgsql' => 'pdo_pgsql',
            'mysql' => 'pdo_mysql'
        ];
    }

    /**
     * Retorna um array com informações para
     * conexão a um banco de dados
     * @return string[]
     */
    public function getArray()
    {
        return [
            'driver' => $this->drivers[$this->type],
            'host' => $this->host,
            'port' => $this->port,
            'dbname' => $this->dbname,
            'user' => $this->user,
            'password' => $this->password
        ];
    }

    /**
     * Retorna um DSN (Data Source Name) no formato do tipo
     * de banco de dados especificado na classe. Por enquanto,
     * suporta apenas PostgreSQL e MySQL
     * @return string
     */
    public function getDsn()
    {
        switch ($this->type) {
            case 'pgsql':
                return $this->getPgsqlDsn();
            default:
                return $this->getMysqlDsn();
        }
    }

    /**
     * Retorna uma instância de uma classe de configuração
     * para conexão com um banco de dados
     * @return DatabaseConfiguration
     */
    public static function getInstance()
    {
        // Obtém o provável nome da classe de configuração do banco
        $dbConfig = ApplicationConfiguration::getInstance()
                ->defaultDatabaseConfig;
        $class_name = 'app\\config\\database\\' .
                StringHandler::underscoreParaPascalCase($dbConfig);

        // Se a classe existir e for válida
        if (class_exists($class_name)) {
            $reflection = new ReflectionClass($class_name);
            if ($reflection->isSubclassOf(__CLASS__)) {
                return new $class_name;
            }
        }
    }

    /**
     * Retorna um DSN (Data Source Name) para 
     * conexão com um banco de dados MySQL
     * @return string
     */
    private function getMysqlDsn()
    {
        return "{$this->type}:"
                . "dbname={$this->dbname};"
                . "host={$this->host};"
                . "port={$this->port};";
    }

    /**
     * Retorna um DSN (Data Source Name) para
     * conexão com um banco de dados PostgreSQL
     * @return string
     */
    private function getPgsqlDsn()
    {
        return $this->getMysqlDsn()
                . "user={$this->user};"
                . "password={$this->password};";
    }

}
