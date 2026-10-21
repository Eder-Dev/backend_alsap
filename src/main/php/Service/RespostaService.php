<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Service;

use Br\Udesc\Alsap\Config\HttpResponse;
use Br\Udesc\Alsap\Entity\JsonSerializer;
use Br\Udesc\Alsap\Model\RespostaRequest as RespostaRequestDto;
use Br\Udesc\Alsap\Repository\EnderecoRepository;
use Br\Udesc\Alsap\Repository\QuestaoRepository;
use Br\Udesc\Alsap\Repository\RespostaRepository;

final class RespostaService
{
    public function __construct(
        private readonly RespostaRepository $repository,
        private readonly EnderecoRepository $enderecos,
        private readonly QuestaoRepository $questoes,
        private readonly JsonSerializer $serializer,
    ) {
    }

    public function all(): HttpResponse
    {
        return new HttpResponse($this->serializer->list('resposta', $this->repository->findAll()));
    }

    public function one(int $id): HttpResponse
    {
        return new HttpResponse($this->serializer->resposta($this->repository->findById($id)));
    }

    public function create(array $input): HttpResponse
    {
        $request = RespostaRequestDto::fromArray($input);
        $addressId = $request->endereco_id;
        $questionId = $request->questao_id;
        $this->enderecos->findById($addressId);
        $this->questoes->findById($questionId);

        $id = $this->repository->create([
            'endereco_id' => $addressId,
            'questao_id' => $questionId,
        ]);
        return new HttpResponse($id, 201);
    }

    public function update(int $id, array $input): HttpResponse
    {
        $this->repository->findById($id);
        $request = RespostaRequestDto::fromArray($input);
        $addressId = $request->endereco_id;
        $questionId = $request->questao_id;
        $this->enderecos->findById($addressId);
        $this->questoes->findById($questionId);

        $this->repository->update($id, [
            'endereco_id' => $addressId,
            'questao_id' => $questionId,
        ]);
        return $this->one($id);
    }

    public function delete(int $id): HttpResponse
    {
        $this->repository->delete($id);
        return new HttpResponse(null, 204);
    }
}