<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Service;

use Br\Udesc\Alsap\Config\HttpResponse;
use Br\Udesc\Alsap\Entity\JsonSerializer;
use Br\Udesc\Alsap\Mapper\NarrativaRequestToEntity;
use Br\Udesc\Alsap\Model\NarrativaRequest as NarrativaRequestDto;
use Br\Udesc\Alsap\Repository\AudioRepository;
use Br\Udesc\Alsap\Repository\NarrativaRepository;

final class NarrativaService
{
    public function __construct(
        private readonly NarrativaRepository $repository,
        private readonly AudioRepository $audios,
        private readonly JsonSerializer $serializer,
    ) {
    }

    public function all(): HttpResponse
    {
        return new HttpResponse($this->serializer->list('narrativa', $this->repository->findAll()));
    }

    public function one(int $id): HttpResponse
    {
        return new HttpResponse($this->serializer->narrativa($this->repository->findById($id)));
    }

    public function create(array $input): HttpResponse
    {
        $request = NarrativaRequestDto::fromArray($input);
        $values = (new NarrativaRequestToEntity())->map($request);
        $location = (string) ($request->local ?? '');
        $city = trim(explode('-', $location, 2)[0]);
        $title = sprintf('Narrativa - %s - %02d', mb_strtoupper($city, 'UTF-8'), $this->repository->countByCidade($city) + 1);
        $values['titulo'] = $title;
        $audioId = $request->audio_id;
        if ($audioId !== null) {
            $this->audios->findById($audioId);
            $values['audio_id'] = $audioId;
        }

        $id = $this->repository->create(array_diff_key($values, ['audio_id' => true]));
        if (isset($values['audio_id'])) {
            $this->audios->associateNarrativa($values['audio_id'], $id);
        }
        return new HttpResponse($id, 201);
    }

    public function update(int $id, array $input): HttpResponse
    {
        $this->repository->findById($id);
        $request = NarrativaRequestDto::fromArray($input);
        $values = (new NarrativaRequestToEntity())->mapUpdate($request);
        $audioId = $request->audio_id;
        if ($audioId !== null) {
            $this->audios->findById($audioId);
            $this->audios->associateNarrativa($audioId, $id);
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