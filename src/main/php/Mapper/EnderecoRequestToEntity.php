<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Mapper;

use Br\Udesc\Alsap\Model\EnderecoRequest;

final class EnderecoRequestToEntity
{
    public function map(EnderecoRequest $request): array
    {
        return ['nome' => $request->nome, 'cep' => $request->cep, 'latitude' => $request->latitude, 'longitude' => $request->longitude];
    }

    public function mapUpdate(EnderecoRequest $request): array
    {
        return $this->map($request);
    }
}