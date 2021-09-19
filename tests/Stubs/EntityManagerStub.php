<?php

declare(strict_types=1);

/*
 * This file is part of the Orbitale ArrayFixture package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Orbitale\Component\ArrayFixture\Stubs;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class EntityManagerStub extends ObjectManagerStub implements EntityManagerInterface
{
    public function getCache()
    {
    }

    public function getConnection(): Connection
    {
        return new class([], $this->createDriver()) extends Connection {};
    }

    public function getExpressionBuilder()
    {
        //
    }

    public function beginTransaction()
    {
        //
    }

    public function transactional($func)
    {
        //
    }

    public function commit()
    {
        //
    }

    public function rollback()
    {
        //
    }

    public function createQuery($dql = '')
    {
        //
    }

    public function createNamedQuery($name)
    {
        //
    }

    public function createNativeQuery($sql, ResultSetMapping $rsm)
    {
        //
    }

    public function createNamedNativeQuery($name)
    {
        //
    }

    public function createQueryBuilder()
    {
        //
    }

    public function getReference($entityName, $id)
    {
        //
    }

    public function getPartialReference($entityName, $identifier)
    {
        //
    }

    public function close()
    {
        //
    }

    public function copy($entity, $deep = false)
    {
        //
    }

    public function lock($entity, $lockMode, $lockVersion = null)
    {
        //
    }

    public function getEventManager()
    {
        //
    }

    public function getConfiguration()
    {
        //
    }

    public function isOpen()
    {
        //
    }

    public function getUnitOfWork()
    {
        //
    }

    public function getHydrator($hydrationMode)
    {
        //
    }

    public function newHydrator($hydrationMode)
    {
        //
    }

    public function getProxyFactory()
    {
        //
    }

    public function getFilters()
    {
        //
    }

    public function isFiltersStateClean()
    {
        //
    }

    public function hasFilters()
    {
        //
    }

    private function createDriver()
    {
        return new class implements Driver {
            public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
            {
            }

            public function getDatabasePlatform()
            {
            }

            public function getSchemaManager(Connection $conn)
            {
            }

            public function getName()
            {
            }

            public function getDatabase(Connection $conn)
            {
            }
        };
    }
}
