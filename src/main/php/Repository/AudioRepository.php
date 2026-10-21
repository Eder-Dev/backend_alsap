<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Repository;

final class AudioRepository extends AbstractRepository
{
    protected string $table = 'audio';
    protected array $columns = ['nome', 'transcricao', 'audio_url', 'tipo', 'resposta', 'narrativa'];

    public function associateNarrativa(int $audioId, int $narrativaId): void
    {
        $statement = $this->pdo->prepare('UPDATE audio SET narrativa = :narrativa WHERE id = :id');
        $statement->execute(['narrativa' => $narrativaId, 'id' => $audioId]);
    }
}