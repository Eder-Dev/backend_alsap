<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Model;

use Br\Udesc\Alsap\Utils\InputData;

final class NarrativaRequest
{
    public function __construct(
        public readonly ?string $nome,
        public readonly ?string $idade,
        public readonly ?string $sexo,
        public readonly ?string $escolaridade,
        public readonly ?string $local,
        public readonly ?string $transcricao,
        public readonly ?string $resumo,
        public readonly ?int $audio_id,
    ) {
    }

    public static function fromArray(array $input): self
    {
        $strings = InputData::strings($input, ['nome', 'idade', 'sexo', 'escolaridade', 'local', 'transcricao', 'resumo']);
        return new self(
            $strings['nome'], $strings['idade'], $strings['sexo'], $strings['escolaridade'],
            $strings['local'], $strings['transcricao'], $strings['resumo'], InputData::optionalId($input, 'audio_id'),
        );
    }
}