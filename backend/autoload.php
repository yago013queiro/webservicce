<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 e carregamento de configuração.
 *
 * Registra o autoloader para o namespace App\, carrega variáveis
 * de ambiente do arquivo .env e arquivos de configuração.
 */

// Define o caminho raiz do projeto
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('BACKEND_PATH', BASE_PATH . 'backend' . DIRECTORY_SEPARATOR);

/**
 * Registra o autoloader PSR-4 para o namespace App\.
 *
 * Mapeia App\Namespace\Class para backend/namespace/Class.php,
 * convertendo separadores de namespace para separadores de diretório.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $prefixLength = strlen($prefix);

    // Verifica se a classe pertence ao namespace App\
    if (strncmp($class, $prefix, $prefixLength) !== 0) {
        return;
    }

    // Remove o prefixo do namespace
    $relativeClass = substr($class, $prefixLength);

    // Converte namespace em caminho de diretório (case-sensitive)
    // App\Core\Session → backend/core/Session.php
    // App\Controllers\AuthController → backend/controllers/AuthController.php
    $namespaceParts = explode('\\', $relativeClass);
    $className = array_pop($namespaceParts);

    // Converte os diretórios do namespace para minúsculas
    $directory = '';
    if (!empty($namespaceParts)) {
        $directory = implode(DIRECTORY_SEPARATOR, array_map('strtolower', $namespaceParts)) . DIRECTORY_SEPARATOR;
    }

    $file = BACKEND_PATH . $directory . $className . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Carrega as variáveis de ambiente do arquivo .env para $_ENV e putenv().
 *
 * Suporta formato KEY=VALUE, comentários (#) e aspas simples/duplas.
 */
$envFile = BASE_PATH . '.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines !== false) {
        foreach ($lines as $line) {
            // Ignora linhas de comentário
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Separa chave e valor
            $eqPos = strpos($line, '=');
            if ($eqPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eqPos));
            $value = trim(substr($line, $eqPos + 1));

            // Remove aspas do valor
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Define a variável de ambiente
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

/**
 * Carrega os arquivos de configuração, se existirem.
 */
$configDir = BACKEND_PATH . 'config' . DIRECTORY_SEPARATOR;

if (is_dir($configDir)) {
    $configFiles = glob($configDir . '*.php');

    if ($configFiles !== false) {
        foreach ($configFiles as $configFile) {
            require_once $configFile;
        }
    }
}
