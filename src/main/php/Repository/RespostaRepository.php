<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Repository;

final class RespostaRepository extends AbstractRepository
{
    protected string $table = 'resposta';
    protected array $columns = ['endereco_id', 'questao_id'];
}