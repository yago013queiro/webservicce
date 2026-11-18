<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Repositories\VerificationLogRepository;

/**
 * Serviço de gerenciamento de documentos.
 *
 * Gerencia upload, armazenamento, validação e consulta de documentos
 * enviados pelas empresas durante o processo de verificação.
 */
class DocumentService
{
    /**
     * Tamanho máximo permitido para upload em bytes (10 MB).
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Tipos MIME permitidos para upload de documentos.
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
    ];

    /**
     * Tipos de documentos aceitos com seus rótulos em português.
     */
    private const DOCUMENT_TYPES = [
        'contrato_social'       => 'Contrato Social / Requerimento de Empresário',
        'cartao_cnpj'           => 'Cartão CNPJ',
        'comprovante_endereco'  => 'Comprovante de Endereço',
        'documento_identidade'  => 'Documento de Identidade (RG/CNH)',
        'alvara_funcionamento'  => 'Alvará de Funcionamento',
        'inscricao_estadual'    => 'Inscrição Estadual',
    ];

    /**
     * Tipos de documentos obrigatórios para submissão.
     */
    private const REQUIRED_DOCUMENT_TYPES = [
        'contrato_social',
        'cartao_cnpj',
        'comprovante_endereco',
        'documento_identidade',
    ];

    /**
     * @param DocumentRepository        $documentRepo Repositório de documentos.
     * @param VerificationLogRepository  $logRepo      Repositório de logs de verificação.
     */
    public function __construct(
        private DocumentRepository $documentRepo,
        private VerificationLogRepository $logRepo,
    ) {
    }

    /**
     * Faz upload de um documento para uma empresa.
     *
     * Valida existência do arquivo, tamanho, tipo MIME e tipo do documento.
     * Armazena o arquivo em diretório privado e cria o registro no banco.
     *
     * @param int    $businessId   ID da empresa.
     * @param array  $fileData     Dados do arquivo ($_FILES-style: tmp_name, name, size, type, error).
     * @param string $documentType Tipo do documento (chave de DOCUMENT_TYPES).
     * @return array ['success' => true, 'document_id' => int] ou ['success' => false, 'errors' => string[]].
     */
    public function upload(int $businessId, array $fileData, string $documentType): array
    {
        $errors = [];

        // Validar presença do arquivo
        if (empty($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
            $errors[] = 'Nenhum arquivo foi enviado.';
        }

        if (isset($fileData['error']) && $fileData['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erro no envio do arquivo. Código: ' . $fileData['error'];
        }

        // Validar tipo de documento
        if (!array_key_exists($documentType, self::DOCUMENT_TYPES)) {
            $errors[] = 'Tipo de documento inválido.';
        }

        // Validar tamanho
        if (!empty($fileData['size']) && $fileData['size'] > self::MAX_FILE_SIZE) {
            $errors[] = 'O arquivo excede o tamanho máximo de 10 MB.';
        }

        // Validar MIME type
        if (!empty($fileData['tmp_name']) && file_exists($fileData['tmp_name'])) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileData['tmp_name']);

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
                $errors[] = 'Tipo de arquivo não permitido. Envie arquivos PDF, JPG ou PNG.';
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Gerar chave de armazenamento única
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $storageKey = uniqid('doc_', true) . '_' . hash('sha256', $fileData['name'] . microtime()) . '.' . $extension;

        // Criar diretório de armazenamento
        $storageDir = dirname(__DIR__) . '/storage/documents/' . $businessId;
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $destination = $storageDir . '/' . $storageKey;

        // Mover arquivo
        if (!move_uploaded_file($fileData['tmp_name'], $destination)) {
            return ['success' => false, 'errors' => ['Erro ao salvar o arquivo. Tente novamente.']];
        }

        // Detectar MIME type real do arquivo salvo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($destination);

        // Criar registro no banco
        $documentId = $this->documentRepo->create([
            'business_id'       => $businessId,
            'document_type'     => $documentType,
            'original_filename' => $fileData['name'],
            'storage_key'       => $storageKey,
            'mime_type'         => $mimeType,
            'file_size_bytes'   => filesize($destination),
            'status'            => 'pending',
        ]);

        return ['success' => true, 'document_id' => $documentId];
    }

    /**
     * Obtém o caminho completo do arquivo de um documento.
     *
     * @param int $documentId ID do documento.
     * @return string|null Caminho do arquivo ou null se não encontrado.
     */
    public function getDocumentPath(int $documentId): ?string
    {
        $document = $this->documentRepo->findById($documentId);

        if ($document === null) {
            return null;
        }

        $path = dirname(__DIR__) . '/storage/documents/' . $document->business_id . '/' . $document->storage_key;

        return file_exists($path) ? $path : null;
    }

    /**
     * Exclui um documento do banco de dados e do sistema de arquivos.
     *
     * @param int $documentId ID do documento.
     * @return bool True se o documento foi excluído com sucesso.
     */
    public function deleteDocument(int $documentId): bool
    {
        $document = $this->documentRepo->findById($documentId);

        if ($document === null) {
            return false;
        }

        // Remover arquivo físico
        $path = dirname(__DIR__) . '/storage/documents/' . $document->business_id . '/' . $document->storage_key;
        if (file_exists($path)) {
            unlink($path);
        }

        // Remover registro (usando updateStatus para soft-delete ou deletando diretamente)
        // Recriamos a exclusão via PDO pois o DocumentRepository não tem um deleteById
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare('DELETE FROM business_documents WHERE id = :id');
        $stmt->execute(['id' => $documentId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna a lista de tipos de documentos aceitos com rótulos em português.
     *
     * @return array Array associativo tipo => rótulo.
     */
    public function getRequiredDocumentTypes(): array
    {
        return self::DOCUMENT_TYPES;
    }

    /**
     * Verifica quais documentos obrigatórios ainda não foram enviados.
     *
     * @param int $businessId ID da empresa.
     * @return array Lista de tipos de documentos faltantes com rótulos.
     */
    public function getMissingDocuments(int $businessId): array
    {
        $uploaded = $this->documentRepo->findByBusinessId($businessId);

        $uploadedTypes = array_map(
            fn($doc) => $doc->document_type,
            $uploaded
        );

        $missing = [];
        foreach (self::REQUIRED_DOCUMENT_TYPES as $requiredType) {
            if (!in_array($requiredType, $uploadedTypes, true)) {
                $missing[] = [
                    'type'  => $requiredType,
                    'label' => self::DOCUMENT_TYPES[$requiredType],
                ];
            }
        }

        return $missing;
    }
}
