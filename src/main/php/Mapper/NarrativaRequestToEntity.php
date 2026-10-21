<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Mapper;

use Br\Udesc\Alsap\Model\NarrativaRequest;

final class NarrativaRequestToEntity
{
    public function map(NarrativaRequest $request): array
    {
        return [
            'nome' => $request->nome,
            'idade' => $request->idade,
            'sexo' => $request->sexo,
            'escolaridade' => $request->escolaridade,
            'local' => $request->local,
            'transcricao' => $request->transcricao,
        ];
    }

    public function mapUpdate(NarrativaRequest $request): array
    {
        return $this->map($request);
    }
}