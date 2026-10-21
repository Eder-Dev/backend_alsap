<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Service;

use Br\Udesc\Alsap\Config\HttpResponse;
use Br\Udesc\Alsap\Entity\JsonSerializer;
use Br\Udesc\Alsap\Mapper\QuestaoRequestToEntity;
use Br\Udesc\Alsap\Model\QuestaoRequest as QuestaoRequestDto;
use Br\Udesc\Alsap\Model\QuestaoResumo;
use Br\Udesc\Alsap\Repository\QuestaoRepository;

final class QuestaoService
{
    public function __construct(private readonly QuestaoRepository $repository, private readonly JsonSerializer $serializer)
    {
    }

    public function all(): HttpResponse
    {
        return new HttpResponse($this->serializer->list('questao', $this->repository->findAll()));
    }

    public function one(int $id): HttpResponse
    {
        return new HttpResponse($this->serializer->questao($this->repository->findById($id)));
    }

    public function summaries(string $tipo): HttpResponse
    {
        $rows = $this->repository->findByTipo($tipo);
        return new HttpResponse(array_map(static fn(array $row): array => (new QuestaoResumo((int) $row['id'], $row['descricao']))->toArray(), $rows));
    }

    public function create(array $input): HttpResponse
    {
        $request = QuestaoRequestDto::fromArray($input);
        return new HttpResponse($this->repository->create((new QuestaoRequestToEntity())->map($request)), 200);
    }

    public function update(int $id, array $input): HttpResponse
    {
        $request = QuestaoRequestDto::fromArray($input);
        $this->repository->update($id, (new QuestaoRequestToEntity())->mapUpdate($request));
        return $this->one($id);
    }

    public function delete(int $id): HttpResponse
    {
        $this->repository->delete($id);
        return new HttpResponse(null, 204);
    }
}