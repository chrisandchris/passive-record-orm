# Mapping and Searching

## Mapping
You can use mapping to create a `json` file with basic information about your database in it. Mapping is as simple as running the following command in your application root

```
php app/console cac:database:mapper
```

This reads the configured database and writes the generated output to the cache dir `cache/common_rowmapper/mapping.json`.

## Searching
The bundle provides an extended interface for searching in databases using the previous generated mapping file. It validates relations (foreign keys) and column names itself and creates, based on that validation, automatically a search query.

```
// Basic, required settings
$search = new SearchContainer();
$search->term = 'term';
$search->rootTable = 'customer';

// complete search query and build the sql statement
$utility = new SearchQueryBuilder();
$query = $utility->buildSearchQuery(
    $utility->buildSearchContainer($search)
);

// and now something like that...
$pdo->exec($query->getSqlQuery());
```

In default settings, the `SearchQueryBuilder::buildSearchQuery()` method does the following:

1. Validate the table
2. Get recursive relations, with configured deep-level (default to 3)
3. Get all fields available in previously fetched tables
4. Put in the primary key column
5. Then return the completed `SearchContainer`

As you see, this does a lot and probably servers up more results than you like. So you can customize what you like, by adding more information to the `SearchContainer` before executing:

```
// making customized joins, only these tables get looked up by default
// this, anyway, still looks up every field of the joined tables
$search->joinedTables = [
    'joined_table => 'joined_field'
];

// now just those fields are looked up
// you must join the tables manually
$search->lookupFields = [
    'field_1',                  // field on primary table
    'joined_table:joined_field  // field on joined table
];

// only results of the given search result are looked up
// so you could search a search a search a search ;)
$search->searchId = 1234;
```

### Using the search
With the methods, the `SearchResultUtility` provides, you are easily able to use the performed search. The probably most useful method is the `SearchResultUtility::getInStatement()` method which returns a builder part to append which looks just like this:

```
IN (
    SELECT [primary_key]
    FROM search_[table]
    WHERE
        search_id = ?
)
```

You can now easily filter a prepared list query by appending this builder part to the where clause.
