<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Helpers de middleware estáticos.
 *
 * Fornece funcionalidades de rate limiting, validação CSRF,
 * validação de content-type e sanitização de entrada.
 */
class Middleware
{
    /** Chave da sessão para controle de rate limiting. */
    private const string RATE_LIMIT_KEY = '_rate_limits';

    /**
     * Rate limiter simples baseado em sessão.
     *
     * Conta o número de tentativas dentro de uma janela de tempo.
     * Se exceder o limite, retorna 429 com mensagem em português.
     *
     * @param string $key            Identificador único do recurso limitado.
     * @param int    $maxAttempts    Número máximo de tentativas permitidas.
     * @param int    $windowSeconds  Janela de tempo em segundos.
     * @return void
     */
    public static function rateLimit(string $key, int $maxAttempts = 60, int $windowSeconds = 60): void
    {
        $rateLimits = Session::get(self::RATE_LIMIT_KEY, []);
        $now = time();

        if (!isset($rateLimits[$key])) {
            $rateLimits[$key] = [
                'attempts'   => 0,
                'window_start' => $now,
            ];
        }

        $entry = &$rateLimits[$key];

        // Reinicia a janela se o período expirou
        if (($now - $entry['window_start']) >= $windowSeconds) {
            $entry['attempts'] = 0;
            $entry['window_start'] = $now;
        }

        $entry['attempts']++;

        Session::set(self::RATE_LIMIT_KEY, $rateLimits);

        if ($entry['attempts'] > $maxAttempts) {
            $retryAfter = $windowSeconds - ($now - $entry['window_start']);

            if (!headers_sent()) {
                header('Retry-After: ' . max(1, $retryAfter));
            }

            Response::error(
                'Muitas tentativas. Aguarde um momento.',
                429
            );
        }
    }

    /**
     * Valida o token CSRF enviado no cabeçalho X-CSRF-Token.
     *
     * Compara com o token armazenado na sessão usando hash_equals
     * para prevenir ataques de temporização.
     *
     * @return void
     */
    public static function validateCsrfToken(): void
    {
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = Session::get('_csrf_token', '');

        if (empty($headerToken) || empty($sessionToken)) {
            Response::error('Token CSRF ausente ou inválido.', 403);
        }

        if (!hash_equals((string) $sessionToken, (string) $headerToken)) {
            Response::error('Token CSRF inválido. Recarregue a página e tente novamente.', 403);
        }
    }

    /**
     * Gera e armazena um token CSRF na sessão.
     *
     * @return string Token CSRF gerado.
     */
    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $token);

        return $token;
    }

    /**
     * Valida se o Content-Type da requisição corresponde ao esperado.
     *
     * @param string $expected Tipo de conteúdo esperado (padrão: application/json).
     * @return void
     */
    public static function validateContentType(string $expected = 'application/json'): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        // Extrai apenas o tipo, ignorando parâmetros como charset
        $contentType = trim(explode(';', $contentType)[0]);

        if (strcasecmp($contentType, $expected) !== 0) {
            Response::error(
                "Content-Type inválido. Esperado: {$expected}.",
                415
            );
        }
    }

    /**
     * Sanitiza recursivamente os dados de entrada.
     *
     * Remove espaços em branco nas extremidades e tags HTML de strings.
     * Processa arrays e objetos recursivamente.
     *
     * @param array $data Dados a serem sanitizados.
     * @return array Dados sanitizados.
     */
    public static function sanitizeInput(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeInput($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = trim(strip_tags($value));
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
