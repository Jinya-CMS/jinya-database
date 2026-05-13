# Advanced Usage

## Custom Value Converters

Jinya Database allows you to customize how data is converted between PHP and the database. This is useful for types that aren't natively supported or when you need a specific format.

### Creating a Converter

To create a custom converter, implement the `Jinya\Database\ValueConverter` interface and add the `#[Attribute]` attribute to the class.

```php
namespace App\Converters;

use Attribute;
use Jinya\Database\ValueConverter;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonConverter implements ValueConverter
{
    public function from(mixed $input): array
    {
        return json_decode($input, true) ?: [];
    }

    public function to(mixed $input): string
    {
        return json_encode($input);
    }
}
```

### Using a Converter

Apply the custom converter attribute to an entity property.

```php
#[Column]
#[JsonConverter]
public array $metadata = [];
```

## Auto Conversion

By default, Jinya Database automatically converts `DateTime` properties if they are typed correctly. This behavior can be toggled in `configure_jinya_database`.

```php
configure_jinya_database(
    // ...
    enableAutoConvert: true // Default is true
);
```

When enabled, `DateTime` properties will use the `DateConverter` with the format `Y-m-d H:i:s`.

## Exception Handling

Jinya Database provides specific exceptions for common database errors:

- `Jinya\Database\Exception\NotNullViolationException`: Thrown when a required column (not null) is missing a value during create/update.
- `Jinya\Database\Exception\UniqueFailedException`: Thrown when a unique constraint is violated.
- `Jinya\Database\Exception\ForeignKeyFailedException`: Thrown when a foreign key constraint fails.

All these exceptions extend `PDOException` or provide access to the underlying `PDOException`.

```php
try {
    $artist->create();
} catch (UniqueFailedException $e) {
    echo "Artist with this name already exists.";
} catch (NotNullViolationException $e) {
    echo "Missing required fields: " . implode(', ', $e->columns);
}
```
