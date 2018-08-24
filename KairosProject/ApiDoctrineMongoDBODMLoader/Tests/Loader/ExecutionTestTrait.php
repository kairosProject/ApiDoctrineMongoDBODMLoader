<?php
declare(strict_types=1);
/**
 * This file is part of the kairos project.
 *
 * As each files provides by the CSCFA, this file is licensed
 * under the MIT license.
 *
 * PHP version 7.2
 *
 * @category Api_Doctrine_MongoDB_ODM_Loader_Test
 * @package  Kairos_Project
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
namespace KairosProject\ApiDoctrineMongoDBODMLoader\Tests\Loader;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use KairosProject\ApiLoader\Event\QueryBuildingEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Execution test trait
 *
 * This class is used to validate the Loader instance execution methods.
 *
 * @category Api_Doctrine_MongoDB_ODM_Loader_Test
 * @package  Kairos_Project
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
trait ExecutionTestTrait
{
    /**
     * Test executeCollectionQuery
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::executeCollectionQuery method.
     *
     * @return void
     */
    public function testExecuteCollectionQuery()
    {
        $event = $this->createMock(QueryBuildingEvent::class);
        $queryBuilder = $this->createMock(Builder::class);
        $query = $this->createMock(Query::class);
        $queryResult = [new \stdClass()];

        $this->getInvocationBuilder($event, $this->once(), 'getQuery')
            ->willReturn($queryBuilder);
        $this->getInvocationBuilder($queryBuilder, $this->once(), 'getQuery')
            ->willReturn($query);
        $this->getInvocationBuilder($query, $this->once(), 'execute')
            ->willReturn($queryResult);

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class)
            ]
        );

        $method = $this->getClassMethod('executeCollectionQuery');

        $result = $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));

        $this->assertSame($queryResult, $result);
    }

    /**
     * Test executeItemQuery
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::executeItemQuery method.
     *
     * @return void
     */
    public function testExecuteItemQuery()
    {
        $event = $this->createMock(QueryBuildingEvent::class);
        $queryBuilder = $this->createMock(Builder::class);
        $query = $this->createMock(Query::class);
        $queryResult = new \stdClass();

        $this->getInvocationBuilder($event, $this->once(), 'getQuery')
            ->willReturn($queryBuilder);
        $this->getInvocationBuilder($queryBuilder, $this->once(), 'getQuery')
            ->willReturn($query);
        $this->getInvocationBuilder($query, $this->once(), 'getSingleResult')
            ->willReturn($queryResult);

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class)
            ]
        );

        $method = $this->getClassMethod('executeItemQuery');

        $result = $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));

        $this->assertSame($queryResult, $result);
    }

    /**
     * Query execution method provider
     *
     * Return a set of execution methods to be validated
     *
     * @return []
     */
    public function queryExecutionMethodProvider() : array
    {
        return [
            ['executeCollectionQuery'],
            ['executeItemQuery']
        ];
    }

    /**
     * Test executeQuery with query type error
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::executeCollectionQuery and
     * KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::executeItem methods in case of query builder type
     * mismatch.
     *
     * @param string $method The method to launch the query execution
     *
     * @return       void
     * @dataProvider queryExecutionMethodProvider
     */
    public function testExecuteQueryQueryError(string $method) : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported query builder type');

        $event = $this->createMock(QueryBuildingEvent::class);
        $queryBuilder = $this->createMock(\stdClass::class);

        $this->getInvocationBuilder($event, $this->once(), 'getQuery')
            ->willReturn($queryBuilder);

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class)
            ]
        );

        $method = $this->getClassMethod($method);

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }
}
