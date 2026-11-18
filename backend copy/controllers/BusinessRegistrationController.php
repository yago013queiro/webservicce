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

/**
 * Controlador de registro de empresa (fluxo multi-etapa).
 *
 * Gerencia o cadastro progressivo de uma empresa no marketplace,
 * incluindo dados corporativos, endereço, empreendedor, conta bancária,
 * documentos e submissão para revisão.
 *
 * Requer usuário autenticado com papel 'business_owner'.
 */
class BusinessRegistrationController
{
    private BusinessRepository $businessRepo;
    private AddressRepository $addressRepo;
    private EntrepreneurRepository $entrepreneurRepo;
    private BankAccountRepository $bankAccountRepo;
    private DocumentRepository $documentRepo;

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
    }

    /**
     * Retorna o progresso do registro (quais etapas estão completas).
     *
     * Handles GET /api/business/progress.
     *
     * @return void
     */
    public function getProgress(): void
    {
        $business = $this->requireBusinessOwner();

        $businessId = (int) $business->id;

        $address = $this->addressRepo->findPrimaryByBusinessId($businessId);
        $entrepreneur = $this->entrepreneurRepo->findByBusinessId($businessId);
        $bankAccount = $this->bankAccountRepo->findByBusinessId($businessId);
        $documents = $this->documentRepo->findByBusinessId($businessId);

        // Verifica se os dados básicos da empresa estão preenchidos
        $hasBusinessInfo = !empty($business->trade_name)
            && !empty($business->legal_name)
            && !empty($business->cnpj)
            && $business->cnpj !== '00000000000000';

        $steps = [
            'business_info'  => $hasBusinessInfo,
            'address'        => $address !== null,
            'entrepreneur'   => $entrepreneur !== null,
            'bank_account'   => $bankAccount !== null,
            'documents'      => count($documents) > 0,
        ];

        $completedSteps = count(array_filter($steps));
        $totalSteps = count($steps);

        Response::success('Progresso do cadastro.', [
            'steps'           => $steps,
            'completed_steps' => $completedSteps,
            'total_steps'     => $totalSteps,
            'all_complete'    => $completedSteps === $totalSteps,
            'status'          => $business->verification_status ?? 'draft',
        ]);
    }

    /**
     * Salva os dados corporativos da empresa.
     *
     * Handles POST /api/business/info.
     *
     * @return void
     */
    public function saveBusinessInfo(): void
    {
        $business = $this->requireBusinessOwner();
        $input = $this->getJsonInput();
        $errors = [];

        // Validação dos campos obrigatórios
        if (empty($input['trade_name'])) {
            $errors[] = 'O campo "Nome fantasia" é obrigatório.';
        }

        if (empty($input['legal_name'])) {
            $errors[] = 'O campo "Razão social" é obrigatório.';
        }

        if (empty($input['cnpj'])) {
            $errors[] = 'O campo "CNPJ" é obrigatório.';
        } elseif (!$this->validateCnpj($input['cnpj'])) {
            $errors[] = 'O CNPJ informado é inválido.';
        }

        if (!empty($errors)) {
            Response::error('Erro de validação.', 422, $errors);
        }

        $businessId = (int) $business->id;

        // Verifica se o CNPJ pertence a outra empresa
        $cnpjSanitized = preg_replace('/\D/', '', $input['cnpj']);
        $existingByCnpj = $this->businessRepo->findByCnpj($cnpjSanitized);
        if ($existingByCnpj !== null && (int) $existingByCnpj->id !== $businessId) {
            Response::error('Este CNPJ já está cadastrado para outra empresa.', 409);
        }

        $this->businessRepo->update($businessId, [
            'trade_name'        => $input['trade_name'],
            'legal_name'        => $input['legal_name'],
            'cnpj'              => $input['cnpj'],
            'business_type'     => $input['legal_entity_type'] ?? null,
            'business_category' => $input['business_category'] ?? null,
            'description'       => $input['company_size'] ?? null,
        ]);

        // Campos extras que podem não estar no ALLOWED_UPDATE_FIELDS do repositório
        // são armazenados via update direto se necessário
        $db = Database::getInstance();
        $extraFields = [];
        $extraParams = ['id' => $businessId];

        if (isset($input['state_registration'])) {
            $extraFields[] = 'state_registration = :state_registration';
            $extraParams['state_registration'] = $input['state_registration'];
        }
        if (isset($input['municipal_registration'])) {
            $extraFields[] = 'municipal_registration = :municipal_registration';
            $extraParams['municipal_registration'] = $input['municipal_registration'];
        }
        if (isset($input['primary_cnae'])) {
            $extraFields[] = 'primary_cnae = :primary_cnae';
            $extraParams['primary_cnae'] = $input['primary_cnae'];
        }
        if (isset($input['company_size'])) {
            $extraFields[] = 'company_size = :company_size';
            $extraParams['company_size'] = $input['company_size'];
        }

        if (!empty($extraFields)) {
            $extraFields[] = 'updated_at = NOW()';
            $sql = 'UPDATE businesses SET ' . implode(', ', $extraFields) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($extraParams);
        }

        Response::success('Dados da empresa salvos com sucesso.');
    }

    /**
     * Salva o endereço da empresa.
     *
     * Handles POST /api/business/address.
     *
     * @return void
     */
    public function saveAddress(): void
    {
        $business = $this->requireBusinessOwner();
        $input = $this->getJsonInput();
        $errors = [];

        $requiredFields = [
            'postal_code'   => 'CEP',
            'street'        => 'Logradouro',
            'street_number' => 'Número',
            'neighborhood'  => 'Bairro',
            'city'          => 'Cidade',
            'state'         => 'Estado',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($input[$field])) {
                $errors[] = "O campo \"{$label}\" é obrigatório.";
            }
        }

        if (!empty($input['state']) && strlen($input['state']) !== 2) {
            $errors[] = 'O campo "Estado" deve conter a sigla com 2 caracteres (ex.: SP).';
        }

        if (!empty($errors)) {
            Response::error('Erro de validação.', 422, $errors);
        }

        $businessId = (int) $business->id;

        // Verifica se já existe um endereço principal
        $existingAddress = $this->addressRepo->findPrimaryByBusinessId($businessId);

        $addressData = [
            'business_id'  => $businessId,
            'street'       => $input['street'],
            'number'       => $input['street_number'],
            'complement'   => $input['address_complement'] ?? null,
            'neighborhood' => $input['neighborhood'],
            'city'         => $input['city'],
            'state'        => strtoupper($input['state']),
            'zip_code'     => $input['postal_code'],
            'is_primary'   => 1,
        ];

        if ($existingAddress !== null) {
            $this->addressRepo->update((int) $existingAddress->id, $addressData);
        } else {
            $this->addressRepo->create($addressData);
        }

        Response::success('Endereço salvo com sucesso.');
    }

    /**
     * Salva os dados pessoais do empreendedor/representante legal.
     *
     * Handles POST /api/business/entrepreneur.
     *
     * @return void
     */
    public function saveEntrepreneur(): void
    {
        $business = $this->requireBusinessOwner();
        $input = $this->getJsonInput();
        $errors = [];

        if (empty($input['full_name'])) {
            $errors[] = 'O campo "Nome completo" é obrigatório.';
        }

        if (empty($input['cpf'])) {
            $errors[] = 'O campo "CPF" é obrigatório.';
        } elseif (!$this->validateCpf($input['cpf'])) {
            $errors[] = 'O CPF informado é inválido.';
        }

        if (empty($input['date_of_birth'])) {
            $errors[] = 'O campo "Data de nascimento" é obrigatório.';
        }

        if (!empty($errors)) {
            Response::error('Erro de validação.', 422, $errors);
        }

        $businessId = (int) $business->id;

        // Verifica se o CPF pertence a outra empresa
        $cpfSanitized = preg_replace('/\D/', '', $input['cpf']);
        if ($this->entrepreneurRepo->cpfExistsForOtherBusiness($cpfSanitized, $businessId)) {
            Response::error('Este CPF já está cadastrado para outra empresa.', 409);
        }

        // Monta os dados do empreendedor
        $entrepreneurData = [
            'business_id' => $businessId,
            'full_name'   => $input['full_name'],
            'cpf'         => $input['cpf'],
            'rg'          => $this->buildRgString($input),
            'birth_date'  => $input['date_of_birth'],
            'mother_name' => $input['representative_role'] ?? null,
        ];

        // Verifica se já existe registro
        $existing = $this->entrepreneurRepo->findByBusinessId($businessId);

        if ($existing !== null) {
            $this->entrepreneurRepo->update((int) $existing->id, $entrepreneurData);
        } else {
            $this->entrepreneurRepo->create($entrepreneurData);
        }

        Response::success('Dados do empreendedor salvos com sucesso.');
    }

    /**
     * Salva os dados da conta bancária da empresa.
     *
     * Handles POST /api/business/bank-account.
     *
     * @return void
     */
    public function saveBankAccount(): void
    {
        $business = $this->requireBusinessOwner();
        $input = $this->getJsonInput();
        $errors = [];

        $requiredFields = [
            'bank_code'           => 'Código do banco',
            'bank_name'           => 'Nome do banco',
            'branch_number'       => 'Número da agência',
            'account_number'      => 'Número da conta',
            'account_type'        => 'Tipo de conta',
            'account_holder_name' => 'Nome do titular',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($input[$field])) {
                $errors[] = "O campo \"{$label}\" é obrigatório.";
            }
        }

        // O titular deve ter CPF ou CNPJ
        if (empty($input['account_holder_cpf']) && empty($input['account_holder_cnpj'])) {
            $errors[] = 'Informe o CPF ou CNPJ do titular da conta.';
        }

        $allowedAccountTypes = ['corrente', 'poupanca'];
        if (!empty($input['account_type']) && !in_array($input['account_type'], $allowedAccountTypes, true)) {
            $errors[] = 'Tipo de conta inválido. Use "corrente" ou "poupanca".';
        }

        if (!empty($errors)) {
            Response::error('Erro de validação.', 422, $errors);
        }

        $businessId = (int) $business->id;

        // Monta a agência com dígito se informado
        $agency = $input['branch_number'];
        if (!empty($input['branch_digit'])) {
            $agency .= '-' . $input['branch_digit'];
        }

        // Monta o número da conta com dígito
        $accountNumber = $input['account_number'];
        if (!empty($input['account_digit'])) {
            $accountNumber .= '-' . $input['account_digit'];
        }

        // Determina o documento do titular
        $holderDocument = !empty($input['account_holder_cpf'])
            ? $input['account_holder_cpf']
            : ($input['account_holder_cnpj'] ?? '');

        $bankData = [
            'business_id'    => $businessId,
            'bank_code'      => $input['bank_code'],
            'bank_name'      => $input['bank_name'],
            'agency'         => $agency,
            'account_number' => $accountNumber,
            'account_type'   => $input['account_type'],
            'holder_name'    => $input['account_holder_name'],
            'holder_document' => $holderDocument,
        ];

        // Verifica se já existe conta bancária
        $existing = $this->bankAccountRepo->findByBusinessId($businessId);

        if ($existing !== null) {
            $this->bankAccountRepo->update((int) $existing->id, $bankData);
        } else {
            $this->bankAccountRepo->create($bankData);
        }

        Response::success('Dados bancários salvos com sucesso.');
    }

    /**
     * Faz upload de um documento da empresa.
     *
     * Handles POST /api/business/document/upload (multipart/form-data).
     *
     * @return void
     */
    public function uploadDocument(): void
    {
        $business = $this->requireBusinessOwner();
        $businessId = (int) $business->id;

        // Valida se o arquivo foi enviado
        if (empty($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'O arquivo excede o tamanho máximo permitido pelo servidor.',
                UPLOAD_ERR_FORM_SIZE  => 'O arquivo excede o tamanho máximo permitido pelo formulário.',
                UPLOAD_ERR_PARTIAL    => 'O upload do arquivo foi feito parcialmente.',
                UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente no servidor.',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar o arquivo no disco.',
                UPLOAD_ERR_EXTENSION  => 'Upload interrompido por extensão PHP.',
            ];

            $errorCode = $_FILES['document']['error'] ?? UPLOAD_ERR_NO_FILE;
            $errorMessage = $uploadErrors[$errorCode] ?? 'Erro desconhecido no upload.';

            Response::error($errorMessage, 400);
        }

        $file = $_FILES['document'];
        $documentType = $_POST['document_type'] ?? '';

        if (empty($documentType)) {
            Response::error('O campo "Tipo de documento" é obrigatório.', 422);
        }

        // Tipos MIME permitidos
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedMimes, true)) {
            Response::error('Tipo de arquivo não permitido. Envie PDF, JPEG, PNG ou WebP.', 422);
        }

        // Limite de 10 MB
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            Response::error('O arquivo não pode exceder 10 MB.', 422);
        }

        // Gera nome único para armazenamento
        $extension = match ($mimeType) {
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            default           => 'bin',
        };

        $storageKey = sprintf(
            'business_%d/%s_%s.%s',
            $businessId,
            $documentType,
            bin2hex(random_bytes(8)),
            $extension
        );

        // Define o diretório de upload
        $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'documents';

        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storageKey);
        $dirPath = dirname($fullPath);

        if (!is_dir($dirPath) && !mkdir($dirPath, 0755, true)) {
            Response::error('Erro ao criar diretório de armazenamento.', 500);
        }

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            Response::error('Erro ao salvar o arquivo. Tente novamente.', 500);
        }

        // Registra o documento no banco
        $documentId = $this->documentRepo->create([
            'business_id'       => $businessId,
            'document_type'     => $documentType,
            'original_filename' => $file['name'],
            'storage_key'       => $storageKey,
            'mime_type'         => $mimeType,
            'file_size_bytes'   => $file['size'],
            'status'            => 'pending',
        ]);

        Response::success('Documento enviado com sucesso.', [
            'document_id' => $documentId,
            'storage_key' => $storageKey,
        ], 201);
    }

    /**
     * Remove um documento enviado (somente se status for pending).
     *
     * Handles DELETE /api/business/document/delete.
     *
     * @return void
     */
    public function deleteDocument(): void
    {
        $business = $this->requireBusinessOwner();
        $businessId = (int) $business->id;

        $input = $this->getJsonInput();
        $documentId = (int) ($input['document_id'] ?? $_GET['document_id'] ?? 0);

        if ($documentId <= 0) {
            Response::error('ID do documento é obrigatório.', 422);
        }

        $document = $this->documentRepo->findById($documentId);

        if ($document === null) {
            Response::notFound('Documento não encontrado.');
        }

        if ((int) $document->business_id !== $businessId) {
            Response::forbidden('Você não tem permissão para excluir este documento.');
        }

        if (($document->status ?? 'pending') !== 'pending') {
            Response::error('Somente documentos com status "pendente" podem ser excluídos.', 422);
        }

        // Remove o arquivo físico
        $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'documents';
        $filePath = $uploadDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $document->storage_key);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove do banco via query direta (o repositório só tem deleteByBusinessId)
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM business_documents WHERE id = :id AND business_id = :business_id');
        $stmt->execute(['id' => $documentId, 'business_id' => $businessId]);

        Response::success('Documento excluído com sucesso.');
    }

    /**
     * Submete o cadastro para revisão administrativa.
     *
     * Handles POST /api/business/submit-review.
     * Valida se todas as etapas estão completas antes de submeter.
     *
     * @return void
     */
    public function submitForReview(): void
    {
        $business = $this->requireBusinessOwner();
        $businessId = (int) $business->id;

        // Verifica se o status permite submissão
        $allowedStatuses = ['draft', 'corrections_requested'];
        $currentStatus = $business->verification_status ?? 'draft';

        if (!in_array($currentStatus, $allowedStatuses, true)) {
            Response::error('O cadastro não pode ser submetido no status atual.', 422);
        }

        // Verifica se todas as etapas estão completas
        $address = $this->addressRepo->findPrimaryByBusinessId($businessId);
        $entrepreneur = $this->entrepreneurRepo->findByBusinessId($businessId);
        $bankAccount = $this->bankAccountRepo->findByBusinessId($businessId);
        $documents = $this->documentRepo->findByBusinessId($businessId);

        $hasBusinessInfo = !empty($business->trade_name)
            && !empty($business->legal_name)
            && !empty($business->cnpj)
            && $business->cnpj !== '00000000000000';

        $incompleteSteps = [];

        if (!$hasBusinessInfo) {
            $incompleteSteps[] = 'Dados da empresa';
        }
        if ($address === null) {
            $incompleteSteps[] = 'Endereço';
        }
        if ($entrepreneur === null) {
            $incompleteSteps[] = 'Dados do empreendedor';
        }
        if ($bankAccount === null) {
            $incompleteSteps[] = 'Conta bancária';
        }
        if (empty($documents)) {
            $incompleteSteps[] = 'Documentos';
        }

        if (!empty($incompleteSteps)) {
            Response::error(
                'Preencha todas as etapas antes de enviar para revisão.',
                422,
                ['incomplete_steps' => $incompleteSteps]
            );
        }

        // Transiciona para PENDING_REVIEW
        $this->businessRepo->updateStatus($businessId, 'pending_review');

        Response::success('Cadastro enviado para revisão com sucesso! Você será notificado sobre o resultado.');
    }

    /**
     * Retorna o perfil completo do registro da empresa do usuário.
     *
     * Handles GET /api/business/profile.
     *
     * @return void
     */
    public function getBusinessProfile(): void
    {
        $business = $this->requireBusinessOwner();
        $businessId = (int) $business->id;

        $addresses = $this->addressRepo->findByBusinessId($businessId);
        $entrepreneur = $this->entrepreneurRepo->findByBusinessId($businessId);
        $bankAccount = $this->bankAccountRepo->findByBusinessId($businessId);
        $documents = $this->documentRepo->findByBusinessId($businessId);

        // Converte objetos para arrays
        $addressesArray = array_map(fn($a) => $this->objectToArray($a), $addresses);
        $documentsArray = array_map(fn($d) => $this->objectToArray($d), $documents);

        Response::success('Perfil da empresa.', [
            'business'     => $this->objectToArray($business),
            'addresses'    => $addressesArray,
            'entrepreneur' => $entrepreneur !== null ? $this->objectToArray($entrepreneur) : null,
            'bank_account' => $bankAccount !== null ? $this->objectToArray($bankAccount) : null,
            'documents'    => $documentsArray,
        ]);
    }

    /**
     * Exige que o usuário seja business_owner autenticado e retorna sua empresa.
     *
     * @return object Objeto Business do usuário autenticado.
     */
    private function requireBusinessOwner(): object
    {
        Session::requireAuth();

        $user = Session::getUser();

        if (($user['user_role'] ?? '') !== 'business_owner') {
            Response::forbidden('Acesso restrito a proprietários de empresa.');
        }

        $userId = Session::getUserId();
        $business = $this->businessRepo->findByOwnerId($userId);

        if ($business === null) {
            Response::error('Nenhum cadastro de empresa encontrado. Registre-se como proprietário.', 404);
        }

        return $business;
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
     * Valida um CNPJ usando o algoritmo oficial.
     *
     * @param string $cnpj CNPJ a validar (com ou sem formatação).
     * @return bool True se o CNPJ é válido.
     */
    private function validateCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Rejeita CNPJs com todos os dígitos iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $digit1) {
            return false;
        }

        // Calcula segundo dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $digit2;
    }

    /**
     * Valida um CPF usando o algoritmo oficial.
     *
     * @param string $cpf CPF a validar (com ou sem formatação).
     * @return bool True se o CPF é válido.
     */
    private function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita CPFs com todos os dígitos iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int) $cpf[9] !== $digit1) {
            return false;
        }

        // Calcula segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return (int) $cpf[10] === $digit2;
    }

    /**
     * Monta a string RG a partir dos campos de entrada.
     *
     * @param array $input Dados de entrada contendo rg_number, rg_issuing_authority, rg_issuing_state.
     * @return string|null String RG formatada ou null se não informado.
     */
    private function buildRgString(array $input): ?string
    {
        if (empty($input['rg_number'])) {
            return null;
        }

        $rg = $input['rg_number'];

        if (!empty($input['rg_issuing_authority'])) {
            $rg .= ' - ' . $input['rg_issuing_authority'];
        }

        if (!empty($input['rg_issuing_state'])) {
            $rg .= '/' . strtoupper($input['rg_issuing_state']);
        }

        return $rg;
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
