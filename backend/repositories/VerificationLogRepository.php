<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repositório de logs de verificação.
 *
 * Registra e consulta todas as ações de verificação realizadas
 * no processo de aprovação/rejeição de empresas.
 */
class VerificationLogRepository
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
     * Cria um novo registro de log de verificação.
     *
     * @param array $data Dados do log (business_id, administrator_user_id, action_type, previous_status, new_status, reason).
     * @return int ID do log recém-criado.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO verification_logs (business_id, administrator_user_id, action_type, previous_status, new_status, reason, created_at)
             VALUES (:business_id, :administrator_user_id, :action_type, :previous_status, :new_status, :reason, NOW())'
        );

        $stmt->execute([
            'business_id'            => $data['business_id'],
            'administrator_user_id'  => $data['administrator_user_id'] ?? null,
            'action_type'            => $data['action_type'],
            'previous_status'        => $data['previous_status'] ?? null,
            'new_status'             => $data['new_status'],
            'reason'                 => $data['reason'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Busca todos os logs de verificação de uma empresa, ordenados do mais recente.
     *
     * @param int $businessId ID da empresa.
     * @return array Lista de registros de log como arrays associativos.
     */
    public function findByBusinessId(int $businessId): array
    {
        $stmt = $this->db->prepare(
            'SELECT vl.*, u.full_name as administrator_name
             FROM verification_logs vl
             LEFT JOIN users u ON u.id = vl.administrator_user_id
             WHERE vl.business_id = :business_id
             ORDER BY vl.created_at DESC'
        );
        $stmt->execute(['business_id' => $businessId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca as ações de verificação mais recentes (para todas as empresas).
     *
     * @param int $limit Número máximo de registros a retornar.
     * @return array Lista de registros de log como arrays associativos.
     */
    public function findRecentActions(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT vl.*, u.full_name as administrator_name, b.trade_name as business_name
             FROM verification_logs vl
             LEFT JOIN users u ON u.id = vl.administrator_user_id
             LEFT JOIN businesses b ON b.id = vl.business_id
             ORDER BY vl.created_at DESC
             LIMIT :lmt'
        );
        $stmt->bindValue('lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
