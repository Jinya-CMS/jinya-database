<?php

namespace Jinya\Database;

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

trait EntityTrait
{
    /**
     * @return PDO
     */
    public static function getPDO(): PDO
    {
        return new PDO(
            /** @phpstan-ignore-next-line */
            KeyCache::get('___Config', 'ConnectionString'),
            /** @phpstan-ignore-next-line */
            options: KeyCache::get('___Config', 'ConnectionOptions')
        );
    }

    private static function getTableName(): string
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

        /** @var string $tableName */
        $tableName = FileCache::entry(
            __FILE__,
            __NAMESPACE__,
            __CLASS__,
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

        if (!is_string($tableName)) {
            return $getTableName(__CLASS__);
        }

        return $tableName;
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
                        $converterArgs = implode(', ', $converterAttributes[0]->getArguments());
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

        $columns = FileCache::entry(
            __FILE__,
            __NAMESPACE__,
            __CLASS__,
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
        if (is_array($columns)) {
            return $columns;
        }

        $columns = $getColumns(__CLASS__);
        $result = [
            'byProperty' => [],
            'bySqlName' => [],
        ];
        foreach ($columns as $column) {
            $col = $column['column'];
            $result['byProperty'][$col->propertyName] = $col;
            $result['bySqlName'][$col->sqlName] = $col;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public static function jsonDeserialize(array $data): mixed
    {
        $self = new self();
        foreach ($data as $key => $value) {
            if (property_exists($self, $key)) {
                $self->$key = $value;
            }
        }

        return $self;
    }

    /**
     * @inheritdoc
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $result = [];
        foreach (self::getColumns()['byProperty'] as $column) {
            /** @var CacheColumn $column */
            $result[$column->propertyName] = $this->{$column->propertyName};
        }

        return $result;
    }

    /**
     * Executes the sql query and returns the PDOStatement
     *
     * @param string $query
     * @param array<string, mixed> $values
     * @return array<string, mixed>[]
     */
    public static function executeQuery(string $query, array $values = []): array
    {
        $pdo = self::getPDO();
        $statement = $pdo->prepare($query);
        if (!$statement) {
            throw new PDOException('Failed to execute query');
        }

        $statement->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $result = $statement->execute($values);
        if (!$result) {
            $ex = new PDOException('Failed to execute query');
            $ex->errorInfo = $statement->errorInfo();
            throw $ex;
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!$result) {
            $ex = new PDOException('Failed to execute query');
            $ex->errorInfo = $statement->errorInfo();
            throw $ex;
        }

        return $result;
    }
}
