<p align="center">
    <a href="https://omegamvc.github.io" target="_blank">
        <img src="https://github.com/omegamvc/omega-assets/blob/main/images/logo-omega.png" alt="Omega Logo">
    </a>
</p>

# Collection

## Collection
Array collection, handel functional array as chain method

### Create New Collection
```php
$coll = new Collection(['vb_net', 'c_sharp', 'java', 'python', 'php', 'javascript', 'html']);

$arr = $coll
  ->remove('html')
  ->sort()
  ->filter(fn ($item) => strlen($item) > 4)
  ->map(fn ($item) => ucfirst($item))
  ->each(function($item) {
    echo $item . PHP_EOL;
  })
  ->all()
;

// arr = ['c_sharp', 'javascript', 'python', 'vb_net']
```

### Available Methods
- `add()`
- `remove()`
- `set()`
- `clear()`
- `replace()`
- `each()`
- `map`
- `filter()`
- `sort()`
- `sortDesc()`
- `sortKey()`
- `sortKeyDesc()`
- `sortBy()`
- `sortByDesc()`
- `all()`

## License

This project is open-source software licensed under the [GNU General Public License v3.0](LICENSE).