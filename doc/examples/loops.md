# LOOPs demonstration

Beginning with *2.1.0*, we support now a series of loops, which makes it easier
to build custom and dynamic queries. Using the loop functionality is especially
helpful when building queries (like search queries) based on user input.

## each()
The `each()` method is comparable with a `foreach` in PHP.

SQL pendant
*this query might also be done using the in() function*

```sql
SELECT `field` FROM `table`
WHERE `field`
IN(1, 2, 3, 4, 5, 6)
```

With query builder

```php
$builder->select()
    ->field('field')
    ->table('table')
    ->where()
        ->field('field')
        ->raw('IN')->brace()
            ->each([1, 2, 3, 4, 5, 6], function ($item, $isLastItem) {
                $builder = $this->getBuilder()  // get a new builder
                return $builder->value($item)
                    ->_if(!$isLastItem)->c()->_end()
            });
        ->close()
    ->close()
```

## asLong()
The `asLong()` method is comparable to `while` in PHP.

SQL pendant
*this query might also be done using the in() function*

```sql
SELECT `field` FROM `table`
WHERE `field`
IN(1, 2, 3, 4, 5, 6)
```

With query builder

```php
$values = [1, 2, 3, 4, 5, 6];
static $index = 0;

$builder->select()
    ->field('field')
    ->table('table')
    ->where()
        ->field('field')
        ->raw('IN')->brace()
            ->asLong(function () use ($values, $index) {
                return ++$index < count($values);
            }, function () use ($values, $index) {
                $builder = $this->getBuilder(); // get a new builder
                
                return $builder->value($values[$index - 1])
                    ->_if($index < count($values))->c()->_end();
            }
        ->close()
    ->close()
```
