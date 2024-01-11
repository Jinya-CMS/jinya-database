<?php

namespace Jinya\Database;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryFactory;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use Jinya\Database\Attributes\Column;
use Jinya\Database\Attributes\Id;
use Jinya\Database\Attributes\Table;
use Jinya\Database\Cache\CacheColumn;
use Jinya\Database\Cache\FileCache;
use Jinya\Database\Cache\KeyCache;
use Jinya\Database\Converters\DateConverter;
use Jinya\Database\Converters\NullableDateConverter;
use Jinya\Database\Exception\NotNullViolationException;
use PDO;
use PDOException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use Throwable;

trait EntityTrait
{
    private static function getColumnByProperty(string $name): CacheColumn|null
    {
        return self::getColumns()['byProperty'][$name] ?? null;
    }

    private static function getColumnBySqlName(string $name): CacheColumn|null
    {
        return self::getColumns()['bySqlName'][$name] ?? null;
    }

    /**
     * @return string[]
     */
    private static function getSqlColumnNames(): array
    {
        $columns = self::getColumns();

        return array_keys($columns['bySqlName']);
    }

    private function getWhereToIdentifyEntity(): array
    {
        $idColumn = self::getIdProperty();
        if ($idColumn['name']) {
            return [$idColumn['sqlName'] . ' = ?', [$this->{$idColumn['name']}]];
        }

        $columns = self::getColumns()['bySqlName'];
        $uniqueColumns = [];
        foreach ($columns as $name => $column) {
            if ($column->unique) {
                $uniqueColumns[$name . ' = ?'] = $column->converter ? $column->converter->to($this->{$column->propertyName}) : $this->{$column->propertyName};
            }
        }

        if (!empty($uniqueColumns)) {
            return [implode(' and ', array_keys($uniqueColumns)), array_values($uniqueColumns)];
        }

        $result = [];
        foreach ($columns as $name => $column) {
            if ($column->notNull) {
                $result[$name . ' = ?'] = $column->converter ? $column->converter->to($this->{$column->propertyName}) : $this->{$column->propertyName};
            }
        }

        return [implode(' and ', array_keys($result)), array_values($result)];
    }

    /**
     * @return array{'sqlName': string|null, 'name': string|null}
     */
    #[ArrayShape([
        'sqlName' => 'string|null',
        'name' => 'string|null',
    ])]
    private static function getIdProperty(): array
    {
        self::getColumns();
        /** @var string $filename */
        $filename = (new ReflectionClass(static::class))->getFileName();
        $cache = FileCache::entry(
            $filename,
            __NAMESPACE__,
            static::class,
            'Id',
            static function (
                string $filename,
                string $namespace,
                string $class,
                string $key,
                string $cacheClass
            ): string {
                return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = [
'id' => [
'sqlName' => null,
'name' => null,
]
];
PHP;
            }
        );

        return $cache['id'];
    }

    /**
     * @return array<string, array<string, CacheColumn>>
     */
    #[ArrayShape([
        'byProperty' => 'array',
        'bySqlName' => 'array',
    ])]
    private static function getColumns(): array
    {
        $autoConverterEnabled = KeyCache::get('___Config', 'EnableAutoConvert');

        /** @var string $filename */
        $filename = (new ReflectionClass(static::class))->getFileName();
        $getColumns = static function (string $class) use ($filename): array {
            /** @phpstan-ignore-next-line */
            $reflectionClass = new ReflectionClass($class);
            $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
            $result = [];
            $hasId = false;

            foreach ($properties as $property) {
                $cols = $property->getAttributes(Column::class);
                if (!empty($cols)) {
                    /** @var Column $column */
                    $column = $cols[0]->newInstance();
                    $name = $property->getName();
                    $sqlName = empty($column->sqlName) ? $name : $column->sqlName;
                    $defaultValue = $column->defaultValue;
                    $nullable = $property->getType()->allowsNull();

                    $converterAttributes = $property->getAttributes(
                        ValueConverter::class,
                        ReflectionAttribute::IS_INSTANCEOF
                    );
                    $converter = null;
                    $converterString = 'null';
                    if (!empty($converterAttributes)) {
                        $converter = $converterAttributes[0]->newInstance();
                        $converterArgs = implode(
                            ', ',
                            array_map(
                                static fn (mixed $arg) => var_export($arg, true),
                                $converterAttributes[0]->getArguments()
                            )
                        );
                        $converterClass = $converterAttributes[0]->getName();
                        $converterString = "new $converterClass($converterArgs)";
                    } else {
                        $type = $property->getType();
                        if ($type instanceof ReflectionNamedType) {
                            $types = [$type->getName()];
                        } else {
                            $types = array_map(static fn (ReflectionType $type) => $type->getName(), $type->getTypes());
                        }
                        if (in_array(DateTime::class, $types, false)) {
                            $dateFormat = 'Y-m-d H:i:s';
                            if ($property->getType()->allowsNull()) {
                                $converter = new NullableDateConverter($dateFormat);
                                $converterString = 'new ' . NullableDateConverter::class . "('$dateFormat')";
                            } else {
                                $converter = new DateConverter($dateFormat);
                                $converterString = 'new ' . DateConverter::class . "('$dateFormat')";
                            }
                        }
                    }

                    $autogenerated = $column->autogenerated ? 'true' : 'false';
                    $notNull = !$nullable ? 'true' : 'false';
                    $unique = $column->unique ? 'true' : 'false';
                    $defaultValueExported = var_export($defaultValue, true);
                    $result[] = [
                        'column' => new CacheColumn($name, $sqlName, $converter, $column->autogenerated, !$nullable, $column->unique, $defaultValue),
                        'string' => "new " . CacheColumn::class . "('$name', '$sqlName', $converterString, $autogenerated, $notNull, $unique, $defaultValueExported)",
                    ];

                    if (!$hasId) {
                        $ids = $property->getAttributes(Id::class);
                        if (!empty($ids)) {
                            $hasId = true;
                            FileCache::entry(
                                $filename,
                                __NAMESPACE__,
                                static::class,
                                'Id',
                                static function (
                                    string $filename,
                                    string $namespace,
                                    string $class,
                                    string $key,
                                    string $cacheClass
                                ) use ($ids, $sqlName, $name): string {
                                    return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = [
'id' => [
'sqlName' => '$sqlName',
'name' => '$name',
],
];
PHP;
                                }
                            );
                        }
                    }
                }
            }

            return $result;
        };

        /** @var array<string, array<string, CacheColumn>> $columns */
        $columns = FileCache::entry(
            $filename,
            __NAMESPACE__,
            static::class,
            'Columns',
            static function (
                string $filename,
                string $namespace,
                string $class,
                string $key,
                string $cacheClass
            ) use ($getColumns): string {
                $columns = $getColumns($class);

                $byProperty = '';
                $bySqlName = '';

                foreach ($columns as $column) {
                    $col = $column['string'];
                    $name = $column['column']->propertyName;
                    $sqlName = $column['column']->sqlName;

                    $byProperty .= "'$name' => $col,\n";
                    $bySqlName .= "'$sqlName' => $col,\n";
                }

                return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = [
'byProperty' => [
$byProperty
],
'bySqlName' => [
$bySqlName
],
];
PHP;
            }
        );

        return $columns;
    }

    /**
     * Maps the current class to an array
     *
     * @return array<string, mixed>
     */
    private function toSqlArray(): array
    {
        $columns = self::getColumns();
        $result = [];

        foreach ($columns['byProperty'] as $property => $column) {
            /** @var CacheColumn $column */
            if (!$column->autogenerated) {
                if (!$column->notNull) {
                    $result[$column->sqlName] = $column->converter ? $column->converter->to($column->defaultValue) : $column->defaultValue;
                } else {
                    $result[$column->sqlName] = $column->converter ? $column->converter->to($this->{$property}) : $this->{$property};
                }
            }
        }

        return $result;
    }

    /**
     * @throws NotNullViolationException
     */
    private function checkRequiredColumns(): void
    {
        $columns = self::getColumns();
        $missingColumns = [];
        foreach ($columns['byProperty'] as $property => $column) {
            if (!$column->autogenerated && $column->notNull && !isset($this->{$property})) {
                $missingColumns[] = $property;
            }
        }

        if (!empty($missingColumns)) {
            throw new NotNullViolationException($missingColumns);
        }
    }

    /**
     * Gets the name of the table
     *
     * @return string
     */
    public static function getTableName(): string
    {
        $getTableName = static function (string $class): string {
            /** @phpstan-ignore-next-line */
            $reflectionClass = new ReflectionClass($class);
            $attributes = $reflectionClass->getAttributes(Table::class);
            if (!empty($attributes)) {
                $attribute = $attributes[0];

                return $attribute->newInstance()->name;
            }

            return $reflectionClass->getShortName();
        };

        /** @var string $filename */
        $filename = (new ReflectionClass(static::class))->getFileName();

        /** @var string $tableName */
        $tableName = FileCache::entry(
            $filename,
            __NAMESPACE__,
            static::class,
            'Table',
            static function (
                string $filename,
                string $namespace,
                string $class,
                string $key,
                string $cacheClass
            ) use ($getTableName): string {
                $table = $getTableName($class);

                return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = '$table';
PHP;
            }
        );

        return $tableName;
    }

    /**
     * Executes the sql query and returns the PDOStatement
     *
     * @param DeleteInterface|InsertInterface|SelectInterface|UpdateInterface $query
     * @return array<string, mixed>[]|string|bool
     */
    public static function executeQuery(
        DeleteInterface|InsertInterface|SelectInterface|UpdateInterface $query
    ): array|string|bool {
        $pdo = self::getPDO();
        $statement = $pdo->prepare($query->getStatement());
        if ($statement === false) {
            throw new PDOException('Failed to execute query');
        }

        try {
            $statement->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable) {
        }

        $result = $statement->execute($query->getBindValues());
        if ($result === false) {
            $ex = new PDOException('Failed to execute query');
            $ex->errorInfo = $statement->errorInfo();
            throw $ex;
        }

        if ($query instanceof SelectInterface) {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($query instanceof InsertInterface) {
            return $pdo->lastInsertId();
        }

        return true;
    }

    /**
     * Maps the given array to this class
     *
     * @param array<string, mixed> $item
     * @return self
     */
    public static function fromArray(array $item): mixed
    {
        $entity = new self();
        foreach ($item as $key => $value) {
            $column = self::getColumnBySqlName($key);
            if ($column && $column->converter) {
                $entity->{$column->propertyName} = $column->converter->from($value);
            } elseif ($column) {
                $entity->{$column->propertyName} = $value;
            }
        }

        return $entity;
    }

    /**
     * @return PDO
     */
    public static function getPDO(): PDO
    {
        /** @var PDO $pdo */
        $pdo = KeyCache::entry('___Database', 'PDO', static function (string $key) {
            $options = KeyCache::get('___Config', 'ConnectionOptions');
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

            return new PDO(
                /** @phpstan-ignore-next-line */
                KeyCache::get('___Config', 'ConnectionString'),
                /** @phpstan-ignore-next-line */
                options: $options
            );
        });

        return $pdo;
    }

    /**
     * @return QueryFactory
     */
    public static function getQueryBuilder(): QueryFactory
    {
        /** @var string $driver */
        $driver = self::getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);

        return new QueryFactory($driver);
    }
}
