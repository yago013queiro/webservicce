<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo de endereço empresarial do sistema de marketplace.
 *
 * Representa um endereço associado a uma empresa, podendo ser
 * comercial, depósito ou de cobrança.
 */
class BusinessAddress
{
    // Constantes de tipo de endereço
    public const string TYPE_COMMERCIAL = 'commercial';
    public const string TYPE_WAREHOUSE  = 'warehouse';
    public const string TYPE_BILLING    = 'billing';

    private ?int    $id;
    private ?int    $businessId;
    private string  $addressType;
    private string  $postalCode;
    private string  $street;
    private string  $streetNumber;
    private ?string $addressComplement;
    private string  $neighborhood;
    private string  $city;
    private string  $state;
    private string  $country;
    private bool    $isPrimary;
    private ?string $createdAt;
    private ?string $updatedAt;

    /**
     * Cria uma instância de BusinessAddress a partir de um array associativo.
     *
     * @param array<string, mixed> $data Dados do endereço.
     */
    public function __construct(array $data)
    {
        $this->id                = isset($data['id']) ? (int) $data['id'] : null;
        $this->businessId        = isset($data['business_id']) ? (int) $data['business_id'] : null;
        $this->addressType       = (string) ($data['address_type'] ?? self::TYPE_COMMERCIAL);
        $this->postalCode        = (string) ($data['postal_code'] ?? '');
        $this->street            = (string) ($data['street'] ?? '');
        $this->streetNumber      = (string) ($data['street_number'] ?? '');
        $this->addressComplement = $data['address_complement'] ?? null;
        $this->neighborhood      = (string) ($data['neighborhood'] ?? '');
        $this->city              = (string) ($data['city'] ?? '');
        $this->state             = (string) ($data['state'] ?? '');
        $this->country           = (string) ($data['country'] ?? 'BR');
        $this->isPrimary         = (bool) ($data['is_primary'] ?? false);
        $this->createdAt         = $data['created_at'] ?? null;
        $this->updatedAt         = $data['updated_at'] ?? null;
    }

    /**
     * Retorna o ID do endereço.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retorna o ID da empresa associada.
     */
    public function getBusinessId(): ?int
    {
        return $this->businessId;
    }

    /**
     * Retorna o tipo de endereço.
     */
    public function getAddressType(): string
    {
        return $this->addressType;
    }

    /**
     * Retorna o CEP (código postal).
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * Retorna o logradouro (rua/avenida).
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * Retorna o número do endereço.
     */
    public function getStreetNumber(): string
    {
        return $this->streetNumber;
    }

    /**
     * Retorna o complemento do endereço.
     */
    public function getAddressComplement(): ?string
    {
        return $this->addressComplement;
    }

    /**
     * Retorna o bairro.
     */
    public function getNeighborhood(): string
    {
        return $this->neighborhood;
    }

    /**
     * Retorna a cidade.
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Retorna o estado (UF).
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Retorna o país.
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Verifica se é o endereço principal.
     */
    public function getIsPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * Retorna a data/hora de criação do registro.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Retorna a data/hora da última atualização.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Converte o modelo para array associativo.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'business_id'        => $this->businessId,
            'address_type'       => $this->addressType,
            'postal_code'        => $this->postalCode,
            'street'             => $this->street,
            'street_number'      => $this->streetNumber,
            'address_complement' => $this->addressComplement,
            'neighborhood'       => $this->neighborhood,
            'city'               => $this->city,
            'state'              => $this->state,
            'country'            => $this->country,
            'is_primary'         => $this->isPrimary,
            'created_at'         => $this->createdAt,
            'updated_at'         => $this->updatedAt,
        ];
    }

    /**
     * Retorna o endereço completo formatado como string legível.
     *
     * @return string Endereço completo no formato brasileiro.
     */
    public function getFullAddress(): string
    {
        $parts = [
            $this->street,
            $this->streetNumber,
        ];

        if ($this->addressComplement !== null && $this->addressComplement !== '') {
            $parts[] = $this->addressComplement;
        }

        $parts[] = $this->neighborhood;
        $parts[] = $this->city . ' - ' . $this->state;
        $parts[] = 'CEP: ' . $this->postalCode;
        $parts[] = $this->country;

        return implode(', ', array_filter($parts, fn(string $part) => $part !== ''));
    }
}
