<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Repository;

use PDO;
use RuntimeException;

abstract class AbstractRepository
{
    protected string $table;
    protected array $columns;

    public function __construct(protected readonly PDO $pdo)
    {
    }

    public function findAll(): array
    {
        return $this->pdo->query('SELECT * FROM `' . $this->table . '`')->fetchAll();
    }

    public function findById(int $id): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM `' . $this->table . '` WHERE id = :id');
        $statement->execute(['id' => $id]);
        $record = $statement->fetch();
        if ($record === false) {
            throw new RuntimeException('Registro não encontrado.', 404);
        }

        return $record;
    }

    public function create(array $values): int
    {
        $values = $this->filter($values);
        $columns = array_keys($values);
        if ($columns === []) {
            $this->pdo->exec('INSERT INTO `' . $this->table . '` () VALUES ()');
            return (int) $this->pdo->lastInsertId();
        }

        $names = implode(', ', array_map(static fn(string $column): string => '`' . $column . '`', $columns));
        $parameters = implode(', ', array_map(static fn(string $column): string => ':' . $column, $columns));
        $statement = $this->pdo->prepare('INSERT INTO `' . $this->table . '` (' . $names . ') VALUES (' . $parameters . ')');
        $statement->execute($values);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $values): void
    {
        $values = $this->filter($values);
        if ($values === []) {
            $this->findById($id);
            return;
        }

        $assignments = implode(', ', array_map(static fn(string $column): string => '`' . $column . '` = :' . $column, array_keys($values)));
        $statement = $this->pdo->prepare('UPDATE `' . $this->table . '` SET ' . $assignments . ' WHERE id = :record_id');
        $statement->execute($values + ['record_id' => $id]);
        if ($statement->rowCount() === 0) {
            $this->findById($id);
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM `' . $this->table . '` WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() === 0) {
            throw new RuntimeException('Registro não encontrado.', 404);
        }
    }

    private function filter(array $values): array
    {
        return array_intersect_key($values, array_flip($this->columns));
    }
}