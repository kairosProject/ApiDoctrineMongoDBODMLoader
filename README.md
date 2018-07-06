# ApiDoctrineMongoDBODMLoader

The doctrine ODM component of the kairos API. Aim to be used as data access object for MongoDB

## 1)  Subject

The doctrine MongoDB ODM loader is an implementation of the abstract API loading system. It is in charge of the data access in front of a MongoDB database and uses doctrine MongoDB ODM library.

## 2) Class architecture

The doctrine MongoDB loader is a simple inheritance of the API loader.

## 3) Dependency description and use into the element

A the time of writing, the doctrine MongoDB loader is designed to have four production dependencies as:

 * psr/log
 * symfony/event-dispatcher
 * kairos-project/api-controller
 * kairos-project/api-loader

### 3.1) psr/log

The debugging and error retracement in each project parts is currently a fundamental law in development and it's missing is part of the OWASP top ten threats.

As defined by the third PHP standard reference, the logger components have to implement a specific interface. By the way, the logging system will be usable by each existing frameworks.

### 3.2) symfony/event-dispatcher

The loader system is designed to be easily extendable and will implement an event dispatching system, allowing the attachment and separation of logic by priority.

### 3.3) kairos-project/api-controller

The loader is made to be used by APIs and the generic system into kairos project is the API controller. This system offer access to specialized workflow events.

The loader will define the controller component as a dependency to make use of the workflow events.

### 3.4) kairos-project/api-loader

The doctrine MongoDB loader inherits the API loader. It uses the base loader logic and has to define it as a dependency.

## 4) Implementation specification

To inherit the abstract API loader, this component will have to define the abstract methods methods:
 * getQueryBuildingEvent
 * instanciateQueryBuilder
 * executeQuery
 * configureQueryForCollection
 * configureQueryForItem


#### 4.1) Dependency injection specification

The document class has to be known to create a new query builder object. This information has to be introduced by the loader constructor.

The system needs to take the item identifier from the request. Two parameters will be introduced to determine its place, and one more to define the identifier field. So the bag name, the parameter key and the identifier name have to be injected.

A document manager has to be provided at the instantiation to access the connection.

#### 4.2) getQueryBuildingEvent algorithm

The getQueryBuildingEvent method is in charge of the query building event instantiation.

```txt
We assume to receive the process event from the parameters.

The method instantiates a new QueryBuildingEvent and injects the process event in the constructor.

Finally, the new event is passing back.
```

#### 4.3) instanciateQueryBuilder algorithm

The instanciateQueryBuilder method will create a new query builder instance to be used by the configuration and execution methods.

```txt
We assume to receive the query building event from the parameters.

The method instantiates a new query builder from doctrine document manager. The document name stored inside the documentName attribute used as a constructor argument.

The query building event will receive the builder.
```

#### 4.4) configureQueryForItem algorithm

The configureQueryForItem method will configure the query builder instance to load a specific element.

```txt
We assume to receive the query building event from the parameters.
We assume the query builder as query building event part.
We assume the bag name and parameter key as loader attribute.
We assume the identifier field name as loader attribute.

Get the item identifier from the request bag, using the bag name and parameter key.
Define the query field equality clause, regarding the identifier field.
```

#### 4.5) configureQueryForCollection algorithm

The configureQueryForCollection method will configure the query builder instance to load a set of elements. This method will stay empty for the MongoDB state. In matter, the instanciateQueryBuilder method is adequate.

#### 4.6) executeQuery algorithm

The executeQuery method will return the query result.

```txt
We assume to receive the query building event from the parameters.
We assume the query builder as query building event part.

Execute the query.
Return the query execution result.
```

## 5) Usage

#### 5.1) Basic usage

```PHP
use KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader;
use Symfony\Component\EventDispatcher\EventDispatcher;

// Instanciating event dispatcher
$eventDispatcher = new EventDispatcher();

$loader = new Loader(
    Document::class,
    'id',
    Loader::REQUEST_ATTRIBUTE,
    'document_id'
    $logger
);

$documentList = $loader->loadCollection($event, 'event_name', $eventDispatcher);

$document = $loader->loadItem($event, 'event_name', $eventDispatcher);
$item = $event->getParameter(Loader::EVENT_KEY_STORAGE);
```

#### 5.2) Complete constructor usage

```PHP
public function __construct(
        string $documentName,
        string $identifierField,
        string $requestBag,
        string $requestBagKey,
        LoggerInterface $logger,
        string $collectionEventName = self::COLLECTION_EVENT_NAME,
        string $itemEventName = self::ITEM_EVENT_NAME,
        string $eventKeyStorage = self::EVENT_KEY_STORAGE
);
```

#### 5.3) Loading with extension

```PHP
use KairosProject\ApiDoctrineMongoDBODMLoader\Loader\Loader;
use Symfony\Component\EventDispatcher\EventDispatcher;

// Instanciating event dispatcher
$eventDispatcher = new EventDispatcher();

$eventDispatcher->addListener(
    Loader::COLLECTION_EVENT_NAME,
    [
        $extension,
        'someFunction'
    ]
);

$loader = new Loader(
    Document::class,
    'id',
    Loader::REQUEST_ATTRIBUTE,
    'document_id'
    $logger
);

$documentList = $loader->loadCollection($event, 'event_name', $eventDispatcher);
```
