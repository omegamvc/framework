<p align="center">
    <a href="https://omegamvc.github.io" target="_blank">
        <img src="https://github.com/omegamvc/omega-assets/blob/main/images/logo-omega.png" alt="Omega Logo">
    </a>
</p>

# Str

Make string manipulation.

```php
Str::chartAt('i love php', 3); // o
Str::concat(['i', 'love', 'php']); // i love php
Str::indexOf('i love php', 'p'); // 8
Str::lastIndexOf('i love php', 'p'); // 10
Str::match('i love php', '/love/'); // love
// ...
// and many more
```
- `chartAt`
- `concat`
- `indexOf`
- `lastIndexOf`
- `match`
- `slice`
- `split`
- `replace`
- `toUpperCase`
- `toLowerCase`
- `firstUpper`
- `firstUpperAll`
- `toSnackCase`
- `toKebabCase`
- `toPascalCase`
- `toCamelCase`
- `contains`
- `startsWith`
- `endsWith`
- `slug`
- `template`
- `length`
- `repeat`
- `isString`
- `isEmpty`
- `fill`
- `fillEnd`
- `limit`

### Custom macro

custom macro string;

```php
Str::macro('prefix', fn($text, $prefix) => $prefix.$test);

echo Str::prefix('cool', 'its '); // its cool
```

### String class

use chain string class.

```php
$string = new Text('I Love rust');

echo $string->replace('rust', 'php')->lower()->slug();
// i-love-php

echo $string->length(); // 10
echo $string->isEmpty(); // false
```

### String Regex

```php
Str::is('some@email.com', Regex::EMAIL); // true
```

available regex
- `email`
- `user`
- `plain_text`
- `slug`
- `html_tag`
- `js_inline`
- `password_complex`
- `password_moderate`
- `date_yyyymmdd`
- `date_ddmmyyyy`
- `date_ddmmmyyyy`
- `ip4`
- `ip6`
- `ip4_6`
- `url`

## License

This project is open-source software licensed under the [GNU General Public License v3.0](LICENSE).