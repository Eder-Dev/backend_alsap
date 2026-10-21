<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Model;

use Br\Udesc\Alsap\Utils\InputData;
use InvalidArgumentException;

final class RespostaRequest
{
    public function __construct(public readonly int $endereco_id, public readonly int $questao_id)
    {
    }

    public static function fromArray(array $input): self
    {
        $addressId = InputData::optionalId($input, 'endereco_id');
        $questionId = InputData::optionalId($input, 'questao_id');
        if ($addressId === null || $questionId === null) {
            throw new InvalidArgumentException('Os campos endereco_id e questao_id são obrigatórios.');
        }
        return new self($addressId, $questionId);
    }
}