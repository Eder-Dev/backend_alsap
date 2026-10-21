<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Routes;

use Br\Udesc\Alsap\Config\Database;
use Br\Udesc\Alsap\Controller\AudioController;
use Br\Udesc\Alsap\Controller\EnderecoController;
use Br\Udesc\Alsap\Controller\NarrativaController;
use Br\Udesc\Alsap\Controller\QuestaoController;
use Br\Udesc\Alsap\Controller\RespostaController;
use Br\Udesc\Alsap\Entity\JsonSerializer;
use Br\Udesc\Alsap\Repository\AudioRepository;
use Br\Udesc\Alsap\Repository\EnderecoRepository;
use Br\Udesc\Alsap\Repository\NarrativaRepository;
use Br\Udesc\Alsap\Repository\QuestaoRepository;
use Br\Udesc\Alsap\Repository\RespostaRepository;
use Br\Udesc\Alsap\Service\AudioService;
use Br\Udesc\Alsap\Service\EnderecoService;
use Br\Udesc\Alsap\Service\NarrativaService;
use Br\Udesc\Alsap\Service\QuestaoService;
use Br\Udesc\Alsap\Service\RespostaService;

final class ApiRoutes
{
    public static function build(): array
    {
        $pdo = Database::connection();
        $serializer = new JsonSerializer($pdo);
        $audioRepository = new AudioRepository($pdo);
        $enderecoRepository = new EnderecoRepository($pdo);
        $narrativaRepository = new NarrativaRepository($pdo);
        $questaoRepository = new QuestaoRepository($pdo);
        $respostaRepository = new RespostaRepository($pdo);

        $audio = new AudioController(new AudioService($audioRepository, $respostaRepository, $narrativaRepository, $serializer));
        $endereco = new EnderecoController(new EnderecoService($enderecoRepository, $serializer));
        $narrativa = new NarrativaController(new NarrativaService($narrativaRepository, $audioRepository, $serializer));
        $questao = new QuestaoController(new QuestaoService($questaoRepository, $serializer));
        $resposta = new RespostaController(new RespostaService($respostaRepository, $enderecoRepository, $questaoRepository, $serializer));
//teste
        return [
            ['GET', '~^/Audio/all$~', static fn(array $match, array $body) => $audio->all()],
            ['GET', '~^/Audio/([0-9]+)$~', static fn(array $match, array $body) => $audio->one((int) $match[1])],
            ['POST', '~^/Audio/create$~', static fn(array $match, array $body) => $audio->create($body)],
            ['PUT', '~^/Audio/update/([0-9]+)$~', static fn(array $match, array $body) => $audio->update((int) $match[1], $body)],
            ['DELETE', '~^/Audio/delete/([0-9]+)$~', static fn(array $match, array $body) => $audio->delete((int) $match[1])],
            ['GET', '~^/Endereco/all$~', static fn(array $match, array $body) => $endereco->all()],
            ['GET', '~^/Endereco/([0-9]+)$~', static fn(array $match, array $body) => $endereco->one((int) $match[1])],
            ['POST', '~^/Endereco/create$~', static fn(array $match, array $body) => $endereco->create($body)],
            ['PUT', '~^/Endereco/update/([0-9]+)$~', static fn(array $match, array $body) => $endereco->update((int) $match[1], $body)],
            ['DELETE', '~^/Endereco/delete/([0-9]+)$~', static fn(array $match, array $body) => $endereco->delete((int) $match[1])],
            ['GET', '~^/Narrativa/all$~', static fn(array $match, array $body) => $narrativa->all()],
            ['GET', '~^/Narrativa/([0-9]+)$~', static fn(array $match, array $body) => $narrativa->one((int) $match[1])],
            ['POST', '~^/Narrativa/create$~', static fn(array $match, array $body) => $narrativa->create($body)],
            ['PUT', '~^/Narrativa/update/([0-9]+)$~', static fn(array $match, array $body) => $narrativa->update((int) $match[1], $body)],
            ['DELETE', '~^/Narrativa/delete/([0-9]+)$~', static fn(array $match, array $body) => $narrativa->delete((int) $match[1])],
            ['GET', '~^/Questao/all$~', static fn(array $match, array $body) => $questao->all()],
            ['GET', '~^/Questao/tipo/qff$~', static fn(array $match, array $body) => $questao->qff()],
            ['GET', '~^/Questao/tipo/qsl$~', static fn(array $match, array $body) => $questao->qsl()],
            ['GET', '~^/Questao/([0-9]+)$~', static fn(array $match, array $body) => $questao->one((int) $match[1])],
            ['POST', '~^/Questao/create$~', static fn(array $match, array $body) => $questao->create($body)],
            ['PUT', '~^/Questao/update/([0-9]+)$~', static fn(array $match, array $body) => $questao->update((int) $match[1], $body)],
            ['DELETE', '~^/Questao/delete/([0-9]+)$~', static fn(array $match, array $body) => $questao->delete((int) $match[1])],
            ['GET', '~^/Respostas/all$~', static fn(array $match, array $body) => $resposta->all()],
            ['GET', '~^/Respostas/([0-9]+)$~', static fn(array $match, array $body) => $resposta->one((int) $match[1])],
            ['POST', '~^/Respostas/create$~', static fn(array $match, array $body) => $resposta->create($body)],
            ['PUT', '~^/Respostas/update/([0-9]+)$~', static fn(array $match, array $body) => $resposta->update((int) $match[1], $body)],
            ['DELETE', '~^/Respostas/delete/([0-9]+)$~', static fn(array $match, array $body) => $resposta->delete((int) $match[1])],
        ];
    }
}