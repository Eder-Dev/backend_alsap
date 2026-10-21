<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Service;

use Br\Udesc\Alsap\Config\HttpResponse;
use Br\Udesc\Alsap\Entity\JsonSerializer;
use Br\Udesc\Alsap\Mapper\AudioResquestToEntity;
use Br\Udesc\Alsap\Model\AudioRequest as AudioRequestDto;
use Br\Udesc\Alsap\Repository\AudioRepository;
use Br\Udesc\Alsap\Repository\NarrativaRepository;
use Br\Udesc\Alsap\Repository\RespostaRepository;

final class AudioService
{
    public function __construct(
        private readonly AudioRepository $repository,
        private readonly RespostaRepository $respostas,
        private readonly NarrativaRepository $narrativas,
        private readonly JsonSerializer $serializer,
    ) {
    }

    public function all(): HttpResponse
    {
        return new HttpResponse($this->serializer->list('audio', $this->repository->findAll()));
    }

    public function one(int $id): HttpResponse
    {
        return new HttpResponse($this->serializer->audio($this->repository->findById($id)));
    }

    public function create(array $input): HttpResponse
    {
        $request = AudioRequestDto::fromArray($input);
        $values = (new AudioResquestToEntity())->map($request);
        $respostaId = $request->resposta_id;
        $narrativaId = $request->narrativa_id;
        if ($respostaId !== null) {
            $this->respostas->findById($respostaId);
            $values['resposta'] = $respostaId;
        }
        if ($narrativaId !== null) {
            $this->narrativas->findById($narrativaId);
            $values['narrativa'] = $narrativaId;
        }

        return new HttpResponse($this->repository->create($values), 200);
    }

    public function update(int $id, array $input): HttpResponse
    {
        $this->repository->findById($id);
        $request = AudioRequestDto::fromArray($input);
        $values = (new AudioResquestToEntity())->mapUpdate($request);
        $respostaId = $request->resposta_id;
        $narrativaId = $request->narrativa_id;
        if ($respostaId !== null) {
            $this->respostas->findById($respostaId);
            $values['resposta'] = $respostaId;
        } elseif ($narrativaId !== null) {
            $this->narrativas->findById($narrativaId);
            $values['narrativa'] = $narrativaId;
        }
        $this->repository->update($id, $values);
        return $this->one($id);
    }

    public function delete(int $id): HttpResponse
    {
        $this->repository->delete($id);
        return new HttpResponse(null, 204);
    }

}