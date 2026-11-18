<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Serviço de autenticação.
 *
 * Gerencia registro, login, troca de senha e sessão de usuários.
 * Utiliza PASSWORD_ARGON2ID para hashing seguro de senhas.
 */
class AuthService
{
    /**
     * Repositório de usuários.
     */
    private UserRepository $userRepo;

    /**
     * Papéis (roles) válidos para registro.
     */
    private const VALID_ROLES = ['customer', 'seller', 'admin'];

    /**
     * @param UserRepository $userRepo Repositório de usuários.
     */
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Registra um novo usuário no sistema.
     *
     * Valida os dados de entrada, cria o hash da senha com Argon2id
     * e persiste o usuário no banco de dados.
     *
     * @param array $data Dados do registro (full_name, email, password, phone, user_role).
     * @return array ['success' => true, 'user_id' => int] ou ['success' => false, 'errors' => string[]].
     */
    public function register(array $data): array
    {
        $errors = $this->validateRegistration($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $userId = $this->userRepo->create([
            'full_name'      => trim($data['full_name']),
            'email'          => mb_strtolower(trim($data['email'])),
            'password_hash'  => $this->hashPassword($data['password']),
            'phone'          => $data['phone'] ?? null,
            'user_role'      => $data['user_role'] ?? 'customer',
            'account_status' => 'pending_verification',
        ]);

        return ['success' => true, 'user_id' => $userId];
    }

    /**
     * Autentica um usuário com e-mail e senha.
     *
     * Verifica se a conta está ativa ou pendente de verificação.
     * Utiliza password_verify para comparação segura.
     *
     * @param string $email    E-mail do usuário.
     * @param string $password Senha em texto plano.
     * @return array ['success' => true, 'user' => User] ou ['success' => false, 'error' => string].
     */
    public function login(string $email, string $password): array
    {
        $row = $this->userRepo->findByEmailWithPassword(mb_strtolower(trim($email)));

        if ($row === null) {
            return ['success' => false, 'error' => 'Credenciais inválidas.'];
        }

        if (!password_verify($password, $row['password_hash'])) {
            return ['success' => false, 'error' => 'Credenciais inválidas.'];
        }

        $allowedStatuses = ['active', 'pending_verification'];
        if (!in_array($row['account_status'], $allowedStatuses, true)) {
            return ['success' => false, 'error' => 'Sua conta está suspensa ou inativa. Entre em contato com o suporte.'];
        }

        // Armazena o ID do usuário na sessão
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['user_id'] = (int) $row['id'];
        }

        unset($row['password_hash']);
        $user = User::fromArray($row);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Altera a senha de um usuário.
     *
     * Verifica a senha atual antes de aplicar a nova.
     *
     * @param int    $userId          ID do usuário.
     * @param string $currentPassword Senha atual.
     * @param string $newPassword     Nova senha.
     * @return array ['success' => true] ou ['success' => false, 'error' => string].
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = $this->userRepo->findById($userId);

        if ($user === null) {
            return ['success' => false, 'error' => 'Usuário não encontrado.'];
        }

        $row = $this->userRepo->findByEmailWithPassword($user->email);

        if ($row === null || !password_verify($currentPassword, $row['password_hash'])) {
            return ['success' => false, 'error' => 'Senha atual incorreta.'];
        }

        if (mb_strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'A nova senha deve ter no mínimo 8 caracteres.'];
        }

        $newHash = $this->hashPassword($newPassword);

        // Atualiza diretamente via PDO pois UserRepository não possui updatePassword
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['hash' => $newHash, 'id' => $userId]);

        return ['success' => true];
    }

    /**
     * Gera o hash de uma senha usando Argon2id.
     *
     * @param string $password Senha em texto plano.
     * @return string Hash da senha.
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Obtém o usuário atualmente autenticado a partir da sessão.
     *
     * @return User|null Objeto User ou null se não autenticado.
     */
    public function getCurrentUser(): ?User
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        $userId = $_SESSION['user_id'] ?? null;

        if ($userId === null) {
            return null;
        }

        return $this->userRepo->findById((int) $userId);
    }

    /**
     * Valida os dados de registro de um novo usuário.
     *
     * @param array $data Dados de registro.
     * @return string[] Lista de mensagens de erro em português (vazia se válido).
     */
    private function validateRegistration(array $data): array
    {
        $errors = [];

        // Nome completo
        if (empty($data['full_name']) || mb_strlen(trim($data['full_name'])) < 3) {
            $errors[] = 'O nome completo é obrigatório e deve ter no mínimo 3 caracteres.';
        }

        // E-mail
        if (empty($data['email'])) {
            $errors[] = 'O e-mail é obrigatório.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'O formato do e-mail é inválido.';
        } elseif ($this->userRepo->emailExists(mb_strtolower(trim($data['email'])))) {
            $errors[] = 'Este e-mail já está cadastrado.';
        }

        // Senha
        if (empty($data['password'])) {
            $errors[] = 'A senha é obrigatória.';
        } elseif (mb_strlen($data['password']) < 8) {
            $errors[] = 'A senha deve ter no mínimo 8 caracteres.';
        }

        // Telefone (opcional, mas se preenchido deve ter formato válido)
        if (!empty($data['phone'])) {
            $phoneDigits = preg_replace('/\D/', '', $data['phone']);
            if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 11) {
                $errors[] = 'O telefone deve ter entre 10 e 11 dígitos (DDD + número).';
            }
        }

        // Papel do usuário
        if (!empty($data['user_role']) && !in_array($data['user_role'], self::VALID_ROLES, true)) {
            $errors[] = 'O tipo de conta informado é inválido.';
        }

        return $errors;
    }
}
