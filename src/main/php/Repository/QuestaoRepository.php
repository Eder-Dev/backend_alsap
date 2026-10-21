<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Repository;

final class QuestaoRepository extends AbstractRepository
{
    protected string $table = 'questao';
    protected array $columns = ['descricao', 'tipo'];

    public function findByTipo(string $tipo): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM questao WHERE tipo = :tipo');
        $statement->execute(['tipo' => $tipo]);
        return $statement->fetchAll();
    }
}