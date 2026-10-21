<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Service;

use Br\Udesc\Alsap\Config\HttpResponse;
use Br\Udesc\Alsap\Entity\JsonSerializer;
use Br\Udesc\Alsap\Mapper\EnderecoRequestToEntity;
use Br\Udesc\Alsap\Model\EnderecoRequest as EnderecoRequestDto;
use Br\Udesc\Alsap\Repository\EnderecoRepository;

final class EnderecoService
{
    public function __construct(private readonly EnderecoRepository $repository, private readonly JsonSerializer $serializer)
    {
    }

    public function all(): HttpResponse
    {
        return new HttpResponse($this->serializer->list('endereco', $this->repository->findAll()));
    }

    public function one(int $id): HttpResponse
    {
        return new HttpResponse($this->serializer->endereco($this->repository->findById($id)));
    }

    public function create(array $input): HttpResponse
    {
        $request = EnderecoRequestDto::fromArray($input);
        return new HttpResponse($this->repository->create((new EnderecoRequestToEntity())->map($request)), 200);
    }

    public function update(int $id, array $input): HttpResponse
    {
        $request = EnderecoRequestDto::fromArray($input);
        $this->repository->update($id, (new EnderecoRequestToEntity())->mapUpdate($request));
        return $this->one($id);
    }

    public function delete(int $id): HttpResponse
    {
        $this->repository->delete($id);
        return new HttpResponse(null, 204);
    }

}