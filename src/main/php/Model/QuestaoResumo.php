<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Model;

final class QuestaoResumo
{
    public function __construct(public readonly int $id, public readonly ?string $descricao)
    {
    }

    public function toArray(): array
    {
        return ['id' => $this->id, 'descricao' => $this->descricao];
    }
}