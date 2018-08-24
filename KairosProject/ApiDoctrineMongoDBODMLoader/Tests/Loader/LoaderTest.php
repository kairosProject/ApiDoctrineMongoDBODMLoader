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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use KairosProject\ApiController\Event\ProcessEventInterface;
use KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader;
use KairosProject\ApiLoader\Event\QueryBuildingEvent;
use KairosProject\Tests\AbstractTestClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    use ExecutionTestTrait,
        ConfigurationTestTrait;

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
