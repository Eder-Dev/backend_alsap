<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Mapper;

use Br\Udesc\Alsap\Model\QuestaoRequest;

final class QuestaoRequestToEntity
{
    public function map(QuestaoRequest $request): array
    {
        return ['descricao' => $request->descricao, 'tipo' => $request->tipo];
    }

    public function mapUpdate(QuestaoRequest $request): array
    {
        return $this->map($request);
    }
}