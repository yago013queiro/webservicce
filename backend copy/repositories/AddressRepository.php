<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BusinessAddress;
use PDO;

/**
 * Repositório de endereços de empresas.
 *
 * Gerencia operações de persistência para endereços comerciais
 * vinculados a empresas cadastradas no marketplace.
 */
class AddressRepository
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
     * Busca todos os endereços de uma empresa.
     *
     * @param int $businessId ID da empresa.
     * @return BusinessAddress[] Lista de objetos BusinessAddress.
     */
    public function findByBusinessId(int $businessId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM business_addresses WHERE business_id = :business_id ORDER BY is_primary DESC, created_at ASC'
        );
        $stmt->execute(['business_id' => $businessId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => BusinessAddress::fromArray($row), $rows);
    }

    /**
     * Busca o endereço principal de uma empresa.
     *
     * @param int $businessId ID da empresa.
     * @return BusinessAddress|null Objeto BusinessAddress ou null se não encontrado.
     */
    public function findPrimaryByBusinessId(int $businessId): ?BusinessAddress
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM business_addresses WHERE business_id = :business_id AND is_primary = 1 LIMIT 1'
        );
        $stmt->execute(['business_id' => $businessId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? BusinessAddress::fromArray($row) : null;
    }

    /**
     * Cria um novo registro de endereço no banco de dados.
     *
     * @param array $data Dados do endereço (business_id, street, number, complement, neighborhood, city, state, zip_code, is_primary).
     * @return int ID do endereço recém-criado.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO business_addresses (business_id, street, number, complement, neighborhood, city, state, zip_code, is_primary, created_at, updated_at)
             VALUES (:business_id, :street, :number, :complement, :neighborhood, :city, :state, :zip_code, :is_primary, NOW(), NOW())'
        );

        $stmt->execute([
            'business_id'  => $data['business_id'],
            'street'       => $data['street'],
            'number'       => $data['number'],
            'complement'   => $data['complement'] ?? null,
            'neighborhood' => $data['neighborhood'],
            'city'         => $data['city'],
            'state'        => $data['state'],
            'zip_code'     => preg_replace('/\D/', '', $data['zip_code']),
            'is_primary'   => $data['is_primary'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza um endereço existente.
     *
     * @param int   $id   ID do endereço.
     * @param array $data Dados a atualizar.
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['street', 'number', 'complement', 'neighborhood', 'city', 'state', 'zip_code', 'is_primary'];
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields, true)) {
                $fieldValue = ($key === 'zip_code') ? preg_replace('/\D/', '', $value) : $value;
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $fieldValue;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE business_addresses SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Exclui todos os endereços de uma empresa.
     *
     * @param int $businessId ID da empresa.
     * @return bool True se ao menos uma linha foi excluída.
     */
    public function deleteByBusinessId(int $businessId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM business_addresses WHERE business_id = :business_id'
        );
        $stmt->execute(['business_id' => $businessId]);

        return $stmt->rowCount() > 0;
    }
}
