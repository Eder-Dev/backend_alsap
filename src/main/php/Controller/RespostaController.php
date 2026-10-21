<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Controller;

use Br\Udesc\Alsap\Config\HttpResponse;
use Br\Udesc\Alsap\Service\RespostaService;

final class RespostaController
{
    public function __construct(private readonly RespostaService $service)
    {
    }

    public function all(): HttpResponse { return $this->service->all(); }
    public function one(int $id): HttpResponse { return $this->service->one($id); }
    public function create(array $input): HttpResponse { return $this->service->create($input); }
    public function update(int $id, array $input): HttpResponse { return $this->service->update($id, $input); }
    public function delete(int $id): HttpResponse { return $this->service->delete($id); }
}