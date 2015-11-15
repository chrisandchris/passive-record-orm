# UPDATE demonstration

With version *2.1.0* we have done a lot of improvements in simplifying the API
to make it even easier to query the database. One of the major improvements
was this enhancement in building updates.

## Simple example
SQL pendant

```sql
UPDATE `table` SET
    `field_1` = 1,
    `field_2` = 2
```

With query builder

```php
$builder->update('table')
->updates([
    ['field_1', 1],
    ['field_2', 2]
]);
```
