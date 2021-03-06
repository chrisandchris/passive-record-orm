# A QueryBuilder and passive record ORM for Symfony2

[![Build Status](https://travis-ci.org/chrisandchris/passive-record-orm.svg?branch=master)](https://travis-ci.org/chrisandchris/passive-record-orm)
[![Code Climate](https://codeclimate.com/github/chrisandchris/passive-record-orm/badges/gpa.svg)](https://codeclimate.com/github/chrisandchris/passive-record-orm)
[![Test Coverage](https://codeclimate.com/github/chrisandchris/passive-record-orm/badges/coverage.svg)](https://codeclimate.com/github/chrisandchris/passive-record-orm/coverage)
[![Version](https://img.shields.io/packagist/v/chrisandchris/passive-record-orm.svg)](https://packagist.org/packages/chrisandchris/passive-record-orm)
[![Downloads](https://img.shields.io/packagist/dt/chrisandchris/passive-record-orm.svg)](https://packagist.org/packages/chrisandchris/passive-record-orm)
[![Licence](https://img.shields.io/packagist/l/chrisandchris/passive-record-orm.svg)](https://github.com/chrisandchris/passive-record-orm/blob/master/LICENSE)

Despite it's package name, it's not simply a row mapper. And it's not simply for Symfony.
This project is a *QueryBuilder* and a *Mapper for SQL Result Sets*,
both combined but still very separated, so you can use them independent.

```php
<?php

use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;

class DemoRepo {
    /** @var ConcreteModel  */
    private $model;
    
    public function __construct(ConcreteModel $model){
        $this->model = $model;
    }
    
    public function getCustomerName($customerId) {
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->field('customer_name')
            ->table('customer')
            ->where()
                ->field('customer_id')->equals()->value($customerId)
            ->close()
            ->getSqlQuery();

        return $this->model->runWithFirstKeyFirstValue($query);
    }
}
```

This doc gives a short overview of all the possibilities this package provides.
We are moving the contents continuously to the `doc/` directory, so look more detailed
information up there.

## Was it does
* Opens and handles MySQL-Connection
* Provides a simple interface for building prepared statements and querying the database
* Provides a simple interface for mapping the results to classes

## Internals
The basic internal principal is the following:

* There are Types (simple key-value classes) which represent a part of a statement
* The query gets parsed using a Parser and the same-named snippets (they contain sql)
* The query is returned

# How To Use
## Configuration
Configure your symfony2 project as you do always. The bundle uses the database
information stored in the parameters.yml and automatically connects to the given
database.

Actually, there is no further configuration possible.

## A simple query
Let's create a service definition:
```yml
services:
    project.demo_repo:
        class: DemoRepo
        arguments: ['@common_rowmapper.model']
```

Create the repository:
```php
<?php

use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;

class DemoRepo {
    /** @var ConcreteModel  */
    private $model;
    
    public function __construct(ConcreteModel $model){
        $this->model = $model;
    }
    
    public function getCustomerName($customerId) {
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->field('customer_name')
            ->table('customer')
            ->where()
                ->field('customer_id')->equals()->value($customerId)
            ->close()
            ->getSqlQuery();

        return $this->model->runWithFirstKeyFirstValue($query);
    }
}
```

If you want to map a more complicated query to a class, use something like this:
```php
<?php

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;

class CustomerEntity implements Entity {
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

use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;

class DemoModel {
    
    /** @var ConcreteModel  */
    private $model;
    
    public function __construct(ConcreteModel $model){
        $this->model = $model;
    }
    
    public function getCustomer($customerId) {
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
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

        return $this->model->run($query, new SomeEntity());
    }
}
```
## Some more information

### The field() method
You could use an array for separating database, table, field:
```
field(['database', 'table', 'field'])`
```

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

### The in()
You can simply build IN-clauses with the two following methods:

```php
// option a
->in([1, 2, 3, 4, 5, 6])
// option b
->in()
    ->select()
    ->value(1)
->close()
```

Option A uses prepared statements all-the-way, any value within the array gets
is way as a parameter to the database.

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
```

### Some other methods
* f() - for functions
* where() - build wheres
* any() - a god-blessed star (evil `SELECT *`)
* value() - a parameter
* null() - a sql `NULL`
* isNull() - compares to null using `IS NULL`
* join() - join tables
* using() - using clause for joined tables
* on() -  on clause for joined tables
* union() - create `UNION` statements
* asLong() - creating while loop
* each() - creating each loop
