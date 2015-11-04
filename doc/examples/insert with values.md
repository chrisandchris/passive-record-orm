# INSERT with values statement
## Simple example
SQL pendant

```sql
INSERT INTO `user` (username, password, salt) 
VALUES ('root', 'root', 'aa-bb-cc-dd');
```

With query builder

```php
$builder->insert('user')
->brace()
->fieldlist([
    'username',
    'password',
    'salt'
])->close()
->values([
    [
        'root',
        'root',
        'aa-bb-cc-dd'
    ]
]);
```

## Inserting multiple rows
SQL pendant

```sql
INSERT INTO `user` (username, password, salt) 
VALUES ('root', 'root', 'aa-bb-cc-dd'),
    ('admin', 'admin', 'cc-aa-bb-dd');
```

With query builder

```php
$builder->insert('usert')
->brace()
->fieldlist([
    'username',
    'password',
    'salt
]);
->values([
    [
        'root',
        'root',
        'aa-bb-cc-dd',
    ],
    [
        'admin',
        'admin',
        'cc-aa-bb-dd'
    ]
]);
```
