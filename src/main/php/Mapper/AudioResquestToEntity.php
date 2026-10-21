<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Mapper;

use Br\Udesc\Alsap\Model\AudioRequest;

final class AudioResquestToEntity
{
    public function map(AudioRequest $request): array
    {
        return [
            'nome' => $request->nome,
            'transcricao' => $request->transcricao,
            'audio_url' => $request->audio_url,
            'tipo' => $request->tipo,
        ];
    }

    public function mapUpdate(AudioRequest $request): array
    {
        return $this->map($request);
    }
}