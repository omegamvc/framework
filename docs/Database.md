<p align="center">
    <a href="https://omegamvc.github.io" target="_blank">
        <img src="https://github.com/omegamvc/omega-assets/blob/main/images/logo-omega.png" alt="Omega Logo">
    </a>
</p>

# Database

## **Built in Query Builder**
of course we are support CRUD data base, this a sample

### Select data
```php
DB::table('table_name')
  ->select(['column_1'])
  ->equal('column_2', 'fast_mvc')
  ->order("column_1", MyQuery::ORDER_ASC)
  ->limit(1, 10)
  ->all()
;
```
the result will show data from query,
its same with SQL query
```SQL
SELECT `column_1` FROM `table_name` WHERE (`column_2` = 'fast_mvc') ORDER BY `table_name`.`column_1` ASC LIMIT 1, 10
```

### Update data
```php
DB::table('table_name')
  ->update()
  ->values([
    'column_1' => 'simple_mvc',
    'column_2' => 'fast_mvc',
    'column_3' => 123
  ])
  ->equal('column_4', 'fast_mvc')
  ->execute()
;
```
the result is boolean true if sql success execute query,
its same with SQL query
```SQL
UPDATE `table_name` SET `column_1` = 'simple_mvc', `column_2` = 'fast_mvc', 'column_3' = 123  WHERE (`column_4` = 'speed')
```

### Insert and Delete
```php
// insert
DB::table('table_name')
  ->insert()
  ->values([
    'column_1'  => '',
    'column_2'  => 'simple_mvc',
    'column_3'  => 'fast_mvc'
    ])
  ->execute()
;

// delete
DB::table('table_name')
  ->delete()
  ->equal('column_3', 'slow_mvc')
  ->execute()
;
```
its supported cancel translation if you needed
```php
use Omega\Support\Facades;

PDO::transaction(function() {
    DB::table('table_name')
        ->insert()
        ->value('age', 22)
        ->execute()
    ;

    // some condition
    if (false === $statement) {
        return false;
    }

    return true;
});
```

### Create Database Table
create database table
```php
  Schema::table('users', function(Column $column) {
    $column('user')->varchar(50);
    $column('pwd')->varchar(500)->notNull();
    $column->primaryKeys('user');
  })
  ->execute();
```

## License

This project is open-source software licensed under the [GNU General Public License v3.0](LICENSE).