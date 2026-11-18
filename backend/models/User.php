<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo de usuário do sistema de marketplace.
 *
 * Representa um usuário com seus dados pessoais, papel no sistema
 * e status da conta. Suporta os papéis: cliente, dono de empresa,
 * motorista de entrega e administrador.
 */
class User
{
    // Constantes de papel do usuário
    public const string ROLE_CUSTOMER        = 'customer';
    public const string ROLE_BUSINESS_OWNER  = 'business_owner';
    public const string ROLE_DELIVERY_DRIVER = 'delivery_driver';
    public const string ROLE_ADMINISTRATOR   = 'administrator';

    // Constantes de status da conta
    public const string STATUS_ACTIVE               = 'active';
    public const string STATUS_INACTIVE              = 'inactive';
    public const string STATUS_SUSPENDED             = 'suspended';
    public const string STATUS_PENDING_VERIFICATION  = 'pending_verification';

    private ?int    $id;
    private string  $fullName;
    private string  $email;
    private string  $passwordHash;
    private ?string $phone;
    private string  $userRole;
    private string  $accountStatus;
    private ?string $emailVerifiedAt;
    private ?string $phoneVerifiedAt;
    private ?string $createdAt;
    private ?string $updatedAt;

    /**
     * Cria uma instância de User a partir de um array associativo.
     *
     * @param array<string, mixed> $data Dados do usuário.
     */
    public function __construct(array $data)
    {
        $this->id              = isset($data['id']) ? (int) $data['id'] : null;
        $this->fullName        = (string) ($data['full_name'] ?? '');
        $this->email           = (string) ($data['email'] ?? '');
        $this->passwordHash    = (string) ($data['password_hash'] ?? '');
        $this->phone           = $data['phone'] ?? null;
        $this->userRole        = (string) ($data['user_role'] ?? self::ROLE_CUSTOMER);
        $this->accountStatus   = (string) ($data['account_status'] ?? self::STATUS_PENDING_VERIFICATION);
        $this->emailVerifiedAt = $data['email_verified_at'] ?? null;
        $this->phoneVerifiedAt = $data['phone_verified_at'] ?? null;
        $this->createdAt       = $data['created_at'] ?? null;
        $this->updatedAt       = $data['updated_at'] ?? null;
    }

    /**
     * Retorna o ID do usuário.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retorna o nome completo do usuário.
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * Retorna o e-mail do usuário.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Retorna o hash da senha do usuário.
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * Retorna o telefone do usuário.
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Retorna o papel do usuário no sistema.
     */
    public function getUserRole(): string
    {
        return $this->userRole;
    }

    /**
     * Retorna o status da conta do usuário.
     */
    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    /**
     * Retorna a data/hora de verificação do e-mail.
     */
    public function getEmailVerifiedAt(): ?string
    {
        return $this->emailVerifiedAt;
    }

    /**
     * Retorna a data/hora de verificação do telefone.
     */
    public function getPhoneVerifiedAt(): ?string
    {
        return $this->phoneVerifiedAt;
    }

    /**
     * Retorna a data/hora de criação do registro.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Retorna a data/hora da última atualização do registro.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Converte o modelo para array associativo (exclui o hash da senha).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'full_name'         => $this->fullName,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'user_role'         => $this->userRole,
            'account_status'    => $this->accountStatus,
            'email_verified_at' => $this->emailVerifiedAt,
            'phone_verified_at' => $this->phoneVerifiedAt,
            'created_at'        => $this->createdAt,
            'updated_at'        => $this->updatedAt,
        ];
    }

    /**
     * Verifica se o usuário é um administrador.
     */
    public function isAdmin(): bool
    {
        return $this->userRole === self::ROLE_ADMINISTRATOR;
    }

    /**
     * Verifica se o usuário é um dono de empresa.
     */
    public function isBusinessOwner(): bool
    {
        return $this->userRole === self::ROLE_BUSINESS_OWNER;
    }

    /**
     * Verifica se o usuário é um cliente.
     */
    public function isCustomer(): bool
    {
        return $this->userRole === self::ROLE_CUSTOMER;
    }

    /**
     * Verifica se o usuário é um motorista de entrega.
     */
    public function isDriver(): bool
    {
        return $this->userRole === self::ROLE_DELIVERY_DRIVER;
    }
}
