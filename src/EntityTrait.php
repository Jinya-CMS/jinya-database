<?php

namespace Jinya\Database;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryFactory;
use JetBrains\PhpStorm\ArrayShape;
use Jinya\Database\Attributes\Column;
use Jinya\Database\Attributes\Table;
use Jinya\Database\Cache\CacheColumn;
use Jinya\Database\Cache\FileCache;
use Jinya\Database\Cache\KeyCache;
use PDO;
use PDOException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
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

    /**
     * @return array<string, array<string, CacheColumn>>
     */
    #[ArrayShape([
        'byProperty' => 'array',
        'bySqlName' => 'array',
    ])]
    private static function getColumns(): array
    {
        $getColumns = static function (string $class): array {
            /** @phpstan-ignore-next-line */
            $reflectionClass = new ReflectionClass($class);
            $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
            $result = [];

            foreach ($properties as $property) {
                $cols = $property->getAttributes(Column::class);
                if (!empty($cols)) {
                    /** @var Column $column */
                    $column = $cols[0]->newInstance();
                    $name = $property->getName();
                    $sqlName = empty($column->sqlName) ? $name : $column->sqlName;

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
                    }

                    $result[] = [
                        'column' => new CacheColumn($name, $sqlName, $converter),
                        'string' => "new " . CacheColumn::class . "('$name', '$sqlName', $converterString)",
                    ];
                }
            }

            return $result;
        };

        /** @var string $filename */
        $filename = (new ReflectionClass(static::class))->getFileName();
        /** @var array<string, array<string, CacheColumn>> $columns */
        $columns = FileCache::entry(
            $filename . 'Columns',
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
            $filename . 'Table',
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
     * @return array<string, mixed>[]
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
        $pdo = KeyCache::entry('___Database', 'PDO', static fn (string $key) => new PDO(
            /** @phpstan-ignore-next-line */
            KeyCache::get('___Config', 'ConnectionString'),
            /** @phpstan-ignore-next-line */
            options: KeyCache::get('___Config', 'ConnectionOptions')
        ));

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
