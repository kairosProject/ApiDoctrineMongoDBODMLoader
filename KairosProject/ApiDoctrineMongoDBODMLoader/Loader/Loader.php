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
 * @category Api_Doctrine_MongoDB_ODM_Loader
 * @package  Kairos_Project
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
namespace KairosProject\ApiDoctrineMongoDBODMLoader\Loader;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use KairosProject\ApiController\Event\ProcessEventInterface;
use KairosProject\ApiLoader\Event\QueryBuildingEvent;
use KairosProject\ApiLoader\Event\QueryBuildingEventInterface;
use KairosProject\ApiLoader\Loader\AbstractApiLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Loader
 *
 * This class provide an implementation of the AbstractApiLoader to load items from a MongoDB database, by using
 * doctrine ODM.
 *
 * @category Api_Doctrine_MongoDB_ODM_Loader
 * @package  Kairos_Project
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
class Loader extends AbstractApiLoader
{
    /**
     * Request server
     *
     * Define the bag location as server attribute
     *
     * @var string
     */
    public const REQUEST_SERVER = 'getServerParams';

    /**
     * Request cookie
     *
     * Define the bag location as request cookie
     *
     * @var string
     */
    public const REQUEST_COOKIE = 'getCookieParams';

    /**
     * Request query
     *
     * Define the bag location as request query string
     *
     * @var string
     */
    public const REQUEST_QUERY = 'getQueryParams';

    /**
     * Request files
     *
     * Define the bag location as request files
     *
     * @var string
     */
    public const REQUEST_FILES = 'getUploadedFiles';

    /**
     * Request body
     *
     * Define the bag location as request body
     *
     * @var string
     */
    public const REQUEST_BODY = 'getParsedBody';

    /**
     * Request attributes
     *
     * Define the bag location as request attribute
     *
     * @var string
     */
    public const REQUEST_ATTRIBUTE = 'getAttributes';

    /**
     * Manager
     *
     * The doctrine document manager
     *
     * @var DocumentManager
     */
    private $manager;

    /**
     * Document name
     *
     * The managed document fully qualified name
     *
     * @var string
     */
    private $documentName;

    /**
     * Identifier field
     *
     * The document identifier field
     *
     * @var string
     */
    private $identifierField;

    /**
     * Request bag
     *
     * The request bag whence get the document identifier
     *
     * @var string
     */
    private $requestBag;

    /**
     * Request bag key
     *
     * The bag key whence get the document identifier value
     *
     * @var string
     */
    private $requestBagKey;

    /**
     * Constructor
     *
     * The default Loader constructor. Store the manager, document name, idientifier field, request bag and bak key.
     *
     * @param DocumentManager $manager             The doctrine document manager
     * @param string          $documentName        The managed document fully qualified name
     * @param string          $identifierField     The document identifier field
     * @param string          $requestBag          The request bag whence get the document identifier
     * @param string          $requestBagKey       The bag key whence get the document identifier value
     * @param LoggerInterface $logger              The application logger
     * @param string          $collectionEventName The collection event fired for extension
     * @param string          $itemEventName       The item ivent fired for extension
     * @param string          $eventKeyStorage     The key storage where is located the final result
     *
     * @return void
     */
    public function __construct(
        DocumentManager $manager,
        string $documentName,
        string $identifierField,
        string $requestBag,
        string $requestBagKey,
        LoggerInterface $logger,
        string $collectionEventName = self::COLLECTION_EVENT_NAME,
        string $itemEventName = self::ITEM_EVENT_NAME,
        string $eventKeyStorage = self::EVENT_KEY_STORAGE
    ) {
        parent::__construct($logger, $collectionEventName, $itemEventName, $eventKeyStorage);

        $this->manager = $manager;
        $this->documentName = $documentName;
        $this->identifierField = $identifierField;
        $this->requestBag = $requestBag;
        $this->requestBagKey = $requestBagKey;
    }

    /**
     * Get query building event.
     *
     * Return a new instance of QueryBuildingEvent to be used during the workflow.
     *
     * @param ProcessEventInterface $originalEvent The original event
     *
     * @return QueryBuildingEventInterface
     */
    protected function getQueryBuildingEvent(
        ProcessEventInterface $originalEvent
    ) : QueryBuildingEventInterface {
        $this->logger->debug('Creating new query building event');
        return new QueryBuildingEvent($originalEvent);
    }

    /**
     * Configure query for collection.
     *
     * This method configure the query builder to load a collection of item.
     *
     * @param QueryBuildingEventInterface $event      The query building event
     * @param string                      $eventName  The current event name
     * @param EventDispatcherInterface    $dispatcher The current event dispatcher
     *
     * @return                                        void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function configureQueryForCollection(
        QueryBuildingEventInterface $event,
        string $eventName,
        EventDispatcherInterface $dispatcher
    ) : void {
        $this->logger->debug('Configuring query builder for collection');
    }

    /**
     * Instanciate query builder.
     *
     * Create a new instance of query builder and inject it inside the QueryBuildingEvent instance.
     *
     * @param QueryBuildingEventInterface $event      The query building event
     * @param string                      $eventName  The current event name
     * @param EventDispatcherInterface    $dispatcher The current event dispatcher
     *
     * @return                                        void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function instanciateQueryBuilder(
        QueryBuildingEventInterface $event,
        string $eventName,
        EventDispatcherInterface $dispatcher
    ) : void {
        $this->logger->debug('Instanciate new query builder', ['document name' => $this->documentName]);

        $queryBuilder = $this->manager->createQueryBuilder($this->documentName);
        $event->setQuery($queryBuilder);
    }

    /**
     * Configure query for item.
     *
     * This method configure the query builder to load a specific item.
     *
     * @param QueryBuildingEventInterface $event      The query building event
     * @param string                      $eventName  The current event name
     * @param EventDispatcherInterface    $dispatcher The current event dispatcher
     *
     * @throws                                        \LogicException In case of unexisting request bag
     * @throws                                        \LogicException In case of unexisting key in the request bag
     * @return                                        void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function configureQueryForItem(
        QueryBuildingEventInterface $event,
        string $eventName,
        EventDispatcherInterface $dispatcher
    ) : void {
        $this->logger->debug('Configuring query builder for item');

        $documentId = $this->getParam($event);
        $this->logger->debug(
            'Configuring query builder for item',
            [
                'document' => $this->documentName,
                'id' => $documentId
            ]
        );

        $query = $event->getQuery();
        if (!$query instanceof Builder) {
            $this->logger->error(
                'Unsupported query builder type',
                [
                    'expected' => Builder::class,
                    'given' => is_object($query) ? get_class($query) : gettype($query)
                ]
            );
            throw new \LogicException(
                'Unsupported query builder type'
            );
        }

        $query->field($this->identifierField)->equals($documentId);
    }

    /**
     * Execute item query.
     *
     * This method execute the query and return the result as a specific item.
     *
     * @param QueryBuildingEventInterface $event      The query building event
     * @param string                      $eventName  The current event name
     * @param EventDispatcherInterface    $dispatcher The current event dispatcher
     *
     * @return                                        mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function executeItemQuery(
        QueryBuildingEventInterface $event,
        $eventName,
        EventDispatcherInterface $dispatcher
    ) {
        return $this->getQuery($event)->getQuery()->getSingleResult();
    }

    /**
     * Execute collection query.
     *
     * This method execute the query and return the result as a collection.
     *
     * @param QueryBuildingEventInterface $event      The query building event
     * @param string                      $eventName  The current event name
     * @param EventDispatcherInterface    $dispatcher The current event dispatcher
     *
     * @return                                        mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function executeCollectionQuery(
        QueryBuildingEventInterface $event,
        $eventName,
        EventDispatcherInterface $dispatcher
    ) {
            return $this->getQuery($event)->getQuery()->execute();
    }

    /**
     * Get query
     *
     * Return a query builder instance contained into the current query building event.
     *
     * @param QueryBuildingEventInterface $event The current query building event
     *
     * @throws \LogicException If the resulting query builder is not a valid builder
     *
     * @return Builder
     */
    private function getQuery(QueryBuildingEventInterface $event) : Builder
    {
        $query = $event->getQuery();
        if (!$query instanceof Builder) {
            $this->logger->error(
                'Unsupported query builder type',
                [
                    'expected' => Builder::class,
                    'given' => is_object($query) ? get_class($query) : gettype($query)
                ]
            );
            throw new \LogicException(
                'Unsupported query builder type'
            );
        }

        return $query;
    }

    /**
     * Get param
     *
     * Return the document identifier from the request.
     *
     * @param QueryBuildingEventInterface $event The current building event
     *
     * @throws \LogicException In case of unexisting request bag
     * @throws \LogicException In case of unexisting key in the request bag
     * @return mixed
     */
    private function getParam(QueryBuildingEventInterface $event)
    {
        $request = $event->getProcessEvent()->getRequest();

        if (!method_exists($request, $this->requestBag)) {
            $this->logger->error('The given bag does not exist in the request', ['bag' => $this->requestBag]);
            throw new \LogicException(
                'The given bag does not exist in the request'
            );
        }

        $requestBag = (array)$request->{$this->requestBag}();

        if (!array_key_exists($this->requestBagKey, $requestBag)) {
            $this->logger->error(
                'The given bag does not exist in the request',
                [
                    'bag' => $this->requestBag,
                    'key' => $this->requestBagKey
                ]
            );
            throw new \LogicException(
                'The given key does not exist in the request bag'
            );
        }

        return $requestBag[$this->requestBagKey];
    }
}
