<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BusinessBankAccount;
use PDO;

/**
 * Repositório de contas bancárias de empresas.
 *
 * Gerencia operações de persistência para dados bancários
 * vinculados às empresas cadastradas no marketplace.
 */
class BankAccountRepository
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
     * Busca a conta bancária de uma empresa.
     *
     * @param int $businessId ID da empresa.
     * @return BusinessBankAccount|null Objeto BusinessBankAccount ou null se não encontrada.
     */
    public function findByBusinessId(int $businessId): ?BusinessBankAccount
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM business_bank_accounts WHERE business_id = :business_id'
        );
        $stmt->execute(['business_id' => $businessId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? BusinessBankAccount::fromArray($row) : null;
    }

    /**
     * Cria um novo registro de conta bancária.
     *
     * @param array $data Dados da conta (business_id, bank_code, bank_name, agency, account_number, account_type, holder_name, holder_document).
     * @return int ID do registro recém-criado.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO business_bank_accounts (business_id, bank_code, bank_name, agency, account_number, account_type, holder_name, holder_document, verification_status, created_at, updated_at)
             VALUES (:business_id, :bank_code, :bank_name, :agency, :account_number, :account_type, :holder_name, :holder_document, :verification_status, NOW(), NOW())'
        );

        $stmt->execute([
            'business_id'         => $data['business_id'],
            'bank_code'           => $data['bank_code'],
            'bank_name'           => $data['bank_name'],
            'agency'              => $data['agency'],
            'account_number'      => $data['account_number'],
            'account_type'        => $data['account_type'] ?? 'corrente',
            'holder_name'         => $data['holder_name'],
            'holder_document'     => preg_replace('/\D/', '', $data['holder_document']),
            'verification_status' => $data['verification_status'] ?? 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza dados de uma conta bancária existente.
     *
     * @param int   $id   ID da conta bancária.
     * @param array $data Dados a atualizar.
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['bank_code', 'bank_name', 'agency', 'account_number', 'account_type', 'holder_name', 'holder_document'];
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields, true)) {
                $fieldValue = ($key === 'holder_document') ? preg_replace('/\D/', '', $value) : $value;
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $fieldValue;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE business_bank_accounts SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza o status de verificação da conta bancária.
     *
     * @param int         $id     ID da conta bancária.
     * @param string      $status Novo status de verificação.
     * @param string|null $method Método de verificação utilizado (opcional).
     * @param string|null $notes  Notas adicionais (opcional).
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function updateVerificationStatus(int $id, string $status, ?string $method = null, ?string $notes = null): bool
    {
        $sql = 'UPDATE business_bank_accounts SET verification_status = :status, updated_at = NOW()';
        $params = ['status' => $status, 'id' => $id];

        if ($method !== null) {
            $sql .= ', verification_method = :method';
            $params['method'] = $method;
        }

        if ($notes !== null) {
            $sql .= ', verification_notes = :notes';
            $params['notes'] = $notes;
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }
}
