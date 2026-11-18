<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Middleware;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AddressRepository;
use App\Repositories\BankAccountRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\EntrepreneurRepository;
use App\Repositories\UserRepository;
use App\Repositories\VerificationLogRepository;

/**
 * Controlador de verificação administrativa.
 *
 * Fornece endpoints para administradores revisarem, aprovarem,
 * rejeitarem e gerenciarem cadastros de empresas no marketplace.
 *
 * Requer usuário autenticado com papel 'admin'.
 */
class AdminVerificationController
{
    private BusinessRepository $businessRepo;
    private AddressRepository $addressRepo;
    private EntrepreneurRepository $entrepreneurRepo;
    private BankAccountRepository $bankAccountRepo;
    private DocumentRepository $documentRepo;
    private UserRepository $userRepo;
    private VerificationLogRepository $logRepo;

    /**
     * Inicializa todos os repositórios necessários.
     */
    public function __construct()
    {
        $db = Database::getInstance();
        $this->businessRepo = new BusinessRepository($db);
        $this->addressRepo = new AddressRepository($db);
        $this->entrepreneurRepo = new EntrepreneurRepository($db);
        $this->bankAccountRepo = new BankAccountRepository($db);
        $this->documentRepo = new DocumentRepository($db);
        $this->userRepo = new UserRepository($db);
        $this->logRepo = new VerificationLogRepository($db);
    }

    /**
     * Retorna estatísticas do painel administrativo.
     *
     * Handles GET /api/admin/dashboard.
     *
     * @return void
     */
    public function dashboard(): void
    {
        Session::requireAdmin();

        $counts = $this->businessRepo->countByStatus();
        $recentActions = $this->logRepo->findRecentActions(20);

        Response::success('Painel administrativo.', [
            'stats' => [
                'pending_count'  => $counts['pending_review'] ?? 0,
                'approved_count' => $counts['approved'] ?? 0,
                'rejected_count' => $counts['rejected'] ?? 0,
                'draft_count'    => $counts['draft'] ?? 0,
                'suspended_count' => $counts['suspended'] ?? 0,
                'total'          => array_sum($counts),
            ],
            'recent_actions' => $recentActions,
        ]);
    }

    /**
     * Lista empresas pendentes de revisão.
     *
     * Handles GET /api/admin/pending.
     *
     * @return void
     */
    public function listPending(): void
    {
        Session::requireAdmin();

        $businesses = $this->businessRepo->findAllPendingReview();
        $result = array_map(fn($b) => $this->objectToArray($b), $businesses);

        Response::success('Empresas pendentes de revisão.', [
            'businesses' => $result,
            'total'      => count($result),
        ]);
    }

    /**
     * Lista empresas filtradas por status de verificação.
     *
     * Handles GET /api/admin/businesses?status=X.
     *
     * @return void
     */
    public function listByStatus(): void
    {
        Session::requireAdmin();

        $status = trim($_GET['status'] ?? '');

        $allowedStatuses = [
            'draft',
            'pending_review',
            'approved',
            'rejected',
            'suspended',
            'corrections_requested',
        ];

        if (empty($status) || !in_array($status, $allowedStatuses, true)) {
            Response::error(
                'Status inválido. Valores permitidos: ' . implode(', ', $allowedStatuses),
                422
            );
        }

        $businesses = $this->businessRepo->findByStatus($status);
        $result = array_map(fn($b) => $this->objectToArray($b), $businesses);

        Response::success("Empresas com status '{$status}'.", [
            'businesses' => $result,
            'total'      => count($result),
            'status'     => $status,
        ]);
    }

    /**
     * Retorna o detalhamento completo de uma empresa.
     *
     * Handles GET /api/admin/business-detail?id=X.
     *
     * @return void
     */
    public function getBusinessDetail(): void
    {
        Session::requireAdmin();

        $businessId = (int) ($_GET['id'] ?? 0);

        if ($businessId <= 0) {
            Response::error('ID da empresa é obrigatório.', 422);
        }

        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            Response::notFound('Empresa não encontrada.');
        }

        $owner = $this->userRepo->findById((int) $business->owner_user_id);
        $addresses = $this->addressRepo->findByBusinessId($businessId);
        $entrepreneur = $this->entrepreneurRepo->findByBusinessId($businessId);
        $bankAccount = $this->bankAccountRepo->findByBusinessId($businessId);
        $documents = $this->documentRepo->findByBusinessId($businessId);
        $logs = $this->logRepo->findByBusinessId($businessId);

        $addressesArray = array_map(fn($a) => $this->objectToArray($a), $addresses);
        $documentsArray = array_map(fn($d) => $this->objectToArray($d), $documents);

        Response::success('Detalhes da empresa.', [
            'business'     => $this->objectToArray($business),
            'owner'        => $owner !== null ? $this->objectToArray($owner) : null,
            'addresses'    => $addressesArray,
            'entrepreneur' => $entrepreneur !== null ? $this->objectToArray($entrepreneur) : null,
            'bank_account' => $bankAccount !== null ? $this->objectToArray($bankAccount) : null,
            'documents'    => $documentsArray,
            'logs'         => $logs,
        ]);
    }

    /**
     * Aprova um documento específico.
     *
     * Handles POST /api/admin/document/approve.
     *
     * @return void
     */
    public function approveDocument(): void
    {
        Session::requireAdmin();

        $input = $this->getJsonInput();
        $documentId = (int) ($input['document_id'] ?? 0);

        if ($documentId <= 0) {
            Response::error('ID do documento é obrigatório.', 422);
        }

        $document = $this->documentRepo->findById($documentId);

        if ($document === null) {
            Response::notFound('Documento não encontrado.');
        }

        $adminId = Session::getUserId();

        $this->documentRepo->updateStatus($documentId, 'approved', $adminId);

        // Registra no log
        $this->logRepo->create([
            'business_id'           => (int) $document->business_id,
            'administrator_user_id' => $adminId,
            'action_type'           => 'document_approved',
            'previous_status'       => $document->status ?? 'pending',
            'new_status'            => 'approved',
            'reason'                => $input['notes'] ?? null,
        ]);

        Response::success('Documento aprovado com sucesso.');
    }

    /**
     * Rejeita um documento específico com motivo.
     *
     * Handles POST /api/admin/document/reject.
     *
     * @return void
     */
    public function rejectDocument(): void
    {
        Session::requireAdmin();

        $input = $this->getJsonInput();
        $documentId = (int) ($input['document_id'] ?? 0);

        if ($documentId <= 0) {
            Response::error('ID do documento é obrigatório.', 422);
        }

        if (empty($input['reason'])) {
            Response::error('O motivo da rejeição é obrigatório.', 422);
        }

        $document = $this->documentRepo->findById($documentId);

        if ($document === null) {
            Response::notFound('Documento não encontrado.');
        }

        $adminId = Session::getUserId();

        $this->documentRepo->updateStatus($documentId, 'rejected', $adminId, $input['reason']);

        // Registra no log
        $this->logRepo->create([
            'business_id'           => (int) $document->business_id,
            'administrator_user_id' => $adminId,
            'action_type'           => 'document_rejected',
            'previous_status'       => $document->status ?? 'pending',
            'new_status'            => 'rejected',
            'reason'                => $input['reason'],
        ]);

        Response::success('Documento rejeitado.');
    }

    /**
     * Aprova o cadastro completo de uma empresa.
     *
     * Handles POST /api/admin/business/approve.
     *
     * @return void
     */
    public function approveBusiness(): void
    {
        Session::requireAdmin();

        $input = $this->getJsonInput();
        $businessId = (int) ($input['business_id'] ?? 0);

        if ($businessId <= 0) {
            Response::error('ID da empresa é obrigatório.', 422);
        }

        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            Response::notFound('Empresa não encontrada.');
        }

        $currentStatus = $business->verification_status ?? 'draft';

        if ($currentStatus !== 'pending_review') {
            Response::error('Somente empresas com status "Em Revisão" podem ser aprovadas.', 422);
        }

        $adminId = Session::getUserId();
        $notes = $input['notes'] ?? null;

        $this->businessRepo->updateStatus($businessId, 'approved', $notes);

        // Ativa a conta do proprietário
        $this->userRepo->updateStatus((int) $business->owner_user_id, 'active');

        // Registra no log
        $this->logRepo->create([
            'business_id'           => $businessId,
            'administrator_user_id' => $adminId,
            'action_type'           => 'business_approved',
            'previous_status'       => $currentStatus,
            'new_status'            => 'approved',
            'reason'                => $notes,
        ]);

        Response::success('Empresa aprovada com sucesso!');
    }

    /**
     * Rejeita o cadastro de uma empresa com motivo.
     *
     * Handles POST /api/admin/business/reject.
     *
     * @return void
     */
    public function rejectBusiness(): void
    {
        Session::requireAdmin();

        $input = $this->getJsonInput();
        $businessId = (int) ($input['business_id'] ?? 0);

        if ($businessId <= 0) {
            Response::error('ID da empresa é obrigatório.', 422);
        }

        if (empty($input['reason'])) {
            Response::error('O motivo da rejeição é obrigatório.', 422);
        }

        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            Response::notFound('Empresa não encontrada.');
        }

        $currentStatus = $business->verification_status ?? 'draft';

        if ($currentStatus !== 'pending_review') {
            Response::error('Somente empresas com status "Em Revisão" podem ser rejeitadas.', 422);
        }

        $adminId = Session::getUserId();

        $this->businessRepo->updateStatus($businessId, 'rejected', $input['reason']);

        // Registra no log
        $this->logRepo->create([
            'business_id'           => $businessId,
            'administrator_user_id' => $adminId,
            'action_type'           => 'business_rejected',
            'previous_status'       => $currentStatus,
            'new_status'            => 'rejected',
            'reason'                => $input['reason'],
        ]);

        Response::success('Empresa rejeitada.');
    }

    /**
     * Solicita documentos adicionais ou correções.
     *
     * Handles POST /api/admin/business/request-docs.
     *
     * @return void
     */
    public function requestDocuments(): void
    {
        Session::requireAdmin();

        $input = $this->getJsonInput();
        $businessId = (int) ($input['business_id'] ?? 0);

        if ($businessId <= 0) {
            Response::error('ID da empresa é obrigatório.', 422);
        }

        if (empty($input['reason'])) {
            Response::error('Descreva quais documentos ou correções são necessários.', 422);
        }

        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            Response::notFound('Empresa não encontrada.');
        }

        $currentStatus = $business->verification_status ?? 'draft';

        if (!in_array($currentStatus, ['pending_review', 'approved'], true)) {
            Response::error('Não é possível solicitar documentos para o status atual da empresa.', 422);
        }

        $adminId = Session::getUserId();

        $this->businessRepo->updateStatus($businessId, 'corrections_requested', $input['reason']);

        // Registra no log
        $this->logRepo->create([
            'business_id'           => $businessId,
            'administrator_user_id' => $adminId,
            'action_type'           => 'documents_requested',
            'previous_status'       => $currentStatus,
            'new_status'            => 'corrections_requested',
            'reason'                => $input['reason'],
        ]);

        Response::success('Solicitação de documentos/correções enviada com sucesso.');
    }

    /**
     * Suspende uma empresa aprovada.
     *
     * Handles POST /api/admin/business/suspend.
     *
     * @return void
     */
    public function suspendBusiness(): void
    {
        Session::requireAdmin();

        $input = $this->getJsonInput();
        $businessId = (int) ($input['business_id'] ?? 0);

        if ($businessId <= 0) {
            Response::error('ID da empresa é obrigatório.', 422);
        }

        if (empty($input['reason'])) {
            Response::error('O motivo da suspensão é obrigatório.', 422);
        }

        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            Response::notFound('Empresa não encontrada.');
        }

        $currentStatus = $business->verification_status ?? 'draft';

        if ($currentStatus !== 'approved') {
            Response::error('Somente empresas aprovadas podem ser suspensas.', 422);
        }

        $adminId = Session::getUserId();

        $this->businessRepo->updateStatus($businessId, 'suspended', $input['reason']);

        // Suspende a conta do proprietário também
        $this->userRepo->updateStatus((int) $business->owner_user_id, 'suspended');

        // Registra no log
        $this->logRepo->create([
            'business_id'           => $businessId,
            'administrator_user_id' => $adminId,
            'action_type'           => 'business_suspended',
            'previous_status'       => $currentStatus,
            'new_status'            => 'suspended',
            'reason'                => $input['reason'],
        ]);

        Response::success('Empresa suspensa com sucesso.');
    }

    /**
     * Retorna o histórico de verificação (trilha de auditoria) de uma empresa.
     *
     * Handles GET /api/admin/verification-logs?business_id=X.
     *
     * @return void
     */
    public function getVerificationLogs(): void
    {
        Session::requireAdmin();

        $businessId = (int) ($_GET['business_id'] ?? 0);

        if ($businessId <= 0) {
            Response::error('ID da empresa é obrigatório.', 422);
        }

        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            Response::notFound('Empresa não encontrada.');
        }

        $logs = $this->logRepo->findByBusinessId($businessId);

        Response::success('Histórico de verificação.', [
            'business_id'   => $businessId,
            'business_name' => $business->trade_name ?? '',
            'logs'          => $logs,
            'total'         => count($logs),
        ]);
    }

    /**
     * Obtém e sanitiza o corpo JSON da requisição.
     *
     * @return array Dados sanitizados.
     */
    private function getJsonInput(): array
    {
        $rawBody = file_get_contents('php://input');

        if (empty($rawBody)) {
            return Middleware::sanitizeInput($_POST);
        }

        $data = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Corpo da requisição inválido. Envie um JSON válido.', 400);
        }

        return Middleware::sanitizeInput($data ?? []);
    }

    /**
     * Converte um objeto em array associativo.
     *
     * @param object $obj Objeto a converter.
     * @return array Array associativo com as propriedades do objeto.
     */
    private function objectToArray(object $obj): array
    {
        return json_decode(json_encode($obj), true) ?? (array) $obj;
    }
}
