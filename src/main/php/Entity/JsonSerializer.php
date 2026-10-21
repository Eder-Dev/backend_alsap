<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Entity;

use PDO;

final class JsonSerializer
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function audio(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'nome' => $row['nome'],
            'transcricao' => $row['transcricao'],
            'audio_url' => $row['audio_url'],
            'tipo' => (int) $row['tipo'],
        ];
    }

    public function endereco(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'nome' => $row['nome'],
            'cep' => $row['cep'],
            'latitude' => $row['latitude'] === null ? null : (float) $row['latitude'],
            'longitude' => $row['longitude'] === null ? null : (float) $row['longitude'],
        ];
    }

    public function narrativa(array $row): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM audio WHERE narrativa = :id');
        $statement->execute(['id' => $row['id']]);
        return [
            'id' => (int) $row['id'],
            'nome' => $row['nome'],
            'idade' => $row['idade'],
            'sexo' => $row['sexo'],
            'escolaridade' => $row['escolaridade'],
            'local' => $row['local'],
            'transcricao' => $row['transcricao'],
            'titulo' => $row['titulo'],
            'audios' => array_map(fn(array $audio): array => $this->audio($audio), $statement->fetchAll()),
        ];
    }

    public function resposta(array $row): array
    {
        $addressStatement = $this->pdo->prepare('SELECT * FROM endereco WHERE id = :id');
        $addressStatement->execute(['id' => $row['endereco_id']]);
        $address = $addressStatement->fetch();

        $audioStatement = $this->pdo->prepare('SELECT * FROM audio WHERE resposta = :id');
        $audioStatement->execute(['id' => $row['id']]);
        return [
            'id' => (int) $row['id'],
            'endereco' => $address === false ? null : $this->endereco($address),
            'audios' => array_map(fn(array $audio): array => $this->audio($audio), $audioStatement->fetchAll()),
        ];
    }

    public function questao(array $row): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM resposta WHERE questao_id = :id');
        $statement->execute(['id' => $row['id']]);
        return [
            'id' => (int) $row['id'],
            'descricao' => $row['descricao'],
            'tipo' => $row['tipo'],
            'respostas' => array_map(fn(array $resposta): array => $this->resposta($resposta), $statement->fetchAll()),
        ];
    }

    public function list(string $resource, array $rows): array
    {
        return array_map(fn(array $row): array => $this->{$resource}($row), $rows);
    }
}