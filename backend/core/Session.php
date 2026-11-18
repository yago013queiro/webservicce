<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Gerenciador de sessão estático.
 *
 * Fornece métodos para iniciar, manipular e destruir sessões,
 * bem como helpers de autenticação e autorização.
 */
class Session
{
    /** Chave da sessão onde os dados do usuário são armazenados. */
    private const string USER_KEY = '_auth_user';

    /** Chave da sessão para mensagens flash. */
    private const string FLASH_KEY = '_flash_message';

    /** Chave da sessão para o token CSRF. */
    private const string CSRF_KEY = '_csrf_token';

    /** Chave da sessão para controle de rate limiting. */
    private const string RATE_LIMIT_KEY = '_rate_limits';

    /**
     * Inicia a sessão com configurações seguras.
     *
     * Configura cookies httponly, samesite=Lax e secure quando em HTTPS.
     *
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

        $cookieParams = [
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly'  => true,
            'samesite' => 'Lax',
        ];

        session_set_cookie_params($cookieParams);
        session_name('APPSESSID');
        session_start();
    }

    /**
     * Define um valor na sessão.
     *
     * @param string $key   Chave do valor.
     * @param mixed  $value Valor a ser armazenado.
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtém um valor da sessão.
     *
     * @param string $key     Chave do valor.
     * @param mixed  $default Valor padrão caso a chave não exista.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica se uma chave existe na sessão.
     *
     * @param string $key Chave a verificar.
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove uma chave da sessão.
     *
     * @param string $key Chave a remover.
     * @return void
     */
    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destrói a sessão completamente.
     *
     * Remove todos os dados e invalida o cookie de sessão.
     *
     * @return void
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Regenera o ID da sessão para prevenir fixação de sessão.
     *
     * Deve ser chamado após login bem-sucedido.
     *
     * @return void
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Armazena os dados do usuário autenticado na sessão.
     *
     * @param array $userData Dados do usuário (sem password_hash).
     * @return void
     */
    public static function setUser(array $userData): void
    {
        // Nunca armazenar o hash da senha na sessão
        unset($userData['password_hash'], $userData['password']);

        $_SESSION[self::USER_KEY] = $userData;
    }

    /**
     * Retorna os dados do usuário autenticado ou null.
     *
     * @return array|null
     */
    public static function getUser(): ?array
    {
        return $_SESSION[self::USER_KEY] ?? null;
    }

    /**
     * Retorna o ID do usuário autenticado ou null.
     *
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        $user = self::getUser();
        return $user !== null ? (int) ($user['id'] ?? 0) ?: null : null;
    }

    /**
     * Verifica se o usuário está autenticado.
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return self::getUser() !== null;
    }

    /**
     * Verifica se o usuário autenticado é administrador.
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        $user = self::getUser();
        return $user !== null && ($user['role'] ?? '') === 'admin';
    }

    /**
     * Exige que o usuário esteja autenticado.
     *
     * Caso contrário, retorna 401 e encerra a execução.
     *
     * @return void
     */
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            Response::unauthorized('Você precisa estar autenticado para acessar este recurso.');
        }
    }

    /**
     * Exige que o usuário seja administrador.
     *
     * Caso contrário, retorna 403 e encerra a execução.
     *
     * @return void
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (!self::isAdmin()) {
            Response::forbidden('Acesso restrito a administradores.');
        }
    }

    /**
     * Exige que o usuário tenha um papel (role) específico.
     *
     * @param string $role Nome do papel requerido.
     * @return void
     */
    public static function requireRole(string $role): void
    {
        self::requireAuth();

        $user = self::getUser();
        if (($user['role'] ?? '') !== $role) {
            Response::forbidden("Acesso restrito ao perfil '{$role}'.");
        }
    }

    /**
     * Armazena uma mensagem flash na sessão.
     *
     * Mensagens flash são exibidas uma única vez e removidas automaticamente.
     *
     * @param string $type    Tipo da mensagem (success, error, warning, info).
     * @param string $message Conteúdo da mensagem.
     * @return void
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION[self::FLASH_KEY] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Retorna e remove a mensagem flash da sessão.
     *
     * @return array|null Array com 'type' e 'message', ou null se não houver.
     */
    public static function getFlash(): ?array
    {
        $flash = $_SESSION[self::FLASH_KEY] ?? null;

        if ($flash !== null) {
            unset($_SESSION[self::FLASH_KEY]);
        }

        return $flash;
    }
}
