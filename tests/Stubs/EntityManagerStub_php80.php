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
    /** @var Driver */
    private $driver;

    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    public function getCache(): void
    {
    }

    public function getConnection(): Connection
    {
        return new class([], $this->driver) extends Connection {
        };
    }

    public function getExpressionBuilder(): void
    {
    }

    public function beginTransaction(): void
    {
    }

    public function transactional($func): void
    {
    }

    public function commit(): void
    {
    }

    public function rollback(): void
    {
    }

    public function createQuery($dql = ''): void
    {
    }

    public function createNamedQuery($name): void
    {
    }

    public function createNativeQuery($sql, ResultSetMapping $rsm): void
    {
    }

    public function createNamedNativeQuery($name): void
    {
    }

    public function createQueryBuilder(): void
    {
    }

    public function getReference($entityName, $id): void
    {
    }

    public function getPartialReference($entityName, $identifier): void
    {
    }

    public function close(): void
    {
    }

    public function copy($entity, $deep = false): void
    {
    }

    public function lock($entity, $lockMode, $lockVersion = null): void
    {
    }

    public function getEventManager(): void
    {
    }

    public function getConfiguration(): void
    {
    }

    public function isOpen(): void
    {
    }

    public function getUnitOfWork(): void
    {
    }

    public function getHydrator($hydrationMode): void
    {
    }

    public function newHydrator($hydrationMode): void
    {
    }

    public function getProxyFactory(): void
    {
    }

    public function getFilters(): void
    {
    }

    public function isFiltersStateClean(): void
    {
    }

    public function hasFilters(): void
    {
    }
}
