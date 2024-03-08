<?php

namespace Migracao\Database;

use PDO;
use PDOException;

class Database
{

    /**
     * Host de conexão com o banco de dados
     * @var string
     */
    private $host;

    /**
     * Nome do banco de dados
     * @var string
     */
    private $name;

    /**
     * Usuário do banco
     * @var string
     */
    private $user;

    /**
     * Senha de acesso ao banco de dados
     * @var string
     */
    private $pass;

    /**
     * Porta de acesso ao banco
     * @var integer
     */
    private $port;

    /**
     * Instância de conexão com o banco de dados
     * @var PDO
     */
    private $connection;

    /**
     * Método responsável por instanciar a classe
     */
    public function __construct(){
        $this->host = getenv('DB_HOST');
        $this->name = getenv('DB_NAME');
        $this->user = getenv('DB_USER');
        $this->pass = getenv('DB_PASS');
        $this->port = getenv('DB_PORT');

        $this->setConnection();
    }

    /**
     * Método responsável por criar uma conexão com o banco de dados
     */
    private function setConnection(){
        try{
            $this->connection = new PDO('mysql:host='.$this->host.';dbname='.$this->name.';port='.$this->port,$this->user,$this->pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            die('ERROR: '.$e->getMessage());
        }
    }

    /**
     * Método responsável por retornar a conexão
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
