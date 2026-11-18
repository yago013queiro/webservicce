<?php

declare(strict_types=1);

/**
 * Ponto de entrada único da API REST.
 *
 * Recebe todas as requisições HTTP, roteia para o controlador apropriado
 * e retorna respostas JSON padronizadas.
 *
 * @see backend/controllers/ para os controladores disponíveis
 */

// =========================================================================
// Configuração de ambiente
// =========================================================================

// Relatório de erros (desabilitar em produção)
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

if (!$isProduction) {
    error_reporting(E_ALL);
    ini_set('display_errors', '0'); // Nunca exibir erros diretamente — sempre JSON
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

ini_set('log_errors', '1');

// =========================================================================
// Headers HTTP
// =========================================================================

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS — origens configuráveis
$allowedOrigins = array_filter(
    explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost,http://localhost:3000')
);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
} elseif (!$isProduction && !empty($origin)) {
    // Em desenvolvimento, permite qualquer origem
    header("Access-Control-Allow-Origin: {$origin}");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Trata preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// =========================================================================
// Autoloader e inicialização
// =========================================================================

require_once __DIR__ . '/../backend/autoload.php';

use App\Core\Response;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\BusinessRegistrationController;
use App\Controllers\AdminVerificationController;

// Inicia a sessão
Session::start();

// =========================================================================
// Roteamento
// =========================================================================

try {
    // Extrai a rota da URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Remove query string
    $uriPath = parse_url($requestUri, PHP_URL_PATH) ?: '';

    // Remove o prefixo do script (ex.: /api.php/)
    $scriptDir = dirname($scriptName);
    $scriptBase = basename($scriptName);

    // Tenta extrair a rota do path info
    $route = '';

    // Formato: /api.php/rota/aqui
    $scriptNamePos = strpos($uriPath, $scriptBase);
    if ($scriptNamePos !== false) {
        $route = substr($uriPath, $scriptNamePos + strlen($scriptBase));
    } else {
        // Formato: /rota/aqui (com rewrite)
        if ($scriptDir !== '/' && $scriptDir !== '\\') {
            $route = substr($uriPath, strlen($scriptDir));
        } else {
            $route = $uriPath;
        }
    }

    // Fallback para query parameter ?route=
    if (empty($route) || $route === '/') {
        $route = $_GET['route'] ?? '';
    }

    // Normaliza a rota: remove barras iniciais/finais
    $route = trim($route, '/');

    // Obtém o método HTTP
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    // =====================================================================
    // Mapa de rotas
    // =====================================================================

    // Chave: "METHOD rota" → Valor: [Classe, Método]
    $routes = [
        // --- Autenticação ---
        'POST auth/register'         => [AuthController::class, 'register'],
        'POST auth/login'            => [AuthController::class, 'login'],
        'POST auth/logout'           => [AuthController::class, 'logout'],
        'GET auth/me'                => [AuthController::class, 'me'],
        'POST auth/recover-password' => [AuthController::class, 'recoverPassword'],

        // --- Cadastro de empresa (multi-step) ---
        'GET business/progress'           => [BusinessRegistrationController::class, 'getProgress'],
        'POST business/info'              => [BusinessRegistrationController::class, 'saveBusinessInfo'],
        'POST business/address'           => [BusinessRegistrationController::class, 'saveAddress'],
        'POST business/entrepreneur'      => [BusinessRegistrationController::class, 'saveEntrepreneur'],
        'POST business/bank-account'      => [BusinessRegistrationController::class, 'saveBankAccount'],
        'POST business/document/upload'   => [BusinessRegistrationController::class, 'uploadDocument'],
        'DELETE business/document/delete' => [BusinessRegistrationController::class, 'deleteDocument'],
        'POST business/submit-review'     => [BusinessRegistrationController::class, 'submitForReview'],
        'GET business/profile'            => [BusinessRegistrationController::class, 'getBusinessProfile'],

        // --- Administração / Verificação ---
        'GET admin/dashboard'              => [AdminVerificationController::class, 'dashboard'],
        'GET admin/pending'                => [AdminVerificationController::class, 'listPending'],
        'GET admin/businesses'             => [AdminVerificationController::class, 'listByStatus'],
        'GET admin/business-detail'        => [AdminVerificationController::class, 'getBusinessDetail'],
        'POST admin/document/approve'      => [AdminVerificationController::class, 'approveDocument'],
        'POST admin/document/reject'       => [AdminVerificationController::class, 'rejectDocument'],
        'POST admin/business/approve'      => [AdminVerificationController::class, 'approveBusiness'],
        'POST admin/business/reject'       => [AdminVerificationController::class, 'rejectBusiness'],
        'POST admin/business/request-docs' => [AdminVerificationController::class, 'requestDocuments'],
        'POST admin/business/suspend'      => [AdminVerificationController::class, 'suspendBusiness'],
        'GET admin/verification-logs'      => [AdminVerificationController::class, 'getVerificationLogs'],
    ];

    // Busca a rota correspondente
    $routeKey = "{$method} {$route}";

    if (!isset($routes[$routeKey])) {
        Response::notFound("Rota não encontrada: {$method} /{$route}");
    }

    [$controllerClass, $controllerMethod] = $routes[$routeKey];

    // Instancia o controlador e chama o método
    $controller = new $controllerClass();
    $controller->$controllerMethod();

} catch (\PDOException $e) {
    // Erro de banco de dados
    $errorMessage = $isProduction
        ? 'Erro interno do servidor. Tente novamente mais tarde.'
        : 'Erro de banco de dados: ' . $e->getMessage();

    Response::error($errorMessage, 500);

} catch (\Throwable $e) {
    // Erro genérico
    $errorMessage = $isProduction
        ? 'Erro interno do servidor. Tente novamente mais tarde.'
        : 'Erro: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine();

    Response::error($errorMessage, 500);
}
