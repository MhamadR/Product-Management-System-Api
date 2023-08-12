<?php

namespace TestAssignment\Api;

use PDO;

class database
{
    public function __construct(
        private $host,
        private $name,
        private $user,
        private $password
    ) {
    }

    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host}; dbname={$this->name}; charset=utf8";

        return new PDO($dsn, $this->user, $this->password, [
            // Set the following PDO attributes
            // To not let PDO convert all values to string
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
    }
}
