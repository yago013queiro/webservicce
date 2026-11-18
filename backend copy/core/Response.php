<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe utilitária estática para respostas HTTP.
 *
 * Fornece métodos padronizados para enviar respostas JSON,
 * redirecionamentos e mensagens de erro ao cliente.
 */
class Response
{
    /**
     * Envia uma resposta JSON genérica e encerra a execução.
     *
     * @param mixed $data       Dados a serem codificados em JSON.
     * @param int   $statusCode Código de status HTTP (padrão 200).
     * @return void
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Envia uma resposta JSON de erro e encerra a execução.
     *
     * @param string $message    Mensagem de erro em português.
     * @param int    $statusCode Código de status HTTP (padrão 400).
     * @param array  $errors     Lista opcional de erros de validação.
     * @return void
     */
    public static function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        self::json($payload, $statusCode);
    }

    /**
     * Envia uma resposta JSON de sucesso e encerra a execução.
     *
     * @param string $message    Mensagem de sucesso em português.
     * @param array  $data       Dados adicionais a incluir na resposta.
     * @param int    $statusCode Código de status HTTP (padrão 200).
     * @return void
     */
    public static function success(string $message, array $data = [], int $statusCode = 200): void
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        self::json($payload, $statusCode);
    }

    /**
     * Redireciona o cliente para a URL informada e encerra a execução.
     *
     * @param string $url URL de destino.
     * @return void
     */
    public static function redirect(string $url): void
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
            http_response_code(302);
        }

        exit;
    }

    /**
     * Envia resposta 401 Não Autorizado.
     *
     * @param string $message Mensagem de erro (padrão em português).
     * @return void
     */
    public static function unauthorized(string $message = 'Não autorizado.'): void
    {
        self::error($message, 401);
    }

    /**
     * Envia resposta 403 Acesso Negado.
     *
     * @param string $message Mensagem de erro (padrão em português).
     * @return void
     */
    public static function forbidden(string $message = 'Acesso negado.'): void
    {
        self::error($message, 403);
    }

    /**
     * Envia resposta 404 Não Encontrado.
     *
     * @param string $message Mensagem de erro (padrão em português).
     * @return void
     */
    public static function notFound(string $message = 'Recurso não encontrado.'): void
    {
        self::error($message, 404);
    }
}
