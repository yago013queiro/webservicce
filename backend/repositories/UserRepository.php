<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;

/**
 * Repositório de usuários.
 *
 * Gerencia operações de persistência para a entidade User,
 * incluindo busca, criação e atualização de status.
 */
class UserRepository
{
    /**
     * Instância PDO para acesso ao banco de dados.
     */
    private PDO $db;

    /**
     * @param PDO $db Conexão PDO ativa.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Busca um usuário pelo ID.
     *
     * @param int $id ID do usuário.
     * @return User|null Objeto User ou null se não encontrado.
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, phone, user_role, account_status, email_verified_at, created_at, updated_at
             FROM users
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? User::fromArray($row) : null;
    }

    /**
     * Busca um usuário pelo e-mail.
     *
     * @param string $email E-mail do usuário.
     * @return User|null Objeto User ou null se não encontrado.
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, phone, user_role, account_status, email_verified_at, created_at, updated_at
             FROM users
             WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? User::fromArray($row) : null;
    }

    /**
     * Busca um usuário pelo e-mail incluindo o hash da senha (para autenticação).
     *
     * @param string $email E-mail do usuário.
     * @return array|null Linha bruta do banco incluindo password_hash, ou null.
     */
    public function findByEmailWithPassword(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, phone, user_role, account_status, email_verified_at, password_hash, created_at, updated_at
             FROM users
             WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Cria um novo usuário no banco de dados.
     *
     * @param array $data Dados do usuário (full_name, email, password_hash, phone, user_role, account_status).
     * @return int ID do usuário recém-criado.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash, phone, user_role, account_status, created_at, updated_at)
             VALUES (:full_name, :email, :password_hash, :phone, :user_role, :account_status, NOW(), NOW())'
        );

        $stmt->execute([
            'full_name'      => $data['full_name'],
            'email'          => $data['email'],
            'password_hash'  => $data['password_hash'],
            'phone'          => $data['phone'] ?? null,
            'user_role'      => $data['user_role'] ?? 'customer',
            'account_status' => $data['account_status'] ?? 'pending_verification',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza o status da conta de um usuário.
     *
     * @param int    $id     ID do usuário.
     * @param string $status Novo status da conta.
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET account_status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Marca o e-mail do usuário como verificado.
     *
     * @param int $id ID do usuário.
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function updateEmailVerified(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET email_verified_at = NOW(), updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica se um e-mail já está cadastrado.
     *
     * @param string $email E-mail a verificar.
     * @return bool True se o e-mail já existe no banco.
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM users WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Busca todos os usuários com um determinado papel (role).
     *
     * @param string $role Papel do usuário (ex.: 'admin', 'customer', 'seller').
     * @return User[] Lista de objetos User.
     */
    public function findByRole(string $role): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, phone, user_role, account_status, email_verified_at, created_at, updated_at
             FROM users
             WHERE user_role = :role
             ORDER BY created_at DESC'
        );
        $stmt->execute(['role' => $role]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => User::fromArray($row), $rows);
    }
}
