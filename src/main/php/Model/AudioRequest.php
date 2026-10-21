<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Model;

use Br\Udesc\Alsap\Utils\InputData;

final class AudioRequest
{
    public function __construct(
        public readonly ?string $nome,
        public readonly ?string $transcricao,
        public readonly ?string $audio_url,
        public readonly int $tipo,
        public readonly ?int $resposta_id,
        public readonly ?int $narrativa_id,
    ) {
    }

    public static function fromArray(array $input): self
    {
        $strings = InputData::strings($input, ['nome', 'transcricao', 'audio_url']);
        return new self(
            $strings['nome'],
            $strings['transcricao'],
            $strings['audio_url'],
            InputData::number($input, 'tipo', true) ?? 0,
            InputData::optionalId($input, 'resposta_id'),
            InputData::optionalId($input, 'narrativa_id'),
        );
    }
}