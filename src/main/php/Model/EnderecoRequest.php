<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Model;

use Br\Udesc\Alsap\Utils\InputData;

final class EnderecoRequest
{
    public function __construct(
        public readonly ?string $nome,
        public readonly ?string $cep,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
    ) {
    }

    public static function fromArray(array $input): self
    {
        $strings = InputData::strings($input, ['nome', 'cep']);
        return new self($strings['nome'], $strings['cep'], InputData::number($input, 'latitude'), InputData::number($input, 'longitude'));
    }
}