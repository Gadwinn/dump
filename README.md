# gadwinn/dump

Readable variable dumps for debugging. Three global helpers, an HTML theme for the browser and a text theme for the command line, and automatic decoding of JSON and serialized strings.

```php
egdump($user, $_GET);
```

```text
/var/www/app/src/Controller/UserController.php:42
object(App\User)
|- public $id = int 7
|- protected $email = string(16) 'john@example.com'
|- private $roles = array(2)
|  |- 0 => string(5) 'admin'
|  |- 1 => string(6) 'editor'

array(1)
|- 'page' => string(1) '2'
```

## Requirements

- PHP >= 8.3
- `ext-mbstring`

## Installation

```bash
composer require --dev gadwinn/dump
```

The helpers are registered by Composer's autoloader; nothing else to set up.

## Helpers

| Function | Behaviour |
|---|---|
| `gdump(mixed ...$vars): string` | Returns the dump |
| `egdump(mixed ...$vars): void` | Echoes the dump |
| `dgdump(mixed ...$vars): never` | Echoes the dump and stops the script |

Every dump starts with the file and line of the call. The HTML theme is used in a web context, the text theme on the command line.

A helper is only defined when no function of that name exists yet, so you can declare your own first.

## What is displayed

- **Scalars** with their type; strings with their length in characters, and in bytes when it differs (`string(3 - size=5) 'été'`).
- **Arrays** and **objects**, nested, with the visibility of each property (`public`, `protected`, `private`, `static`). Uninitialized typed properties are skipped.
- **Exceptions** with code, message, file, line, trace and previous exception.
- **Resources**, open or closed.
- **JSON strings** (`json`), **JSON with escaped quotes** (`json slashed`) and **`serialize()` output** (`serialized`) are decoded and shown as structures.
- `*RECURSION*` when an object contains itself, `*MAX DEPTH*` beyond the nesting limit (10 levels by default).

### Safety

- With the HTML theme, every value, key, property name, class name and message is HTML-escaped: dumping user input cannot inject markup or scripts into the page.
- A serialized string is decoded without instantiating any class (objects appear as `__PHP_Incomplete_Class`): dumping a string never runs `__wakeup()` or `__destruct()`.

This is a development tool: a dump exposes internal data, do not leave calls in production code.

## Configuration

```php
use G\Dump\Theme\ThemeHTML;
use G\Dump\Theme\ThemeText;

// Custom themes and nesting limit for gdump(), egdump() and dgdump()
gdumpOptions(themeHtml: new MyTheme(), maxDepth: 5);
```

`gdumpOptions()` returns the shared `Dumper`; an argument left to `null` keeps the current setting.

## Using the class directly

```php
use G\Dump\Dumper;
use G\Dump\Theme\ThemeText;

$dumper = new Dumper(new ThemeText(), maxDepth: 5);

$text = $dumper->dump($value);

// Hide the "file:line" header
$dumper->setTraceIndex(Dumper::TRACE_DISABLED);
```

### Custom theme

Implement `G\Dump\Theme\ThemeInterface`:

| Method | Role |
|---|---|
| `header(): string` / `footer(): string` | Opening and closing of a dump |
| `output(string $type, ?string $complement = null, ?string $value = null): string` | One element: type, complement (length, class name…) and value |
| `indent(int $depth): string` | Prefix of a nested line |
| `escape(string $text): string` | Makes a raw value safe to print (HTML escaping, or nothing for plain text) |

`ThemeHTML` exposes its colours as protected constants (`STYLE_TYPE`, `STYLE_COMPLEMENT`, `STYLE_VALUE`): extend it to restyle.

### Custom type

A type implements `G\Dump\Type\TypeInterface` (`supports()`, `dump()`, `getPriority()`; the highest priority is asked first) and usually extends `AbstractType` to reach the dumper. Passing a list of types to the `Dumper` constructor replaces the built-in ones.

## Demo page

`examples/index.php` dumps a sample of every supported value; open it through a web server to see the HTML theme.

## Development

```bash
composer install
composer test-all
```

## License

MIT
