<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BusinessRepository;
use App\Repositories\AddressRepository;
use App\Repositories\EntrepreneurRepository;
use App\Repositories\BankAccountRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\VerificationLogRepository;
use App\Core\Database;

/**
 * Serviço de registro de empresas.
 *
 * Orquestra o fluxo de cadastro multi-etapas para registro de
 * empresas no marketplace, incluindo dados empresariais, endereço,
 * dados do empreendedor, documentos e conta bancária.
 */
class BusinessRegistrationService
{
    /**
     * @param BusinessRepository       $businessRepo     Repositório de empresas.
     * @param AddressRepository        $addressRepo      Repositório de endereços.
     * @param EntrepreneurRepository   $entrepreneurRepo Repositório de empreendedores.
     * @param BankAccountRepository    $bankAccountRepo  Repositório de contas bancárias.
     * @param DocumentRepository       $documentRepo     Repositório de documentos.
     * @param VerificationLogRepository $logRepo          Repositório de logs de verificação.
     */
    public function __construct(
        private BusinessRepository $businessRepo,
        private AddressRepository $addressRepo,
        private EntrepreneurRepository $entrepreneurRepo,
        private BankAccountRepository $bankAccountRepo,
        private DocumentRepository $documentRepo,
        private VerificationLogRepository $logRepo,
    ) {
    }

    /**
     * Inicia o registro de uma nova empresa em status DRAFT.
     *
     * Valida formato e unicidade do CNPJ antes de criar o registro.
     *
     * @param int   $userId       ID do usuário proprietário.
     * @param array $businessData Dados da empresa (trade_name, legal_name, cnpj, etc.).
     * @return array ['success' => true, 'business_id' => int] ou ['success' => false, 'errors' => string[]].
     */
    public function startRegistration(int $userId, array $businessData): array
    {
        $errors = [];

        // Validar campos obrigatórios
        if (empty($businessData['trade_name'])) {
            $errors[] = 'O nome fantasia é obrigatório.';
        }

        if (empty($businessData['legal_name'])) {
            $errors[] = 'A razão social é obrigatória.';
        }

        if (empty($businessData['cnpj'])) {
            $errors[] = 'O CNPJ é obrigatório.';
        } else {
            $cnpjDigits = preg_replace('/\D/', '', $businessData['cnpj']);

            if (strlen($cnpjDigits) !== 14) {
                $errors[] = 'O CNPJ deve conter exatamente 14 dígitos.';
            } elseif (!$this->validateCnpj($cnpjDigits)) {
                $errors[] = 'O CNPJ informado é inválido.';
            } elseif ($this->businessRepo->cnpjExists($cnpjDigits)) {
                $errors[] = 'Este CNPJ já está cadastrado no sistema.';
            }
        }

        // Verificar se usuário já tem empresa
        $existing = $this->businessRepo->findByOwnerId($userId);
        if ($existing !== null) {
            $errors[] = 'Você já possui uma empresa cadastrada.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $businessData['owner_user_id'] = $userId;
        $businessData['verification_status'] = 'draft';

        $businessId = $this->businessRepo->create($businessData);

        return ['success' => true, 'business_id' => $businessId];
    }

    /**
     * Salva ou atualiza o endereço de uma empresa.
     *
     * @param int   $businessId  ID da empresa.
     * @param array $addressData Dados do endereço (street, number, complement, neighborhood, city, state, zip_code).
     * @return array ['success' => true, 'address_id' => int] ou ['success' => false, 'errors' => string[]].
     */
    public function saveAddress(int $businessId, array $addressData): array
    {
        $errors = $this->validateAddress($addressData);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $existing = $this->addressRepo->findPrimaryByBusinessId($businessId);

        if ($existing !== null) {
            $this->addressRepo->update($existing->id, $addressData);
            return ['success' => true, 'address_id' => $existing->id];
        }

        $addressData['business_id'] = $businessId;
        $addressData['is_primary'] = 1;
        $addressId = $this->addressRepo->create($addressData);

        return ['success' => true, 'address_id' => $addressId];
    }

    /**
     * Salva ou atualiza os dados pessoais do empreendedor.
     *
     * Valida formato do CPF e unicidade entre empresas.
     *
     * @param int   $businessId   ID da empresa.
     * @param array $personalData Dados pessoais (full_name, cpf, rg, birth_date, mother_name, nationality).
     * @return array ['success' => true, 'entrepreneur_id' => int] ou ['success' => false, 'errors' => string[]].
     */
    public function saveEntrepreneurData(int $businessId, array $personalData): array
    {
        $errors = [];

        if (empty($personalData['full_name'])) {
            $errors[] = 'O nome completo do responsável é obrigatório.';
        }

        if (empty($personalData['cpf'])) {
            $errors[] = 'O CPF é obrigatório.';
        } else {
            $cpfDigits = preg_replace('/\D/', '', $personalData['cpf']);

            if (strlen($cpfDigits) !== 11) {
                $errors[] = 'O CPF deve conter exatamente 11 dígitos.';
            } elseif (!$this->validateCpf($cpfDigits)) {
                $errors[] = 'O CPF informado é inválido.';
            } elseif ($this->entrepreneurRepo->cpfExistsForOtherBusiness($cpfDigits, $businessId)) {
                $errors[] = 'Este CPF já está vinculado a outra empresa.';
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $existing = $this->entrepreneurRepo->findByBusinessId($businessId);

        if ($existing !== null) {
            $this->entrepreneurRepo->update($existing->id, $personalData);
            return ['success' => true, 'entrepreneur_id' => $existing->id];
        }

        $personalData['business_id'] = $businessId;
        $entrepreneurId = $this->entrepreneurRepo->create($personalData);

        return ['success' => true, 'entrepreneur_id' => $entrepreneurId];
    }

    /**
     * Salva ou atualiza os dados bancários de uma empresa.
     *
     * @param int   $businessId ID da empresa.
     * @param array $bankData   Dados bancários (bank_code, bank_name, agency, account_number, account_type, holder_name, holder_document).
     * @return array ['success' => true, 'bank_account_id' => int] ou ['success' => false, 'errors' => string[]].
     */
    public function saveBankAccount(int $businessId, array $bankData): array
    {
        $errors = $this->validateBankAccount($bankData);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $existing = $this->bankAccountRepo->findByBusinessId($businessId);

        if ($existing !== null) {
            $this->bankAccountRepo->update($existing->id, $bankData);
            return ['success' => true, 'bank_account_id' => $existing->id];
        }

        $bankData['business_id'] = $businessId;
        $bankAccountId = $this->bankAccountRepo->create($bankData);

        return ['success' => true, 'bank_account_id' => $bankAccountId];
    }

    /**
     * Submete a empresa para revisão administrativa.
     *
     * Transiciona o status de DRAFT para PENDING_REVIEW, validando
     * que todas as etapas obrigatórias estejam completas.
     *
     * @param int $businessId ID da empresa.
     * @param int $userId     ID do usuário que está submetendo.
     * @return array ['success' => true] ou ['success' => false, 'errors' => string[]].
     */
    public function submitForReview(int $businessId, int $userId): array
    {
        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            return ['success' => false, 'errors' => ['Empresa não encontrada.']];
        }

        if ($business->verification_status !== 'draft' && $business->verification_status !== 'documents_requested') {
            return ['success' => false, 'errors' => ['A empresa não pode ser submetida para revisão no status atual.']];
        }

        $readiness = $this->canSubmitForReview($businessId);

        if (!$readiness['ready']) {
            $missingLabels = array_map(fn(string $step) => $this->getStepLabel($step), $readiness['missing']);
            return [
                'success' => false,
                'errors'  => ['As seguintes etapas precisam ser concluídas: ' . implode(', ', $missingLabels) . '.'],
            ];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $previousStatus = $business->verification_status;
            $this->businessRepo->updateStatus($businessId, 'pending_review');

            $this->logRepo->create([
                'business_id'           => $businessId,
                'administrator_user_id' => null,
                'action_type'           => 'submit_for_review',
                'previous_status'       => $previousStatus,
                'new_status'            => 'pending_review',
                'reason'                => 'Cadastro submetido para revisão pelo usuário.',
            ]);

            $db->commit();

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->rollBack();
            return ['success' => false, 'errors' => ['Erro ao submeter para revisão. Tente novamente.']];
        }
    }

    /**
     * Retorna o progresso de preenchimento de cada etapa do cadastro.
     *
     * @param int $businessId ID da empresa.
     * @return array Array com status de cada etapa (business_info, address, entrepreneur, documents, bank_account => bool).
     */
    public function getRegistrationProgress(int $businessId): array
    {
        $business = $this->businessRepo->findById($businessId);
        $address = $this->addressRepo->findPrimaryByBusinessId($businessId);
        $entrepreneur = $this->entrepreneurRepo->findByBusinessId($businessId);
        $documents = $this->documentRepo->findByBusinessId($businessId);
        $bankAccount = $this->bankAccountRepo->findByBusinessId($businessId);

        return [
            'business_info' => $business !== null,
            'address'       => $address !== null,
            'entrepreneur'  => $entrepreneur !== null,
            'documents'     => !empty($documents),
            'bank_account'  => $bankAccount !== null,
        ];
    }

    /**
     * Verifica se todas as etapas obrigatórias estão concluídas para submissão.
     *
     * @param int $businessId ID da empresa.
     * @return array ['ready' => bool, 'missing' => string[]] Lista de etapas faltantes.
     */
    public function canSubmitForReview(int $businessId): array
    {
        $progress = $this->getRegistrationProgress($businessId);

        $requiredSteps = ['business_info', 'address', 'entrepreneur', 'documents'];
        $missing = [];

        foreach ($requiredSteps as $step) {
            if (!($progress[$step] ?? false)) {
                $missing[] = $step;
            }
        }

        return [
            'ready'   => empty($missing),
            'missing' => $missing,
        ];
    }

    /**
     * Valida os dados de endereço.
     *
     * @param array $data Dados do endereço.
     * @return string[] Lista de mensagens de erro.
     */
    private function validateAddress(array $data): array
    {
        $errors = [];

        if (empty($data['street'])) {
            $errors[] = 'O logradouro é obrigatório.';
        }

        if (empty($data['number'])) {
            $errors[] = 'O número é obrigatório.';
        }

        if (empty($data['neighborhood'])) {
            $errors[] = 'O bairro é obrigatório.';
        }

        if (empty($data['city'])) {
            $errors[] = 'A cidade é obrigatória.';
        }

        if (empty($data['state'])) {
            $errors[] = 'O estado é obrigatório.';
        } elseif (mb_strlen($data['state']) !== 2) {
            $errors[] = 'O estado deve ser informado com a sigla de 2 letras (ex.: SP, RJ).';
        }

        if (empty($data['zip_code'])) {
            $errors[] = 'O CEP é obrigatório.';
        } else {
            $cepDigits = preg_replace('/\D/', '', $data['zip_code']);
            if (strlen($cepDigits) !== 8) {
                $errors[] = 'O CEP deve conter exatamente 8 dígitos.';
            }
        }

        return $errors;
    }

    /**
     * Valida os dados de conta bancária.
     *
     * @param array $data Dados bancários.
     * @return string[] Lista de mensagens de erro.
     */
    private function validateBankAccount(array $data): array
    {
        $errors = [];

        if (empty($data['bank_code'])) {
            $errors[] = 'O código do banco é obrigatório.';
        }

        if (empty($data['bank_name'])) {
            $errors[] = 'O nome do banco é obrigatório.';
        }

        if (empty($data['agency'])) {
            $errors[] = 'A agência é obrigatória.';
        }

        if (empty($data['account_number'])) {
            $errors[] = 'O número da conta é obrigatório.';
        }

        if (empty($data['holder_name'])) {
            $errors[] = 'O nome do titular é obrigatório.';
        }

        if (empty($data['holder_document'])) {
            $errors[] = 'O CPF/CNPJ do titular é obrigatório.';
        }

        $validTypes = ['corrente', 'poupanca', 'pagamento'];
        if (!empty($data['account_type']) && !in_array($data['account_type'], $validTypes, true)) {
            $errors[] = 'O tipo de conta informado é inválido. Use: corrente, poupança ou pagamento.';
        }

        return $errors;
    }

    /**
     * Valida o formato de um CNPJ (algoritmo de dígitos verificadores).
     *
     * @param string $cnpj CNPJ com apenas dígitos.
     * @return bool True se o CNPJ é válido.
     */
    private function validateCnpj(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Rejeita CNPJs com todos os dígitos iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Valida primeiro dígito verificador
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $digit1) {
            return false;
        }

        // Valida segundo dígito verificador
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $digit2;
    }

    /**
     * Valida o formato de um CPF (algoritmo de dígitos verificadores).
     *
     * @param string $cpf CPF com apenas dígitos.
     * @return bool True se o CPF é válido.
     */
    private function validateCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita CPFs com todos os dígitos iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Valida primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int) $cpf[9] !== $digit1) {
            return false;
        }

        // Valida segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return (int) $cpf[10] === $digit2;
    }

    /**
     * Retorna o rótulo em português de cada etapa do cadastro.
     *
     * @param string $step Identificador da etapa.
     * @return string Rótulo em português.
     */
    private function getStepLabel(string $step): string
    {
        return match ($step) {
            'business_info' => 'Dados da Empresa',
            'address'       => 'Endereço',
            'entrepreneur'  => 'Dados do Responsável',
            'documents'     => 'Documentos',
            'bank_account'  => 'Conta Bancária',
            default         => $step,
        };
    }
}
