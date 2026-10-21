<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Repository;

final class NarrativaRepository extends AbstractRepository
{
    protected string $table = 'narrativa';
    protected array $columns = ['nome', 'idade', 'sexo', 'escolaridade', 'local', 'transcricao', 'titulo'];

    public function countByCidade(string $cidade): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM narrativa WHERE LOWER(local) LIKE LOWER(:cidade)');
        $statement->execute(['cidade' => $cidade . '%']);
        return (int) $statement->fetchColumn();
    }
}