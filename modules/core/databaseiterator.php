<?php
namespace CameraLife;

/*
 * PDO implementation of the iterator class
 */
class PDOIterator
{
    private $myResult;

    public function __construct($mysqlResult)
    {
        $this->myResult = $mysqlResult;
    }

    public function fetchAssoc()
    {
        return $this->myResult->fetch(\PDO::FETCH_ASSOC);
    }
}
