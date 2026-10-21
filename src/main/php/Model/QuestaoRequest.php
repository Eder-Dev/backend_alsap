<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Model;

use Br\Udesc\Alsap\Utils\InputData;

final class QuestaoRequest
{
    public function __construct(public readonly ?string $descricao, public readonly ?string $tipo)
    {
    }

    public static function fromArray(array $input): self
    {
        $strings = InputData::strings($input, ['descricao', 'tipo']);
        return new self($strings['descricao'], $strings['tipo']);
    }
}