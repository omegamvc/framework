<p align="center">
    <a href="https://omegamvc.github.io" target="_blank">
        <img src="https://github.com/omegamvc/omega-assets/blob/main/images/logo-omega.png" alt="Omega Logo">
    </a>
</p>

# Console

Assembling beautifully console app make easy

- naming parameter
- coloring console (text and background)

### Build simple console app
```php
class GreatConsole extends Console
{
  public function main()
  {
    // getter to get param form cli argument
    $name = $this->name ?? 'animus';

    style("Great console Application")
    	->textGreen()
        ->newLines()
        ->push("hay my name is ")
        ->push($name)
        ->textYellow()
        ->out()
    ;
  }
}
```

**Run your app**

- create bootstrapper
```php
#!usr/bin/env php

// $argv come with default global php
return (new greatConsole($argv))->main();

```

- on your console
```bash
php cli create --name php_mvc

# output:
# Great console application
# hay my name is php_mvc

## License

This project is open-source software licensed under the [GNU General Public License v3.0](LICENSE).