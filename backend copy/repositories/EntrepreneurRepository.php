<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\EntrepreneurData;
use PDO;

/**
 * Repositório de dados do empreendedor/sócio.
 *
 * Gerencia operações de persistência para os dados pessoais
 * do responsável legal pela empresa (CPF, RG, etc.).
 */
class EntrepreneurRepository
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
     * Busca os dados do empreendedor pela empresa.
     *
     * @param int $businessId ID da empresa.
     * @return EntrepreneurData|null Objeto EntrepreneurData ou null se não encontrado.
     */
    public function findByBusinessId(int $businessId): ?EntrepreneurData
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM entrepreneur_data WHERE business_id = :business_id'
        );
        $stmt->execute(['business_id' => $businessId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? EntrepreneurData::fromArray($row) : null;
    }

    /**
     * Cria um novo registro de dados do empreendedor.
     *
     * @param array $data Dados do empreendedor (business_id, full_name, cpf, rg, birth_date, mother_name, nationality, etc.).
     * @return int ID do registro recém-criado.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO entrepreneur_data (business_id, full_name, cpf, rg, birth_date, mother_name, nationality, verification_status, created_at, updated_at)
             VALUES (:business_id, :full_name, :cpf, :rg, :birth_date, :mother_name, :nationality, :verification_status, NOW(), NOW())'
        );

        $stmt->execute([
            'business_id'         => $data['business_id'],
            'full_name'           => $data['full_name'],
            'cpf'                 => preg_replace('/\D/', '', $data['cpf']),
            'rg'                  => $data['rg'] ?? null,
            'birth_date'          => $data['birth_date'] ?? null,
            'mother_name'         => $data['mother_name'] ?? null,
            'nationality'         => $data['nationality'] ?? 'Brasileira',
            'verification_status' => $data['verification_status'] ?? 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza os dados de um empreendedor existente.
     *
     * @param int   $id   ID do registro do empreendedor.
     * @param array $data Dados a atualizar.
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['full_name', 'cpf', 'rg', 'birth_date', 'mother_name', 'nationality'];
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields, true)) {
                $fieldValue = ($key === 'cpf') ? preg_replace('/\D/', '', $value) : $value;
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $fieldValue;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE entrepreneur_data SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza o status de verificação dos dados do empreendedor.
     *
     * @param int    $id     ID do registro.
     * @param string $status Novo status de verificação.
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function updateVerificationStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE entrepreneur_data SET verification_status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica se um CPF já está cadastrado para outra empresa.
     *
     * @param string $cpf               CPF a verificar.
     * @param int    $excludeBusinessId  ID da empresa a excluir da busca.
     * @return bool True se o CPF já está em uso por outra empresa.
     */
    public function cpfExistsForOtherBusiness(string $cpf, int $excludeBusinessId): bool
    {
        $sanitized = preg_replace('/\D/', '', $cpf);

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM entrepreneur_data WHERE cpf = :cpf AND business_id != :business_id'
        );
        $stmt->execute(['cpf' => $sanitized, 'business_id' => $excludeBusinessId]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
