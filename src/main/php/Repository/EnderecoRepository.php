<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Repository;

final class EnderecoRepository extends AbstractRepository
{
    protected string $table = 'endereco';
    protected array $columns = ['nome', 'cep', 'latitude', 'longitude'];
}