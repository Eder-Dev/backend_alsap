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

        return [
            ['GET', '~^/alsap/Audio/all$~', static fn(array $match, array $body) => $audio->all()],
            ['GET', '~^/alsap/Audio/([0-9]+)$~', static fn(array $match, array $body) => $audio->one((int) $match[1])],
            ['POST', '~^/alsap/Audio/create$~', static fn(array $match, array $body) => $audio->create($body)],
            ['PUT', '~^/alsap/Audio/update/([0-9]+)$~', static fn(array $match, array $body) => $audio->update((int) $match[1], $body)],
            ['DELETE', '~^/alsap/Audio/delete/([0-9]+)$~', static fn(array $match, array $body) => $audio->delete((int) $match[1])],
            ['GET', '~^/alsap/Endereco/all$~', static fn(array $match, array $body) => $endereco->all()],
            ['GET', '~^/alsap/Endereco/([0-9]+)$~', static fn(array $match, array $body) => $endereco->one((int) $match[1])],
            ['POST', '~^/alsap/Endereco/create$~', static fn(array $match, array $body) => $endereco->create($body)],
            ['PUT', '~^/alsap/Endereco/update/([0-9]+)$~', static fn(array $match, array $body) => $endereco->update((int) $match[1], $body)],
            ['DELETE', '~^/alsap/Endereco/delete/([0-9]+)$~', static fn(array $match, array $body) => $endereco->delete((int) $match[1])],
            ['GET', '~^/alsap/Narrativa/all$~', static fn(array $match, array $body) => $narrativa->all()],
            ['GET', '~^/alsap/Narrativa/([0-9]+)$~', static fn(array $match, array $body) => $narrativa->one((int) $match[1])],
            ['POST', '~^/alsap/Narrativa/create$~', static fn(array $match, array $body) => $narrativa->create($body)],
            ['PUT', '~^/alsap/Narrativa/update/([0-9]+)$~', static fn(array $match, array $body) => $narrativa->update((int) $match[1], $body)],
            ['DELETE', '~^/alsap/Narrativa/delete/([0-9]+)$~', static fn(array $match, array $body) => $narrativa->delete((int) $match[1])],
            ['GET', '~^/alsap/Questao/all$~', static fn(array $match, array $body) => $questao->all()],
            ['GET', '~^/alsap/Questao/tipo/qff$~', static fn(array $match, array $body) => $questao->qff()],
            ['GET', '~^/alsap/Questao/tipo/qsl$~', static fn(array $match, array $body) => $questao->qsl()],
            ['GET', '~^/alsap/Questao/([0-9]+)$~', static fn(array $match, array $body) => $questao->one((int) $match[1])],
            ['POST', '~^/alsap/Questao/create$~', static fn(array $match, array $body) => $questao->create($body)],
            ['PUT', '~^/alsap/Questao/update/([0-9]+)$~', static fn(array $match, array $body) => $questao->update((int) $match[1], $body)],
            ['DELETE', '~^/alsap/Questao/delete/([0-9]+)$~', static fn(array $match, array $body) => $questao->delete((int) $match[1])],
            ['GET', '~^/alsap/Respostas/all$~', static fn(array $match, array $body) => $resposta->all()],
            ['GET', '~^/alsap/Respostas/([0-9]+)$~', static fn(array $match, array $body) => $resposta->one((int) $match[1])],
            ['POST', '~^/alsap/Respostas/create$~', static fn(array $match, array $body) => $resposta->create($body)],
            ['PUT', '~^/alsap/Respostas/update/([0-9]+)$~', static fn(array $match, array $body) => $resposta->update((int) $match[1], $body)],
            ['DELETE', '~^/alsap/Respostas/delete/([0-9]+)$~', static fn(array $match, array $body) => $resposta->delete((int) $match[1])],
        ];
    }
}