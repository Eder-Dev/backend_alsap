<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Controller;

use Br\Udesc\Alsap\Config\HttpResponse;
use PDO;
use Throwable;

final class DatabaseMaintenanceController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function format(): HttpResponse
    {
        $tables = ['audio', 'resposta', 'narrativa', 'questao', 'endereco'];
        $this->pdo->beginTransaction();
        try {
            foreach ($tables as $table) {
                $this->pdo->exec('DELETE FROM `' . $table . '`');
            }
            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }

        foreach ($tables as $table) {
            $this->pdo->exec('ALTER TABLE `' . $table . '` AUTO_INCREMENT = 1');
        }

        return new HttpResponse(['message' => 'Registros do acervo removidos e IDs reiniciados com sucesso.']);
    }
}
