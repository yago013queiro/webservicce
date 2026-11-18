-- ==========================================================================
-- CONECTA WEB SERVICE & MARKETPLACE
-- Schema de Banco de Dados para Verificação e Cadastro de Empresas
-- MySQL 8.0+ | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Idioma: Português do Brasil (pt-BR)
-- ==========================================================================

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS `marketplace_business_platform`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `marketplace_business_platform`;

-- ==========================================================================
-- TABELA: users
-- Armazena credenciais de autenticação e dados da conta de todos os usuários
-- Papéis: customer, business_owner, delivery_driver, administrator
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`           VARCHAR(255) NOT NULL,
  `email`               VARCHAR(255) NOT NULL,
  `password_hash`       VARCHAR(255) NOT NULL COMMENT 'Hash Argon2id via password_hash()',
  `phone`               VARCHAR(20) DEFAULT NULL COMMENT 'Formato: +55 (11) 98765-4321',
  `user_role`           ENUM('customer', 'business_owner', 'delivery_driver', 'administrator')
                          NOT NULL DEFAULT 'customer',
  `account_status`      ENUM('active', 'inactive', 'suspended', 'pending_verification')
                          NOT NULL DEFAULT 'pending_verification',
  `email_verified_at`   DATETIME DEFAULT NULL,
  `phone_verified_at`   DATETIME DEFAULT NULL,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  INDEX `idx_users_role` (`user_role`),
  INDEX `idx_users_status` (`account_status`),
  INDEX `idx_users_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- TABELA: businesses
-- Dados cadastrais da empresa parceira (modelo de negócio do Canvas)
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `businesses` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_user_id`         BIGINT UNSIGNED NOT NULL,

  -- Identificação da Empresa
  `legal_name`            VARCHAR(255) NOT NULL COMMENT 'Razão Social',
  `trade_name`            VARCHAR(255) NOT NULL COMMENT 'Nome Fantasia',
  `cnpj`                  CHAR(14) NOT NULL COMMENT 'CNPJ somente dígitos, preserva zeros à esquerda',

  -- Classificação Empresarial
  `company_size`          ENUM('MEI', 'ME', 'EPP', 'MEDIO', 'GRANDE')
                            NOT NULL DEFAULT 'MEI'
                            COMMENT 'Porte: MEI, ME, EPP, Médio, Grande',
  `legal_entity_type`     ENUM('EI', 'EIRELI', 'LTDA', 'SLU', 'SA', 'MEI')
                            NOT NULL DEFAULT 'MEI'
                            COMMENT 'Natureza Jurídica: EI, EIRELI, LTDA, SLU, SA, MEI',
  `business_category`     ENUM('alimentacao', 'supermercados', 'farmacias', 'roupas',
                                'eletronicos', 'beleza', 'casa', 'outros')
                            NOT NULL DEFAULT 'outros'
                            COMMENT 'Categoria principal no marketplace',

  -- Atividade Econômica (CNAE)
  `primary_cnae`          VARCHAR(10) DEFAULT NULL COMMENT 'CNAE principal (ex: 5611-2/01)',
  `secondary_cnaes`       JSON DEFAULT NULL COMMENT 'Lista de CNAEs secundários',

  -- Inscrições Oficiais
  `state_registration`    VARCHAR(30) DEFAULT NULL COMMENT 'Inscrição Estadual (quando aplicável)',
  `municipal_registration` VARCHAR(30) DEFAULT NULL COMMENT 'Inscrição Municipal (quando aplicável)',

  -- Status de Verificação do CNPJ
  `cnpj_status`           ENUM('pending', 'verified', 'invalid')
                            NOT NULL DEFAULT 'pending'
                            COMMENT 'Status da verificação do CNPJ junto à Receita Federal',
  `cnpj_verified_at`      DATETIME DEFAULT NULL,

  -- Status do Cadastro de Parceria
  `registration_status`   ENUM('draft', 'pending_review', 'under_analysis',
                                'documents_requested', 'approved', 'rejected', 'suspended')
                            NOT NULL DEFAULT 'draft',
  `verification_notes`    TEXT DEFAULT NULL COMMENT 'Observações dos revisores / administradores',

  -- Datas do Fluxo de Aprovação
  `submitted_at`          DATETIME DEFAULT NULL COMMENT 'Data de submissão para análise',
  `approved_at`           DATETIME DEFAULT NULL,
  `rejected_at`           DATETIME DEFAULT NULL,

  `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_businesses_cnpj` (`cnpj`),
  INDEX `idx_businesses_owner` (`owner_user_id`),
  INDEX `idx_businesses_status` (`registration_status`),
  INDEX `idx_businesses_category` (`business_category`),
  INDEX `idx_businesses_cnpj_status` (`cnpj_status`),
  INDEX `idx_businesses_submitted` (`submitted_at`),

  CONSTRAINT `fk_businesses_owner`
    FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- TABELA: business_legal_documents
-- Documentos legais enviados para verificação da empresa
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `business_legal_documents` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id`         BIGINT UNSIGNED NOT NULL,

  `document_type`       ENUM('cnpj_card', 'alvara', 'identity_doc',
                              'social_contract', 'address_proof', 'other')
                          NOT NULL
                          COMMENT 'Tipo: Cartão CNPJ, Alvará, Documento de Identidade, Contrato Social, Comprovante de Endereço, Outro',

  -- Informações do Arquivo
  `original_filename`   VARCHAR(255) NOT NULL,
  `storage_key`         VARCHAR(500) NOT NULL COMMENT 'Caminho relativo no diretório privado de armazenamento',
  `mime_type`           VARCHAR(100) NOT NULL,
  `file_size`           BIGINT UNSIGNED NOT NULL COMMENT 'Tamanho em bytes',

  -- Status da Revisão
  `document_status`     ENUM('pending', 'approved', 'rejected')
                          NOT NULL DEFAULT 'pending',
  `issued_at`           DATE DEFAULT NULL COMMENT 'Data de emissão do documento',
  `expires_at`          DATE DEFAULT NULL COMMENT 'Data de validade (quando aplicável)',

  -- Revisão Administrativa
  `reviewed_by`         BIGINT UNSIGNED DEFAULT NULL,
  `reviewed_at`         DATETIME DEFAULT NULL,
  `rejection_reason`    TEXT DEFAULT NULL COMMENT 'Motivo da rejeição (obrigatório ao rejeitar)',

  `uploaded_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_documents_business` (`business_id`),
  INDEX `idx_documents_type` (`document_type`),
  INDEX `idx_documents_status` (`document_status`),

  CONSTRAINT `fk_documents_business`
    FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_documents_reviewer`
    FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- TABELA: business_addresses
-- Endereço comercial do estabelecimento parceiro
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `business_addresses` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id`         BIGINT UNSIGNED NOT NULL,

  `address_type`        ENUM('commercial', 'warehouse', 'billing')
                          NOT NULL DEFAULT 'commercial',

  -- Campos de Endereço no Padrão Brasileiro
  `postal_code`         CHAR(8) NOT NULL COMMENT 'CEP somente dígitos (8 caracteres)',
  `street`              VARCHAR(255) NOT NULL COMMENT 'Logradouro / Rua / Avenida',
  `street_number`       VARCHAR(20) NOT NULL COMMENT 'Número',
  `address_complement`  VARCHAR(100) DEFAULT NULL COMMENT 'Complemento (Sala, Loja, Andar)',
  `neighborhood`        VARCHAR(100) NOT NULL COMMENT 'Bairro',
  `city`                VARCHAR(100) NOT NULL COMMENT 'Cidade / Município',
  `state`               CHAR(2) NOT NULL COMMENT 'UF do estado brasileiro (SP, RJ, MG, etc.)',
  `country`             CHAR(2) NOT NULL DEFAULT 'BR' COMMENT 'Código do país (ISO 3166-1 alpha-2)',

  `is_primary`          TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Endereço principal da empresa',

  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_addresses_business` (`business_id`),
  INDEX `idx_addresses_primary` (`business_id`, `is_primary`),

  CONSTRAINT `fk_addresses_business`
    FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- TABELA: entrepreneur_personal_data
-- Dados pessoais do empreendedor ou representante legal autorizado
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `entrepreneur_personal_data` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id`           BIGINT UNSIGNED NOT NULL,

  `full_name`             VARCHAR(255) NOT NULL COMMENT 'Nome completo conforme documento oficial',
  `cpf`                   CHAR(11) NOT NULL COMMENT 'CPF somente dígitos, preserva zeros à esquerda',
  `rg_number`             VARCHAR(20) NOT NULL COMMENT 'Número do RG',
  `rg_issuing_authority`  VARCHAR(20) NOT NULL COMMENT 'Órgão emissor (SSP, DETRAN, IFP, etc.)',
  `rg_issuing_state`      CHAR(2) NOT NULL COMMENT 'UF do órgão emissor',
  `date_of_birth`         DATE DEFAULT NULL COMMENT 'Data de nascimento (quando necessário)',

  `representative_role`   ENUM('owner', 'legal_representative', 'partner')
                            NOT NULL DEFAULT 'owner'
                            COMMENT 'Função: Proprietário, Representante Legal, Sócio',

  `verification_status`   ENUM('pending', 'verified', 'failed')
                            NOT NULL DEFAULT 'pending',
  `verified_at`           DATETIME DEFAULT NULL,

  `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_entrepreneur_business` (`business_id`),
  INDEX `idx_entrepreneur_cpf` (`cpf`),
  INDEX `idx_entrepreneur_verification` (`verification_status`),

  CONSTRAINT `fk_entrepreneur_business`
    FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- TABELA: business_bank_accounts
-- Dados bancários para repasse de pagamentos do marketplace
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `business_bank_accounts` (
  `id`                            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id`                   BIGINT UNSIGNED NOT NULL,

  -- Dados do Banco e Agência
  `bank_code`                     VARCHAR(10) NOT NULL COMMENT 'Código do banco (ex: 001, 341, 237)',
  `bank_name`                     VARCHAR(100) NOT NULL COMMENT 'Nome do banco (ex: Banco do Brasil)',
  `branch_number`                 VARCHAR(10) NOT NULL COMMENT 'Número da agência',
  `branch_digit`                  VARCHAR(2) DEFAULT NULL COMMENT 'Dígito da agência (quando aplicável)',

  -- Dados da Conta
  `account_number`                VARCHAR(20) NOT NULL,
  `account_digit`                 VARCHAR(2) NOT NULL COMMENT 'Dígito verificador da conta',
  `account_type`                  ENUM('corrente', 'poupanca')
                                    NOT NULL DEFAULT 'corrente'
                                    COMMENT 'Conta Corrente ou Conta Poupança',

  -- Titular da Conta
  `account_holder_name`           VARCHAR(255) NOT NULL COMMENT 'Nome do titular conforme cadastro bancário',
  `account_holder_cpf`            CHAR(11) DEFAULT NULL COMMENT 'CPF do titular (se Pessoa Física)',
  `account_holder_cnpj`           CHAR(14) DEFAULT NULL COMMENT 'CNPJ do titular (se Pessoa Jurídica)',
  `holder_entity_type`            ENUM('PF', 'PJ') NOT NULL DEFAULT 'PJ'
                                    COMMENT 'PF = Pessoa Física, PJ = Pessoa Jurídica',

  -- Verificação de Titularidade
  `ownership_verification_status` ENUM('pending', 'verified', 'failed')
                                    NOT NULL DEFAULT 'pending',
  `verification_method`           VARCHAR(50) DEFAULT NULL
                                    COMMENT 'Método: micro_deposit, document_check, api_validation',
  `verified_at`                   DATETIME DEFAULT NULL,
  `verification_notes`            TEXT DEFAULT NULL,

  `created_at`                    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bank_business` (`business_id`),
  INDEX `idx_bank_verification` (`ownership_verification_status`),

  CONSTRAINT `fk_bank_business`
    FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- TABELA: business_verification_logs
-- Trilha de auditoria imutável para ações de verificação e administração
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `business_verification_logs` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id`             BIGINT UNSIGNED NOT NULL,
  `administrator_user_id`   BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL para ações automatizadas do sistema',

  `action_type`             VARCHAR(50) NOT NULL
                              COMMENT 'Tipos: registration_submitted, cnpj_verified, document_approved, document_rejected, business_approved, business_rejected, business_suspended, status_changed, bank_account_verified, documents_requested',
  `previous_status`         VARCHAR(30) DEFAULT NULL,
  `new_status`              VARCHAR(30) DEFAULT NULL,
  `reason`                  TEXT DEFAULT NULL COMMENT 'Justificativa da ação realizada',

  `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_log_business` (`business_id`),
  INDEX `idx_log_admin` (`administrator_user_id`),
  INDEX `idx_log_action` (`action_type`),
  INDEX `idx_log_created` (`created_at`),

  CONSTRAINT `fk_log_business`
    FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_log_admin`
    FOREIGN KEY (`administrator_user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- TABELA: password_reset_tokens
-- Tokens para recuperação de senha com expiração
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `token_hash`    VARCHAR(255) NOT NULL COMMENT 'Hash SHA-256 do token enviado por e-mail',
  `expires_at`    DATETIME NOT NULL,
  `used_at`       DATETIME DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_reset_user` (`user_id`),
  INDEX `idx_reset_expires` (`expires_at`),

  CONSTRAINT `fk_reset_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================================
-- DADOS INICIAIS: Usuário Administrador Padrão
-- Senha: ConectaAdmin@2026 (hash Argon2id gerado via PHP)
-- IMPORTANTE: Alterar a senha em ambiente de produção!
-- ==========================================================================
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `phone`, `user_role`, `account_status`, `email_verified_at`)
VALUES (
  'Administrador Conecta',
  'admin@conectamarket.com.br',
  '$argon2id$v=19$m=65536,t=4,p=1$dGVzdHNhbHRjb25lY3Rh$YJ7dC8tLMGKGZ+R8mH9B5kW6bN3xU7E4qF2pA1vS0cQ',
  '(11) 4003-8920',
  'administrator',
  'active',
  NOW()
);


-- ==========================================================================
-- TABELA: business_subscription_plans (Canvas: Fontes de Receita)
-- Planos de assinatura configuráveis para empresas parceiras
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `business_subscription_plans` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_code`         VARCHAR(30) NOT NULL COMMENT 'Código: essencial, pro, corporativo',
  `plan_name`         VARCHAR(100) NOT NULL COMMENT 'Nome de exibição do plano',
  `description`       TEXT DEFAULT NULL,
  `monthly_price`     DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Valor mensal em BRL',
  `commission_rate`   DECIMAL(5, 4) NOT NULL DEFAULT 0.0800 COMMENT 'Taxa de comissão (ex: 0.0800 = 8%)',
  `max_products`      INT UNSIGNED DEFAULT NULL COMMENT 'Limite de produtos no catálogo (NULL = ilimitado)',
  `features`          JSON DEFAULT NULL COMMENT 'Lista de funcionalidades incluídas',
  `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plan_code` (`plan_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir os 3 planos definidos no Canvas de Negócios
INSERT INTO `business_subscription_plans` (`plan_code`, `plan_name`, `description`, `monthly_price`, `commission_rate`, `max_products`, `features`) VALUES
(
  'essencial',
  'Plano Essencial',
  'Ideal para comerciantes que estão iniciando as vendas online.',
  0.00,
  0.0800,
  100,
  '["Catálogo de até 100 produtos", "Acesso ao painel web de pedidos", "Rede de entregadores conectados", "Recebimento quinzenal automático", "Suporte padrão via e-mail e chat"]'
),
(
  'pro',
  'Plano Crescimento Pro',
  'Para empresas que buscam aceleração de vendas e destaque máximo.',
  79.90,
  0.0500,
  NULL,
  '["Catálogo ilimitado de produtos", "Posicionamento prioritário nas buscas", "Emissão de cupons de desconto próprios", "Dashboard analítico de vendas e clientes", "Repasse semanal antecipado", "Suporte dedicado via WhatsApp"]'
),
(
  'corporativo',
  'Plano Corporativo & Redes',
  'Para franquias, redes e grandes estabelecimentos.',
  199.00,
  0.0350,
  NULL,
  '["Multi-filiais em um único login", "Banner promocional rotativo na Home", "Integração via API com seu PDV/ERP", "Gerente de conta exclusivo", "Treinamento de equipe e onboarding VIP", "Taxa de entrega subsidiada para clientes"]'
);


-- ==========================================================================
-- TABELA: business_subscriptions
-- Assinaturas ativas vinculando empresas a seus respectivos planos
-- ==========================================================================
CREATE TABLE IF NOT EXISTS `business_subscriptions` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id`     BIGINT UNSIGNED NOT NULL,
  `plan_id`         BIGINT UNSIGNED NOT NULL,
  `status`          ENUM('active', 'paused', 'cancelled', 'expired') NOT NULL DEFAULT 'active',
  `started_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`      DATETIME DEFAULT NULL,
  `cancelled_at`    DATETIME DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_subscription_business` (`business_id`),
  INDEX `idx_subscription_status` (`status`),

  CONSTRAINT `fk_subscription_business`
    FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_subscription_plan`
    FOREIGN KEY (`plan_id`) REFERENCES `business_subscription_plans` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
