<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Business;
use PDO;

/**
 * Repositório de empresas/negócios.
 *
 * Gerencia operações de persistência para a entidade Business,
 * incluindo busca por CNPJ, status e controle de verificação.
 */
class BusinessRepository
{
    /**
     * Instância PDO para acesso ao banco de dados.
     */
    private PDO $db;

    /**
     * Campos permitidos para atualização via método update().
     */
    private const ALLOWED_UPDATE_FIELDS = [
        'trade_name',
        'legal_name',
        'cnpj',
        'business_type',
        'business_category',
        'description',
        'phone',
        'email',
        'website',
    ];

    /**
     * @param PDO $db Conexão PDO ativa.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Busca uma empresa pelo ID.
     *
     * @param int $id ID da empresa.
     * @return Business|null Objeto Business ou null se não encontrada.
     */
    public function findById(int $id): ?Business
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM businesses WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Business::fromArray($row) : null;
    }

    /**
     * Busca uma empresa pelo ID do proprietário.
     *
     * @param int $userId ID do usuário proprietário.
     * @return Business|null Objeto Business ou null se não encontrada.
     */
    public function findByOwnerId(int $userId): ?Business
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM businesses WHERE owner_user_id = :owner_user_id'
        );
        $stmt->execute(['owner_user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Business::fromArray($row) : null;
    }

    /**
     * Busca uma empresa pelo CNPJ (apenas dígitos).
     *
     * @param string $cnpj CNPJ sanitizado (somente dígitos).
     * @return Business|null Objeto Business ou null se não encontrada.
     */
    public function findByCnpj(string $cnpj): ?Business
    {
        $sanitized = preg_replace('/\D/', '', $cnpj);

        $stmt = $this->db->prepare(
            'SELECT * FROM businesses WHERE cnpj = :cnpj'
        );
        $stmt->execute(['cnpj' => $sanitized]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Business::fromArray($row) : null;
    }

    /**
     * Cria um novo registro de empresa no banco de dados.
     *
     * @param array $data Dados da empresa.
     * @return int ID da empresa recém-criada.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO businesses (owner_user_id, trade_name, legal_name, cnpj, business_type, business_category, description, phone, email, website, verification_status, cnpj_status, created_at, updated_at)
             VALUES (:owner_user_id, :trade_name, :legal_name, :cnpj, :business_type, :business_category, :description, :phone, :email, :website, :verification_status, :cnpj_status, NOW(), NOW())'
        );

        $stmt->execute([
            'owner_user_id'       => $data['owner_user_id'],
            'trade_name'          => $data['trade_name'],
            'legal_name'          => $data['legal_name'],
            'cnpj'                => preg_replace('/\D/', '', $data['cnpj']),
            'business_type'       => $data['business_type'] ?? null,
            'business_category'   => $data['business_category'] ?? null,
            'description'         => $data['description'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'email'               => $data['email'] ?? null,
            'website'             => $data['website'] ?? null,
            'verification_status' => $data['verification_status'] ?? 'draft',
            'cnpj_status'         => $data['cnpj_status'] ?? 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza campos permitidos de uma empresa.
     *
     * @param int   $id   ID da empresa.
     * @param array $data Dados a atualizar (somente campos permitidos serão considerados).
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, self::ALLOWED_UPDATE_FIELDS, true)) {
                $fieldValue = ($key === 'cnpj') ? preg_replace('/\D/', '', $value) : $value;
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $fieldValue;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE businesses SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza o status de verificação de uma empresa, com notas opcionais.
     *
     * @param int         $id     ID da empresa.
     * @param string      $status Novo status de verificação.
     * @param string|null $notes  Notas adicionais (opcional).
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        $sql = 'UPDATE businesses SET verification_status = :status, updated_at = NOW()';
        $params = ['status' => $status, 'id' => $id];

        if ($notes !== null) {
            $sql .= ', admin_notes = :notes';
            $params['notes'] = $notes;
        }

        if ($status === 'approved') {
            $sql .= ', approved_at = NOW()';
        } elseif ($status === 'rejected') {
            $sql .= ', rejected_at = NOW()';
        } elseif ($status === 'pending_review') {
            $sql .= ', submitted_at = NOW()';
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza o status de validação do CNPJ.
     *
     * @param int    $id     ID da empresa.
     * @param string $status Novo status do CNPJ.
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function updateCnpjStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE businesses SET cnpj_status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Busca todas as empresas com um determinado status de verificação.
     *
     * @param string $status Status de verificação.
     * @return Business[] Lista de objetos Business.
     */
    public function findByStatus(string $status): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM businesses WHERE verification_status = :status ORDER BY created_at DESC'
        );
        $stmt->execute(['status' => $status]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => Business::fromArray($row), $rows);
    }

    /**
     * Busca todas as empresas pendentes de revisão, ordenadas pela data de submissão (mais antigas primeiro).
     *
     * @return Business[] Lista de objetos Business pendentes de revisão.
     */
    public function findAllPendingReview(): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM businesses WHERE verification_status = :status ORDER BY submitted_at ASC'
        );
        $stmt->execute(['status' => 'pending_review']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => Business::fromArray($row), $rows);
    }

    /**
     * Verifica se um CNPJ já está cadastrado.
     *
     * @param string $cnpj CNPJ a verificar.
     * @return bool True se o CNPJ já existe no banco.
     */
    public function cnpjExists(string $cnpj): bool
    {
        $sanitized = preg_replace('/\D/', '', $cnpj);

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM businesses WHERE cnpj = :cnpj'
        );
        $stmt->execute(['cnpj' => $sanitized]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Retorna contagem de empresas agrupadas por status de verificação.
     *
     * @return array Array associativo status => contagem.
     */
    public function countByStatus(): array
    {
        $stmt = $this->db->query(
            'SELECT verification_status, COUNT(*) as total FROM businesses GROUP BY verification_status'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['verification_status']] = (int) $row['total'];
        }

        return $counts;
    }
}
