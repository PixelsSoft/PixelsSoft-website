<?php

namespace App\Services;

use PDO;
use PDOException;

class WordPressSqlLoader
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $username,
        private readonly string $password,
        private readonly string $importDatabase,
    ) {}

    public function load(string $sqlFilePath): void
    {
        if (!is_readable($sqlFilePath)) {
            throw new \InvalidArgumentException("SQL file not readable: {$sqlFilePath}");
        }

        $pdo = $this->adminPdo();
        $pdo->exec("DROP DATABASE IF EXISTS `{$this->importDatabase}`");
        $pdo->exec("CREATE DATABASE `{$this->importDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$this->importDatabase}`");
        $pdo->exec("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION'");

        $handle = fopen($sqlFilePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open SQL file.');
        }

        $capture = false;
        $buffer = '';
        $executed = 0;

        while (($line = fgets($handle)) !== false) {
            if (str_contains($line, 'CREATE TABLE `wp_posts`')) {
                $capture = true;
                $buffer = $line;
                continue;
            }

            if (!$capture) {
                continue;
            }

            if (str_contains($line, '-- Table structure for table `wp_revslider_css`')) {
                break;
            }

            $buffer .= $line;

            if (preg_match('/;\s*$/', $line)) {
                $statement = str_replace('utf8mb4_unicode_520_ci', 'utf8mb4_unicode_ci', trim($buffer));
                if ($statement !== '') {
                    try {
                        $pdo->exec($statement);
                        $executed++;
                    } catch (PDOException $e) {
                        if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                            throw $e;
                        }
                    }
                }
                $buffer = '';
            }
        }

        fclose($handle);

        if ($executed === 0) {
            throw new \RuntimeException('No wp_posts statements were imported from the SQL file.');
        }
    }

    public function cleanup(): void
    {
        $this->adminPdo()->exec("DROP DATABASE IF EXISTS `{$this->importDatabase}`");
    }

    private function adminPdo(): PDO
    {
        return new PDO(
            "mysql:host={$this->host};port={$this->port};charset=utf8mb4",
            $this->username,
            $this->password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}
