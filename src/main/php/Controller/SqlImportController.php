<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Controller;

use Br\Udesc\Alsap\Config\HttpResponse;
use InvalidArgumentException;
use PDO;
use Throwable;

final class SqlImportController
{
    private const MAX_FILE_SIZE = 5242880;

    private const TABLE_COLUMNS = [
        'audio' => ['id', 'nome', 'transcricao', 'audio_url', 'tipo', 'resposta', 'narrativa'],
        'endereco' => ['id', 'nome', 'cep', 'latitude', 'longitude'],
        'narrativa' => ['id', 'nome', 'idade', 'sexo', 'escolaridade', 'local', 'transcricao', 'titulo'],
        'questao' => ['id', 'descricao', 'tipo'],
        'resposta' => ['id', 'endereco_id', 'questao_id'],
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function importFile(array $file): HttpResponse
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->uploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
        }

        $name = $file['name'] ?? null;
        $temporaryPath = $file['tmp_name'] ?? null;
        if (!is_string($name) || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'sql'
            || !is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
            throw new InvalidArgumentException('Envie um arquivo SQL válido.');
        }

        $size = filesize($temporaryPath);
        if ($size === false || $size > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('O arquivo SQL deve ter no máximo 5 MB.');
        }

        $sql = file_get_contents($temporaryPath);
        if ($sql === false) {
            throw new InvalidArgumentException('Não foi possível ler o arquivo SQL enviado.');
        }

        return $this->importSql($sql);
    }

    public function importSql(string $sql): HttpResponse
    {
        if (strlen($sql) > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('O arquivo SQL deve ter no máximo 5 MB.');
        }
        if (str_starts_with($sql, "\xEF\xBB\xBF")) {
            $sql = substr($sql, 3);
        }
        if (str_contains($sql, "\0")) {
            throw new InvalidArgumentException('O arquivo SQL contém caracteres inválidos.');
        }

        $statements = $this->splitStatements($sql);
        if ($statements === []) {
            throw new InvalidArgumentException('O arquivo não contém comandos INSERT para importar.');
        }

        $inserts = [];
        foreach ($statements as $index => $statement) {
            try {
                [$table, $columns, $values] = $this->parseInsert($statement);
                foreach ($this->parseRows($values) as $row) {
                    if (count($row) !== count($columns)) {
                        throw new InvalidArgumentException('A quantidade de valores não corresponde às colunas informadas.');
                    }
                }
                $inserts[] = [$table, $columns, $values];
            } catch (InvalidArgumentException $error) {
                throw new InvalidArgumentException('Comando ' . ($index + 1) . ': ' . $error->getMessage(), 0, $error);
            }
        }

        $imported = 0;
        $this->pdo->beginTransaction();
        try {
            foreach ($inserts as [$table, $columns, $values]) {
                $quotedColumns = implode(', ', array_map(static fn(string $column): string => '`' . $column . '`', $columns));
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $statement = $this->pdo->prepare(
                    'INSERT INTO `' . $table . '` (' . $quotedColumns . ') VALUES (' . $placeholders . ')'
                );
                foreach ($this->parseRows($values) as $row) {
                    $statement->execute($row);
                    $imported++;
                }
            }
            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }

        return new HttpResponse([
            'message' => 'Importação concluída: ' . $imported . ' registro(s) adicionado(s).',
            'inserted' => $imported,
        ]);
    }

    private function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $quote = null;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $character = $sql[$index];
            $next = $sql[$index + 1] ?? '';

            if ($quote !== null) {
                $current .= $character;
                if ($character === '\\' && $quote !== '`' && $index + 1 < $length) {
                    $current .= $sql[++$index];
                } elseif ($character === $quote) {
                    if ($next === $quote) {
                        $current .= $sql[++$index];
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }

            if ($character === '#' || ($character === '-' && $next === '-' && isset($sql[$index + 2]) && ctype_space($sql[$index + 2]))) {
                $current .= ' ';
                while ($index < $length && $sql[$index] !== "\n" && $sql[$index] !== "\r") {
                    $index++;
                }
                continue;
            }
            if ($character === '/' && $next === '*') {
                $end = strpos($sql, '*/', $index + 2);
                if ($end === false) {
                    throw new InvalidArgumentException('O arquivo contém um comentário SQL incompleto.');
                }
                $current .= ' ';
                $index = $end + 1;
                continue;
            }
            if ($character === "'" || $character === '"' || $character === '`') {
                $quote = $character;
                $current .= $character;
                continue;
            }
            if ($character === ';') {
                if (trim($current) !== '') {
                    $statements[] = trim($current);
                }
                $current = '';
                continue;
            }

            $current .= $character;
        }

        if ($quote !== null) {
            throw new InvalidArgumentException('O arquivo contém uma string SQL incompleta.');
        }
        if (trim($current) !== '') {
            $statements[] = trim($current);
        }

        return $statements;
    }

    private function parseInsert(string $sql): array
    {
        $pattern = '/^\s*INSERT\s+INTO\s+(?:`([A-Za-z_][A-Za-z0-9_]*)`|([A-Za-z_][A-Za-z0-9_]*))\s*\(([^()]*)\)\s*VALUES\s*(.+)\s*$/is';
        if (preg_match($pattern, $sql, $matches) !== 1) {
            throw new InvalidArgumentException('Só são aceitos comandos INSERT INTO ... VALUES.');
        }

        $table = $matches[1] !== '' ? $matches[1] : $matches[2];
        if (!isset(self::TABLE_COLUMNS[$table])) {
            throw new InvalidArgumentException('A tabela "' . $table . '" não faz parte do acervo permitido.');
        }

        $columns = [];
        foreach (explode(',', $matches[3]) as $column) {
            $column = trim($column);
            if (preg_match('/^(?:`([A-Za-z_][A-Za-z0-9_]*)`|([A-Za-z_][A-Za-z0-9_]*))$/', $column, $columnMatch) !== 1) {
                throw new InvalidArgumentException('A lista de colunas do INSERT é inválida.');
            }
            $name = $columnMatch[1] !== '' ? $columnMatch[1] : $columnMatch[2];
            if (!in_array($name, self::TABLE_COLUMNS[$table], true) || in_array($name, $columns, true)) {
                throw new InvalidArgumentException('A coluna "' . $name . '" não é permitida na tabela "' . $table . '".');
            }
            $columns[] = $name;
        }
        if ($columns === []) {
            throw new InvalidArgumentException('O INSERT deve informar ao menos uma coluna.');
        }

        return [$table, $columns, $matches[4]];
    }

    private function parseRows(string $values): \Generator
    {
        $length = strlen($values);
        $index = 0;
        $hasRows = false;

        while ($index < $length) {
            while ($index < $length && ctype_space($values[$index])) {
                $index++;
            }
            if ($index >= $length) {
                break;
            }
            if ($values[$index] !== '(') {
                throw new InvalidArgumentException('A lista VALUES contém uma expressão não permitida.');
            }

            $start = ++$index;
            $quote = null;
            $closed = false;
            while ($index < $length) {
                $character = $values[$index];
                $next = $values[$index + 1] ?? '';
                if ($quote !== null) {
                    if ($character === '\\' && $index + 1 < $length) {
                        $index += 2;
                        continue;
                    }
                    if ($character === $quote) {
                        if ($next === $quote) {
                            $index += 2;
                            continue;
                        }
                        $quote = null;
                    }
                } elseif ($character === "'" || $character === '"') {
                    $quote = $character;
                } elseif ($character === '(') {
                    throw new InvalidArgumentException('Funções e expressões não são permitidas em VALUES.');
                } elseif ($character === ')') {
                    $tuple = substr($values, $start, $index - $start);
                    $hasRows = true;
                    yield $this->parseValueList($tuple);
                    $index++;
                    $closed = true;
                    break;
                }
                $index++;
            }

            if (!$closed || $quote !== null) {
                throw new InvalidArgumentException('Um grupo de valores do INSERT está incompleto.');
            }

            while ($index < $length && ctype_space($values[$index])) {
                $index++;
            }
            if ($index === $length) {
                break;
            }
            if ($values[$index] !== ',') {
                throw new InvalidArgumentException('A lista VALUES contém conteúdo não permitido.');
            }
            $index++;
            while ($index < $length && ctype_space($values[$index])) {
                $index++;
            }
            if ($index >= $length) {
                throw new InvalidArgumentException('A lista VALUES termina antes de completar um registro.');
            }
        }

        if (!$hasRows) {
            throw new InvalidArgumentException('O INSERT não contém registros.');
        }
    }

    private function parseValueList(string $tuple): array
    {
        $values = [];
        $start = 0;
        $quote = null;
        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $character = $tuple[$index];
            $next = $tuple[$index + 1] ?? '';
            if ($quote !== null) {
                if ($character === '\\' && $index + 1 < $length) {
                    $index++;
                } elseif ($character === $quote) {
                    if ($next === $quote) {
                        $index++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }
            if ($character === "'" || $character === '"') {
                $quote = $character;
            } elseif ($character === ',') {
                $values[] = $this->parseLiteral(substr($tuple, $start, $index - $start));
                $start = $index + 1;
            }
        }

        if ($quote !== null) {
            throw new InvalidArgumentException('Uma string em VALUES está incompleta.');
        }
        $values[] = $this->parseLiteral(substr($tuple, $start));

        return $values;
    }

    private function parseLiteral(string $literal): mixed
    {
        $literal = trim($literal);
        if (strcasecmp($literal, 'NULL') === 0) {
            return null;
        }
        if (preg_match('/^[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?$/', $literal) === 1) {
            return $literal;
        }

        $quote = $literal[0] ?? '';
        if (($quote !== "'" && $quote !== '"') || strlen($literal) < 2 || $literal[strlen($literal) - 1] !== $quote) {
            throw new InvalidArgumentException('Só são aceitos valores literais de texto, números e NULL.');
        }

        $result = '';
        $last = strlen($literal) - 1;
        for ($index = 1; $index < $last; $index++) {
            $character = $literal[$index];
            if ($character === '\\') {
                if ($index + 1 >= $last) {
                    throw new InvalidArgumentException('Uma sequência de escape em VALUES está incompleta.');
                }
                $escape = $literal[++$index];
                $result .= match ($escape) {
                    '0' => "\0",
                    'b' => "\x08",
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    'Z' => "\x1a",
                    default => $escape,
                };
                continue;
            }
            if ($character === $quote) {
                if (($literal[$index + 1] ?? '') !== $quote || $index + 1 >= $last) {
                    throw new InvalidArgumentException('Uma string em VALUES contém aspas inválidas.');
                }
                $index++;
            }
            $result .= $character;
        }

        return $result;
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo SQL excede o limite de tamanho permitido.',
            UPLOAD_ERR_PARTIAL => 'O envio do arquivo SQL foi interrompido.',
            UPLOAD_ERR_NO_FILE => 'Selecione um arquivo SQL para importar.',
            default => 'Não foi possível receber o arquivo SQL.',
        };
    }
}
