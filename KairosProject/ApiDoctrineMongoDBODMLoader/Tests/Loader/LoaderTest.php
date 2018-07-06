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

use KairosProject\Tests\AbstractTestClass;
use KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader;
use Psr\Log\LoggerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use KairosProject\ApiController\Event\ProcessEventInterface;
use KairosProject\ApiLoader\Event\QueryBuildingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ODM\MongoDB\Query\Builder;
use KairosProject\ApiController\Event\ProcessEvent;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\ODM\MongoDB\Query\Query;

/**
 * Loader test
 *
 * This class is used to validate the Loader instance.
 *
 * @category Api_Doctrine_MongoDB_ODM_Loader_Test
 * @package  Kairos_Project
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
class LoaderTest extends AbstractTestClass
{
    /**
     * Test constructor.
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::_construct method.
     *
     * @return void
     */
    public function testConstructor()
    {
        $this->assertConstructor(
            [
                'same:manager' => $this->createMock(DocumentManager::class),
                'documentName' => \stdClass::class,
                'identifierField' => 'id',
                'requestBag' => Loader::REQUEST_ATTRIBUTE,
                'requestBagKey' => 'attrName',
                'same:logger' => $this->createMock(LoggerInterface::class)
            ]
        );
    }

    /**
     * Test getQueryBuildingEvent.
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::getQueryBuildingEvent method.
     *
     * @return void
     */
    public function testGetQueryBuildingEvent()
    {
        $instance = $this->getInstance(['logger' => $this->createMock(LoggerInterface::class)]);

        $originalEvent = $this->createMock(ProcessEventInterface::class);
        $method = $this->getClassMethod('getQueryBuildingEvent');

        $result = $method->invoke($instance, $originalEvent);

        $this->assertInstanceOf(QueryBuildingEvent::class, $result);
        $this->assertSame($originalEvent, $result->getProcessEvent());
    }

    /**
     * Test configureQueryForCollection.
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::configureQueryForCollection
     * method.
     *
     * @return void
     */
    public function testConfigureQueryForCollection()
    {
        $instance = $this->getInstance(['logger' => $this->createMock(LoggerInterface::class)]);

        $event = $this->createMock(QueryBuildingEvent::class);
        $method = $this->getClassMethod('configureQueryForCollection');

        $this->getInvocationBuilder($event, $this->never(), 'getProcessEvent');
        $this->getInvocationBuilder($event, $this->never(), 'getQuery');
        $this->getInvocationBuilder($event, $this->never(), 'setQuery');

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }

    /**
     * Test instanciateQueryBuilder.
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::instanciateQueryBuilder
     * method.
     *
     * @return void
     */
    public function testInstanciateQueryBuilder()
    {
        $documentManager = $this->createMock(DocumentManager::class);
        $documentClass = \stdClass::class;
        $queryBuilder = $this->createMock(Builder::class);
        $event = $this->createMock(QueryBuildingEvent::class);

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class),
                'manager' => $documentManager,
                'documentName' => $documentClass
            ]
        );

        $method = $this->getClassMethod('instanciateQueryBuilder');

        $this->getInvocationBuilder($documentManager, $this->once(), 'createQueryBuilder')
            ->with($this->equalTo($documentClass))
            ->willReturn($queryBuilder);

        $this->getInvocationBuilder($event, $this->once(), 'setQuery')
            ->with($this->identicalTo($queryBuilder));

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }

    /**
     * Test configureQueryForItem.
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::configureQueryForItem method.
     *
     * @return void
     */
    public function testConfigureQueryForItem()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $originalEvent = $this->createMock(ProcessEvent::class);
        $event = $this->createMock(QueryBuildingEvent::class);
        $queryBuilder = $this->createMock(Builder::class);
        $bagKey = 'bagKey';
        $bagValue = 'value';
        $identifierField = 'id';

        $this->getInvocationBuilder($event, $this->once(), 'getQuery')
            ->willReturn($queryBuilder);
        $this->getInvocationBuilder($event, $this->once(), 'getProcessEvent')
            ->willReturn($originalEvent);
        $this->getInvocationBuilder($originalEvent, $this->once(), 'getRequest')
            ->willReturn($request);
        $this->getInvocationBuilder($request, $this->once(), Loader::REQUEST_BODY)
            ->willReturn([$bagKey => $bagValue]);
        $this->getInvocationBuilder($queryBuilder, $this->once(), 'field')
            ->with($this->equalTo($identifierField))
            ->willReturn($queryBuilder);
        $this->getInvocationBuilder($queryBuilder, $this->once(), 'equals')
            ->with($this->equalTo($bagValue));

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class),
                'requestBag' => Loader::REQUEST_BODY,
                'requestBagKey' => $bagKey,
                'identifierField' => $identifierField
            ]
        );

        $method = $this->getClassMethod('configureQueryForItem');

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }

    /**
     * Test configureQueryForItem with query error
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::configureQueryForItem method
     * in case of query type mismatch.
     *
     * @return void
     */
    public function testConfigureQueryForItemQueryError()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported query builder type');

        $request = $this->createMock(ServerRequestInterface::class);
        $originalEvent = $this->createMock(ProcessEvent::class);
        $event = $this->createMock(QueryBuildingEvent::class);
        $bagKey = 'bagKey';
        $bagValue = 'value';

        $this->getInvocationBuilder($event, $this->once(), 'getQuery')
            ->willReturn($this->createMock(\stdClass::class));
        $this->getInvocationBuilder($event, $this->once(), 'getProcessEvent')
            ->willReturn($originalEvent);
        $this->getInvocationBuilder($originalEvent, $this->once(), 'getRequest')
            ->willReturn($request);
        $this->getInvocationBuilder($request, $this->once(), Loader::REQUEST_BODY)
            ->willReturn([$bagKey => $bagValue]);

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class),
                'requestBag' => Loader::REQUEST_BODY,
                'requestBagKey' => $bagKey
            ]
        );

        $method = $this->getClassMethod('configureQueryForItem');

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }

    /**
     * Test configureQueryForItem with bag key error
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::configureQueryForItem method
     * in case of unexisting bag key.
     *
     * @return void
     */
    public function testConfigureQueryForItemBagKeyError()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The given key does not exist in the request bag');

        $request = $this->createMock(ServerRequestInterface::class);
        $originalEvent = $this->createMock(ProcessEvent::class);
        $event = $this->createMock(QueryBuildingEvent::class);

        $this->getInvocationBuilder($event, $this->once(), 'getProcessEvent')
            ->willReturn($originalEvent);
        $this->getInvocationBuilder($originalEvent, $this->once(), 'getRequest')
            ->willReturn($request);
        $this->getInvocationBuilder($request, $this->once(), Loader::REQUEST_BODY)
            ->willReturn(['a' => 'b']);

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class),
                'requestBag' => Loader::REQUEST_BODY,
                'requestBagKey' => 'c'
            ]
        );

        $method = $this->getClassMethod('configureQueryForItem');

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }

    /**
     * Test configureQueryForItem with bag error
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::configureQueryForItem method
     * in case of unexisting bag.
     *
     * @return void
     */
    public function testConfigureQueryForItemBagError()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The given bag does not exist in the request');

        $request = $this->createMock(ServerRequestInterface::class);
        $originalEvent = $this->createMock(ProcessEvent::class);
        $event = $this->createMock(QueryBuildingEvent::class);

        $this->getInvocationBuilder($event, $this->once(), 'getProcessEvent')
            ->willReturn($originalEvent);
        $this->getInvocationBuilder($originalEvent, $this->once(), 'getRequest')
            ->willReturn($request);

        $instance = $this->getInstance(
            [
                'logger' => $this->createMock(LoggerInterface::class),
                'requestBag' => 'unexistingMethod'
            ]
        );

        $method = $this->getClassMethod('configureQueryForItem');

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }

    /**
     * Test executeQuery
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::executeQuery method.
     *
     * @return void
     */
    public function testExecuteQuery()
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

        $method = $this->getClassMethod('executeQuery');

        $result = $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));

        $this->assertSame($queryResult, $result);
    }

    /**
     * Test executeQuery with query type error
     *
     * This method validate the KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader::executeQuery method in case
     * of query builder type mismatch.
     *
     * @return void
     */
    public function testExecuteQueryQueryError()
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

        $method = $this->getClassMethod('executeQuery');

        $method->invoke($instance, $event, 'eventName', $this->createMock(EventDispatcherInterface::class));
    }

    /**
     * Get tested class
     *
     * Return the tested class name
     *
     * @return string
     */
    protected function getTestedClass() : string
    {
        return Loader::class;
    }
}
