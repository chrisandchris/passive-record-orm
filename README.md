# symfony-rowmapper
This bundle is a row mapper designed to use in symfony2 projects.

## Was it does
* Opens and handles MySQL-Connection
* Provides a simple interface for building prepared statements and querying the database
* Provides a simple interface for mapping the results to classes
* Caches the queries if required to save parsing time

## What is not documented
* How the query cache works
* How the internals work

## Internals
The basic internal principal is the following:
* There are Types (simple key-value classes) which represent a part of a statement
* They get appended to the query, this query could get cached
* The query gets parsed using a Parser and the same-named snippets (they contain sql)
* The query is returned

# How To Use
## Configuration
Configure your symfony2 project as you do always. The bundle uses the database
information stored in the parameters.yml and automatically connects to the given
database.

Actually, there is no further configuration possible.

## A simple query
You could extend the `ChrisAndChris\Common\RowMapperBundle\Services\Model\Model` class
for a simpler use in your services or you could inject the
`ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider` in your
custom model. I like to show you the usage with the first method:

Let's create a service

```xml
<service id="my_gold_project.demoModel"
         class="My\Gold\Project\Services\DemoModel">
    <argument type="service" id="common_rowmapper.dependencyProvider"/>
</service>
```

Create the model
```php
<?php
namespace \My\Gold\Project\Services;
class DemoModel extends ChrisAndChris\Common\RowMapperBundle\Services\Model\Model {
    public function getCustomerName($customerId) {
        $query = $this->getDependencyProvider->getBuilder->select()
            ->field('customer_name')
            ->table('customer')
            ->where()
                ->field('customer_id')->equals()->value($customerId)
            ->close()
            ->getSqlQuery();
        return $this->runWithFirstKeyFirstValue($query);
    }
}
```

If you want to map a more complicated query to a class, use something like this:
```php
<?php
namespace \My\Gold\Project\Entity;
class CustomerEntity implements ChrisAndChris\Common\RowMapperBundle\Entity\Entity {
    public $customerId;
    public $name;
    public $street;
    public $zip;
    public $city;
}
```

And to map, use this method
```php
<?php
namespace \My\Gold\Project\Services;
class DemoModel extends ChrisAndChris\Common\RowMapperBundle\Services\Model\Model {
    public function getCustomer($customerId) {
        $query = $this->getDependencyProvider()->getBuilder->select()
            ->fieldlist([
                'customer_id' => 'customerId',
                'cus_name' => 'name',
                'street',
                'zip',
                'city'
            ])
            ->table('customer')
            ->where()
                ->field('customer_id')->equals()->value($customerId)
            ->close()
            ->getSqlQuery();
        return $this->run($query, new \My\Gold\Project\Entity\CustomerEntity());
}
```
## Some more information

### The field() method
You could use an array for separating database, table, field:
```field(['database', 'table', 'field'])```

If you fetch single fields, you must append a comma by yourself:
```php
->field('field1')->c()
->field('field2')->c()
```

You could also give a closure as parameter:
```php
->field(function () { return $value; });
```

### The value() method
Use this method to append a parameter to the query:
```php
->value($someValue);
->value(function () { return $someValue; });
```

### The fieldlist() method
This method is even much more powerful, use it as follows:

Simple key-value usage:
```php
fieldlist([
    'field' => 'alias',
    'customer_id' => 'customerId',
    'cus_name' => 'name'
])
```

Specify database, table, field:
```php
fieldlist([
    'database:table:field' => 'alias'
]);
```

Mix anything
```php
fieldlist([
    'database:table:field' => 'alias',
    'field1', // fetched by real field name
    'field2' => 'alias1'
]);
```
### The f(), where(), order(), groupBy()
Any of these four types open so-called "braces". A brace represents a kind of
sub-query which is fully independent from the query before. In its internals, during
parsing this sub-query, the parser has principally no access to the other statements.

So, if you finish one of these, simply call close() or end() to close the brace:
```php
->where()
    ->field('field1')->equals()->value(true)
->close()
```

### The raw()
Because of the lack of time and to fulfill any requirement, I simply implemented
a raw method. And gladly, this method is able to use parameters :D

```php
->raw('SELECT customer_id FROM customer WHERE customer_name LIKE ?', [
    '%github%'
]);
```

### Conditional appending
There are three methods to provide conditional appending:
* _if()
* _else()
* _end()

You are allowed to nest ifs, and you are allowed to push a closure as parameter to the if:
```php
->_if($condition === true)
    ->where()
    ->_if(function() { return $condition === true; })
        // ...
    ->_end()
        // ...
    ->close()
->_else()
    //
->_end()

### Some other methods
* f() - for functions
* where() - build wheres
* any() - a god-blessed star (evil SELECT *)
* value() - a parameter
* null() - a sql NULL
* isNull() - compares to null using IS NULL
* join() - join tables
* using() - using clause for joined tables
* on() -  on clause for joined tables
