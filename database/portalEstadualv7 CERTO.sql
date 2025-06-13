-- =====================================================
-- Portal de Obras Estaduais - Versão 7.0 Completa
-- Sistema de Gestão de Fluxo de Obras Públicas
-- =====================================================
-- Autor: Sistema desenvolvido para Paranacidade
-- Data: 2025
-- Descrição: Sistema para controle de fluxo de obras públicas
-- com suporte a fluxos condicionais e rastreabilidade completa
-- =====================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema portal_obras_estaduais
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `portal_obras_estaduais` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portal_obras_estaduais`;

-- -----------------------------------------------------
-- Table `organizacao`
-- -----------------------------------------------------
-- Tabela que armazena todas as organizações do sistema
-- incluindo órgãos públicos e empresas privadas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `organizacao` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da organização',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome completo da organização',
  `tipo` ENUM('PARANACIDADE', 'SECID', 'SEED', 'SESA', 'SESP', 'EMPRESA', 'OUTRO') NOT NULL COMMENT 'Tipo de organização no sistema',
  `cnpj` VARCHAR(18) NULL COMMENT 'CNPJ da organização (formato: 00.000.000/0000-00)',
  `email` VARCHAR(255) NULL COMMENT 'Email principal de contato',
  `telefone` VARCHAR(20) NULL COMMENT 'Telefone principal',
  `endereco` TEXT NULL COMMENT 'Endereço completo',
  `responsavel_nome` VARCHAR(255) NULL COMMENT 'Nome do responsável pela organização',
  `responsavel_cargo` VARCHAR(100) NULL COMMENT 'Cargo do responsável',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do registro',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização',
  `created_by` INT NULL COMMENT 'ID do usuário que criou o registro',
  `updated_by` INT NULL COMMENT 'ID do usuário que fez a última atualização',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_cnpj` (`cnpj` ASC),
  INDEX `idx_tipo` (`tipo` ASC),
  INDEX `idx_ativo` (`is_ativo` ASC)
) ENGINE = InnoDB COMMENT = 'Armazena organizações participantes do sistema de obras';

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
-- Tabela de usuários do sistema
-- Integrada com LDAP para usuários internos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do usuário',
  `organizacao_id` INT NOT NULL COMMENT 'Organização à qual o usuário pertence',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nome completo do usuário',
  `email` VARCHAR(255) NOT NULL COMMENT 'Email do usuário (usado para login)',
  `password` VARCHAR(255) NULL COMMENT 'Senha criptografada (null para usuários LDAP)',
  `username` VARCHAR(255) NULL COMMENT 'Nome de usuário (para login LDAP)',
  `cpf` VARCHAR(14) NULL COMMENT 'CPF do usuário (formato: 000.000.000-00)',
  `telefone` VARCHAR(20) NULL COMMENT 'Telefone de contato',
  `cargo` VARCHAR(100) NULL COMMENT 'Cargo do usuário na organização',
  `email_verified_at` TIMESTAMP NULL COMMENT 'Data de verificação do email',
  `guid` VARCHAR(255) NULL COMMENT 'GUID do Active Directory',
  `domain` VARCHAR(255) NULL COMMENT 'Domínio do Active Directory',
  `manager` VARCHAR(255) NULL COMMENT 'DN do gerente no AD',
  `department` VARCHAR(255) NULL COMMENT 'Departamento no AD',
  `employee_number` VARCHAR(255) NULL COMMENT 'Número do funcionário',
  `is_externo` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Usuário externo, 0=Usuário interno',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `ultimo_acesso` TIMESTAMP NULL COMMENT 'Data/hora do último acesso ao sistema',
  `remember_token` VARCHAR(255) NULL COMMENT 'Token para "lembrar-me"',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  `created_by` INT NULL COMMENT 'ID do usuário que criou o registro',
  `updated_by` INT NULL COMMENT 'ID do usuário que atualizou',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_email` (`email` ASC),
  UNIQUE INDEX `uk_cpf` (`cpf` ASC),
  UNIQUE INDEX `uk_username` (`username` ASC),
  INDEX `fk_users_organizacao_idx` (`organizacao_id` ASC),
  INDEX `idx_ativo` (`is_ativo` ASC),
  CONSTRAINT `fk_users_organizacao`
    FOREIGN KEY (`organizacao_id`)
    REFERENCES `organizacao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Usuários do sistema - internos (LDAP) e externos';

-- -----------------------------------------------------
-- Table `termo_adesao`
-- -----------------------------------------------------
-- Termos de adesão assinados entre organizações e o estado
-- Base legal para participação no sistema
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `termo_adesao` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do termo',
  `organizacao_id` INT NOT NULL COMMENT 'Organização que assinou o termo',
  `numero_termo` VARCHAR(50) NOT NULL COMMENT 'Número oficial do termo',
  `descricao` TEXT NULL COMMENT 'Descrição do objeto do termo',
  `data_assinatura` DATE NOT NULL COMMENT 'Data de assinatura do termo',
  `data_vigencia_inicio` DATE NOT NULL COMMENT 'Início da vigência',
  `data_vigencia_fim` DATE NULL COMMENT 'Fim da vigência (null = indeterminado)',
  `valor_total` DECIMAL(15,2) NULL COMMENT 'Valor total previsto no termo',
  `path_arquivo` VARCHAR(500) NULL COMMENT 'Caminho do arquivo PDF do termo',
  `hash_arquivo` VARCHAR(64) NULL COMMENT 'Hash SHA256 do arquivo para validação',
  `status` ENUM('RASCUNHO', 'ASSINADO', 'VIGENTE', 'EXPIRADO', 'CANCELADO') NOT NULL DEFAULT 'RASCUNHO' COMMENT 'Status atual do termo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  `created_by` INT NULL COMMENT 'Usuário que criou',
  `updated_by` INT NULL COMMENT 'Usuário que atualizou',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_numero_termo` (`numero_termo` ASC),
  INDEX `fk_termo_adesao_organizacao_idx` (`organizacao_id` ASC),
  INDEX `idx_status` (`status` ASC),
  INDEX `idx_vigencia` (`data_vigencia_inicio` ASC, `data_vigencia_fim` ASC),
  CONSTRAINT `fk_termo_adesao_organizacao`
    FOREIGN KEY (`organizacao_id`)
    REFERENCES `organizacao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Termos de adesão ao sistema de obras estaduais';

-- -----------------------------------------------------
-- Table `cadastro_demanda_gms`
-- -----------------------------------------------------
-- Integração com o sistema GMS (Gestão de Materiais e Serviços)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cadastro_demanda_gms` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `codigo_gms` VARCHAR(50) NOT NULL COMMENT 'Código único no sistema GMS',
  `protocolo` VARCHAR(100) NOT NULL COMMENT 'Número do protocolo oficial',
  `descricao` TEXT NULL COMMENT 'Descrição da demanda no GMS',
  `status_gms` VARCHAR(50) NULL COMMENT 'Status atual no sistema GMS',
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro no GMS',
  `data_sincronizacao` TIMESTAMP NULL COMMENT 'Última sincronização com GMS',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_codigo_gms` (`codigo_gms` ASC),
  UNIQUE INDEX `uk_protocolo` (`protocolo` ASC)
) ENGINE = InnoDB COMMENT = 'Cadastro de demandas integradas com sistema GMS';

-- -----------------------------------------------------
-- Table `demanda`
-- -----------------------------------------------------
-- Demandas de obras solicitadas pelas organizações
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `demanda` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da demanda',
  `termo_adesao_id` INT NOT NULL COMMENT 'Termo de adesão relacionado',
  `cadastro_demanda_gms_id` INT NULL COMMENT 'Cadastro no sistema GMS',
  `prioridade_sam` VARCHAR(50) NULL COMMENT 'Prioridade no sistema SAM',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome/título da demanda',
  `descricao` TEXT NULL COMMENT 'Descrição detalhada da demanda',
  `justificativa` TEXT NULL COMMENT 'Justificativa técnica da demanda',
  `valor_estimado` DECIMAL(15,2) NULL COMMENT 'Valor estimado da obra',
  `localizacao` VARCHAR(500) NULL COMMENT 'Endereço/localização da obra',
  `coordenadas_lat` DECIMAL(10,8) NULL COMMENT 'Latitude',
  `coordenadas_lng` DECIMAL(11,8) NULL COMMENT 'Longitude',
  `beneficiarios_estimados` INT NULL COMMENT 'Número estimado de beneficiários',
  `status` ENUM('NOVA', 'EM_ANALISE', 'APROVADA', 'REPROVADA', 'CANCELADA') NOT NULL DEFAULT 'NOVA' COMMENT 'Status da demanda',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  `created_by` INT NULL COMMENT 'Usuário que criou',
  `updated_by` INT NULL COMMENT 'Usuário que atualizou',
  PRIMARY KEY (`id`),
  INDEX `fk_demanda_termo_adesao_idx` (`termo_adesao_id` ASC),
  INDEX `fk_demanda_cadastro_gms_idx` (`cadastro_demanda_gms_id` ASC),
  INDEX `idx_status` (`status` ASC),
  CONSTRAINT `fk_demanda_termo_adesao`
    FOREIGN KEY (`termo_adesao_id`)
    REFERENCES `termo_adesao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_demanda_cadastro_gms`
    FOREIGN KEY (`cadastro_demanda_gms_id`)
    REFERENCES `cadastro_demanda_gms` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Demandas de obras das organizações';

-- -----------------------------------------------------
-- Table `tipo_fluxo`
-- -----------------------------------------------------
-- Tipos de fluxo disponíveis no sistema (workflows)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipo_fluxo` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do tipo de fluxo',
  `descricao` TEXT NULL COMMENT 'Descrição detalhada do fluxo',
  `categoria` VARCHAR(100) NULL COMMENT 'Categoria do fluxo (ESCOLA, SAUDE, SEGURANCA, etc)',
  `versao` VARCHAR(20) NOT NULL DEFAULT '1.0' COMMENT 'Versão do fluxo',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  `created_by` INT NULL COMMENT 'Usuário que criou',
  `updated_by` INT NULL COMMENT 'Usuário que atualizou',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_nome_versao` (`nome` ASC, `versao` ASC),
  INDEX `idx_ativo` (`is_ativo` ASC),
  INDEX `idx_categoria` (`categoria` ASC)
) ENGINE = InnoDB COMMENT = 'Tipos de fluxo (workflows) disponíveis';

-- -----------------------------------------------------
-- Table `acao`
-- -----------------------------------------------------
-- Ações/Obras aprovadas para execução
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acao` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da ação',
  `demanda_id` INT NOT NULL COMMENT 'Demanda que originou a ação',
  `tipo_fluxo_id` INT NOT NULL COMMENT 'Tipo de fluxo a ser seguido',
  `codigo_referencia` VARCHAR(100) NULL COMMENT 'Código único de referência da obra',
  `projeto_sam` VARCHAR(100) NULL COMMENT 'Código do projeto no sistema SAM',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome da ação/obra',
  `descricao` TEXT NULL COMMENT 'Descrição detalhada',
  `valor_estimado` DECIMAL(15,2) NULL COMMENT 'Valor estimado inicial',
  `valor_contratado` DECIMAL(15,2) NULL COMMENT 'Valor efetivamente contratado',
  `valor_executado` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'Valor já executado',
  `percentual_execucao` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Percentual de execução física',
  `localizacao` VARCHAR(500) NULL COMMENT 'Endereço completo da obra',
  `coordenadas_lat` DECIMAL(10,8) NULL COMMENT 'Latitude',
  `coordenadas_lng` DECIMAL(11,8) NULL COMMENT 'Longitude',
  `data_inicio_previsto` DATE NULL COMMENT 'Data prevista de início',
  `data_fim_previsto` DATE NULL COMMENT 'Data prevista de término',
  `data_inicio_real` DATE NULL COMMENT 'Data real de início',
  `data_fim_real` DATE NULL COMMENT 'Data real de término',
  `status` ENUM('PLANEJAMENTO', 'EM_EXECUCAO', 'PARALISADA', 'CONCLUIDA', 'CANCELADA') NOT NULL DEFAULT 'PLANEJAMENTO' COMMENT 'Status da ação',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  `created_by` INT NULL COMMENT 'Usuário que criou',
  `updated_by` INT NULL COMMENT 'Usuário que atualizou',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_codigo_referencia` (`codigo_referencia` ASC),
  INDEX `fk_acao_demanda_idx` (`demanda_id` ASC),
  INDEX `fk_acao_tipo_fluxo_idx` (`tipo_fluxo_id` ASC),
  INDEX `idx_status` (`status` ASC),
  INDEX `idx_datas` (`data_inicio_previsto` ASC, `data_fim_previsto` ASC),
  CONSTRAINT `fk_acao_demanda`
    FOREIGN KEY (`demanda_id`)
    REFERENCES `demanda` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_acao_tipo_fluxo`
    FOREIGN KEY (`tipo_fluxo_id`)
    REFERENCES `tipo_fluxo` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Ações/Obras aprovadas e em execução';

-- -----------------------------------------------------
-- Table `modulo`
-- -----------------------------------------------------
-- Módulos disponíveis no sistema (componentes reutilizáveis)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `modulo` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do módulo',
  `tipo` ENUM('ENVIO', 'ANALISE', 'ASSINATURA') NOT NULL COMMENT 'Tipo do módulo',
  `descricao` TEXT NULL COMMENT 'Descrição do módulo',
  `icone` VARCHAR(50) NULL COMMENT 'Ícone do módulo (FontAwesome)',
  `cor` VARCHAR(7) NULL COMMENT 'Cor do módulo (hexadecimal)',
  `campos_customizaveis` JSON NULL COMMENT 'Definição de campos customizáveis em JSON',
  `configuracao_padrao` JSON NULL COMMENT 'Configurações padrão do módulo',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_nome` (`nome` ASC),
  INDEX `idx_tipo` (`tipo` ASC),
  INDEX `idx_ativo` (`is_ativo` ASC)
) ENGINE = InnoDB COMMENT = 'Módulos/componentes reutilizáveis do sistema';

-- -----------------------------------------------------
-- Table `grupo_exigencia`
-- -----------------------------------------------------
-- Grupos de exigências documentais
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `grupo_exigencia` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do grupo de exigência',
  `descricao` TEXT NULL COMMENT 'Descrição do grupo',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_nome` (`nome` ASC)
) ENGINE = InnoDB COMMENT = 'Grupos de exigências documentais';

-- -----------------------------------------------------
-- Table `etapa_fluxo`
-- -----------------------------------------------------
-- Definição das etapas de cada tipo de fluxo
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `etapa_fluxo` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `tipo_fluxo_id` INT NOT NULL COMMENT 'Tipo de fluxo ao qual pertence',
  `modulo_id` INT NOT NULL COMMENT 'Módulo utilizado nesta etapa',
  `grupo_exigencia_id` INT NULL COMMENT 'Grupo de exigências desta etapa',
  `organizacao_solicitante_id` INT NOT NULL COMMENT 'Organização que solicita a ação',
  `organizacao_executora_id` INT NOT NULL COMMENT 'Organização que executa a ação',
  `ordem_execucao` INT NULL COMMENT 'Ordem de execução (null para etapas condicionais)',
  `nome_etapa` VARCHAR(255) NOT NULL COMMENT 'Nome da etapa',
  `descricao_customizada` TEXT NULL COMMENT 'Descrição específica desta etapa',
  `prazo_dias` INT NOT NULL DEFAULT 5 COMMENT 'Prazo em dias úteis',
  `tipo_prazo` ENUM('UTEIS', 'CORRIDOS') NOT NULL DEFAULT 'UTEIS' COMMENT 'Tipo de contagem do prazo',
  `is_obrigatoria` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Obrigatória, 0=Opcional',
  `permite_pular` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Permite pular, 0=Não permite',
  `permite_retorno` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Permite retorno, 0=Não permite',
  `tipo_etapa` ENUM('SEQUENCIAL', 'CONDICIONAL') NOT NULL DEFAULT 'SEQUENCIAL' COMMENT 'Tipo da etapa',
  `configuracoes` JSON NULL COMMENT 'Configurações específicas da etapa',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  INDEX `fk_etapa_fluxo_tipo_fluxo_idx` (`tipo_fluxo_id` ASC),
  INDEX `fk_etapa_fluxo_modulo_idx` (`modulo_id` ASC),
  INDEX `fk_etapa_fluxo_grupo_exigencia_idx` (`grupo_exigencia_id` ASC),
  INDEX `fk_etapa_fluxo_org_solicitante_idx` (`organizacao_solicitante_id` ASC),
  INDEX `fk_etapa_fluxo_org_executora_idx` (`organizacao_executora_id` ASC),
  INDEX `idx_ordem` (`tipo_fluxo_id` ASC, `ordem_execucao` ASC),
  CONSTRAINT `fk_etapa_fluxo_tipo_fluxo`
    FOREIGN KEY (`tipo_fluxo_id`)
    REFERENCES `tipo_fluxo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_etapa_fluxo_modulo`
    FOREIGN KEY (`modulo_id`)
    REFERENCES `modulo` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_etapa_fluxo_grupo_exigencia`
    FOREIGN KEY (`grupo_exigencia_id`)
    REFERENCES `grupo_exigencia` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_etapa_fluxo_org_solicitante`
    FOREIGN KEY (`organizacao_solicitante_id`)
    REFERENCES `organizacao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_etapa_fluxo_org_executora`
    FOREIGN KEY (`organizacao_executora_id`)
    REFERENCES `organizacao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Etapas que compõem cada tipo de fluxo';

-- -----------------------------------------------------
-- Table `status`
-- -----------------------------------------------------
-- Status possíveis no sistema
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `status` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `codigo` VARCHAR(50) NOT NULL COMMENT 'Código único do status',
  `nome` VARCHAR(100) NOT NULL COMMENT 'Nome do status',
  `descricao` TEXT NULL COMMENT 'Descrição do status',
  `categoria` ENUM('EXECUCAO', 'DOCUMENTO', 'GERAL') NOT NULL DEFAULT 'EXECUCAO' COMMENT 'Categoria do status',
  `cor` VARCHAR(7) NULL COMMENT 'Cor para exibição (hexadecimal)',
  `icone` VARCHAR(50) NULL COMMENT 'Ícone (FontAwesome)',
  `ordem` INT NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_codigo` (`codigo` ASC),
  INDEX `idx_categoria` (`categoria` ASC),
  INDEX `idx_ordem` (`ordem` ASC)
) ENGINE = InnoDB COMMENT = 'Status possíveis para etapas e documentos';

-- Inserir status padrão
INSERT INTO `status` (`codigo`, `nome`, `descricao`, `categoria`, `cor`, `ordem`) VALUES
('PENDENTE', 'Pendente', 'Aguardando início', 'EXECUCAO', '#6c757d', 1),
('EM_ANALISE', 'Em Análise', 'Em processo de análise', 'EXECUCAO', '#ffc107', 2),
('APROVADO', 'Aprovado', 'Aprovado com sucesso', 'EXECUCAO', '#28a745', 3),
('REPROVADO', 'Reprovado', 'Reprovado - necessita correções', 'EXECUCAO', '#dc3545', 4),
('DEVOLVIDO', 'Devolvido para Correção', 'Retornado para ajustes', 'EXECUCAO', '#fd7e14', 5),
('CANCELADO', 'Cancelado', 'Processo cancelado', 'EXECUCAO', '#6c757d', 6);

-- -----------------------------------------------------
-- Table `execucao_etapa`
-- -----------------------------------------------------
-- Execução das etapas para cada ação/obra
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `execucao_etapa` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `acao_id` INT NOT NULL COMMENT 'Ação/obra relacionada',
  `etapa_fluxo_id` INT NOT NULL COMMENT 'Etapa do fluxo sendo executada',
  `usuario_responsavel_id` INT NULL COMMENT 'Usuário responsável atual',
  `status_id` INT NOT NULL COMMENT 'Status atual da execução',
  `etapa_anterior_id` INT NULL COMMENT 'Execução anterior (para rastreabilidade)',
  `data_inicio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Início da execução',
  `data_prazo` TIMESTAMP NULL COMMENT 'Prazo para conclusão',
  `data_conclusao` TIMESTAMP NULL COMMENT 'Data de conclusão real',
  `dias_em_atraso` INT NULL DEFAULT 0 COMMENT 'Dias de atraso (calculado)',
  `observacoes` TEXT NULL COMMENT 'Observações gerais',
  `justificativa` TEXT NULL COMMENT 'Justificativa (para reprovação/devolução)',
  `motivo_transicao` VARCHAR(500) NULL COMMENT 'Motivo da transição entre etapas',
  `dados_especificos` JSON NULL COMMENT 'Dados específicos da execução',
  `percentual_conclusao` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Percentual concluído',
  `notificacao_enviada` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se notificação foi enviada',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  `created_by` INT NULL COMMENT 'Usuário que criou',
  `updated_by` INT NULL COMMENT 'Usuário que atualizou',
  PRIMARY KEY (`id`),
  INDEX `fk_execucao_etapa_acao_idx` (`acao_id` ASC),
  INDEX `fk_execucao_etapa_etapa_fluxo_idx` (`etapa_fluxo_id` ASC),
  INDEX `fk_execucao_etapa_usuario_idx` (`usuario_responsavel_id` ASC),
  INDEX `fk_execucao_etapa_status_idx` (`status_id` ASC),
  INDEX `fk_execucao_etapa_anterior_idx` (`etapa_anterior_id` ASC),
  INDEX `idx_prazo` (`data_prazo` ASC),
  INDEX `idx_conclusao` (`data_conclusao` ASC),
  CONSTRAINT `fk_execucao_etapa_acao`
    FOREIGN KEY (`acao_id`)
    REFERENCES `acao` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_execucao_etapa_etapa_fluxo`
    FOREIGN KEY (`etapa_fluxo_id`)
    REFERENCES `etapa_fluxo` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_execucao_etapa_usuario`
    FOREIGN KEY (`usuario_responsavel_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_execucao_etapa_status`
    FOREIGN KEY (`status_id`)
    REFERENCES `status` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_execucao_etapa_anterior`
    FOREIGN KEY (`etapa_anterior_id`)
    REFERENCES `execucao_etapa` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Registro de execução de cada etapa do fluxo';

-- -----------------------------------------------------
-- Table `tipo_documento`
-- -----------------------------------------------------
-- Tipos de documentos aceitos no sistema
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipo_documento` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `codigo` VARCHAR(50) NOT NULL COMMENT 'Código único do tipo',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do tipo de documento',
  `descricao` TEXT NULL COMMENT 'Descrição do tipo',
  `extensoes_permitidas` VARCHAR(255) NULL COMMENT 'Extensões permitidas (ex: pdf,doc,docx)',
  `tamanho_maximo_mb` INT NOT NULL DEFAULT 10 COMMENT 'Tamanho máximo em MB',
  `requer_assinatura` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Requer assinatura digital',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_codigo` (`codigo` ASC)
) ENGINE = InnoDB COMMENT = 'Tipos de documentos aceitos pelo sistema';

-- -----------------------------------------------------
-- Table `template_documento`
-- -----------------------------------------------------
-- Templates/modelos de documentos por grupo de exigência
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `template_documento` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `grupo_exigencia_id` INT NOT NULL COMMENT 'Grupo de exigência relacionado',
  `tipo_documento_id` INT NOT NULL COMMENT 'Tipo de documento',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do template',
  `descricao` TEXT NULL COMMENT 'Descrição/instruções',
  `caminho_modelo_storage` VARCHAR(500) NULL COMMENT 'Caminho do arquivo modelo',
  `exemplo_preenchido` VARCHAR(500) NULL COMMENT 'Caminho de exemplo preenchido',
  `is_obrigatorio` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Obrigatório, 0=Opcional',
  `ordem` INT NOT NULL DEFAULT 0 COMMENT 'Ordem de apresentação',
  `instrucoes_preenchimento` TEXT NULL COMMENT 'Instruções detalhadas',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  INDEX `fk_template_doc_grupo_exigencia_idx` (`grupo_exigencia_id` ASC),
  INDEX `fk_template_doc_tipo_documento_idx` (`tipo_documento_id` ASC),
  INDEX `idx_ordem` (`grupo_exigencia_id` ASC, `ordem` ASC),
  CONSTRAINT `fk_template_doc_grupo_exigencia`
    FOREIGN KEY (`grupo_exigencia_id`)
    REFERENCES `grupo_exigencia` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_template_doc_tipo_documento`
    FOREIGN KEY (`tipo_documento_id`)
    REFERENCES `tipo_documento` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Templates de documentos por grupo de exigência';

-- -----------------------------------------------------
-- Table `documento`
-- -----------------------------------------------------
-- Documentos enviados durante a execução das etapas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `documento` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `execucao_etapa_id` INT NOT NULL COMMENT 'Execução da etapa relacionada',
  `template_documento_id` INT NULL COMMENT 'Template usado (se aplicável)',
  `tipo_documento_id` INT NOT NULL COMMENT 'Tipo do documento',
  `usuario_upload_id` INT NOT NULL COMMENT 'Usuário que fez upload',
  `nome_arquivo` VARCHAR(500) NOT NULL COMMENT 'Nome original do arquivo',
  `nome_arquivo_sistema` VARCHAR(500) NOT NULL COMMENT 'Nome do arquivo no sistema',
  `tamanho_bytes` BIGINT NOT NULL COMMENT 'Tamanho em bytes',
  `mime_type` VARCHAR(100) NULL COMMENT 'Tipo MIME do arquivo',
  `hash_arquivo` VARCHAR(64) NOT NULL COMMENT 'Hash SHA256 para validação',
  `caminho_storage` VARCHAR(1000) NOT NULL COMMENT 'Caminho no storage',
  `versao` INT NOT NULL DEFAULT 1 COMMENT 'Versão do documento',
  `documento_pai_id` INT NULL COMMENT 'Documento anterior (versionamento)',
  `is_assinado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Assinado digitalmente',
  `data_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora do upload',
  `data_validade` DATE NULL COMMENT 'Data de validade do documento',
  `observacoes` TEXT NULL COMMENT 'Observações sobre o documento',
  `metadata` JSON NULL COMMENT 'Metadados adicionais',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  INDEX `fk_documento_execucao_etapa_idx` (`execucao_etapa_id` ASC),
  INDEX `fk_documento_template_idx` (`template_documento_id` ASC),
  INDEX `fk_documento_tipo_idx` (`tipo_documento_id` ASC),
  INDEX `fk_documento_usuario_idx` (`usuario_upload_id` ASC),
  INDEX `fk_documento_pai_idx` (`documento_pai_id` ASC),
  INDEX `idx_hash` (`hash_arquivo` ASC),
  INDEX `idx_versao` (`execucao_etapa_id` ASC, `tipo_documento_id` ASC, `versao` ASC),
  CONSTRAINT `fk_documento_execucao_etapa`
    FOREIGN KEY (`execucao_etapa_id`)
    REFERENCES `execucao_etapa` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_documento_template`
    FOREIGN KEY (`template_documento_id`)
    REFERENCES `template_documento` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_documento_tipo`
    FOREIGN KEY (`tipo_documento_id`)
    REFERENCES `tipo_documento` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_documento_usuario`
    FOREIGN KEY (`usuario_upload_id`)
    REFERENCES `users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_documento_pai`
    FOREIGN KEY (`documento_pai_id`)
    REFERENCES `documento` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Documentos enviados nas execuções';

-- -----------------------------------------------------
-- Table `historico_etapa`
-- -----------------------------------------------------
-- Histórico de todas as ações nas etapas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `historico_etapa` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `execucao_etapa_id` INT NOT NULL COMMENT 'Execução relacionada',
  `usuario_id` INT NOT NULL COMMENT 'Usuário que realizou a ação',
  `status_anterior_id` INT NULL COMMENT 'Status anterior',
  `status_novo_id` INT NULL COMMENT 'Novo status',
  `acao` VARCHAR(100) NOT NULL COMMENT 'Tipo de ação realizada',
  `descricao_acao` VARCHAR(500) NULL COMMENT 'Descrição da ação',
  `observacao` TEXT NULL COMMENT 'Observações do usuário',
  `dados_alterados` JSON NULL COMMENT 'Dados que foram alterados',
  `ip_usuario` VARCHAR(45) NULL COMMENT 'IP do usuário',
  `user_agent` VARCHAR(500) NULL COMMENT 'Browser/sistema do usuário',
  `data_acao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora da ação',
  PRIMARY KEY (`id`),
  INDEX `fk_historico_execucao_etapa_idx` (`execucao_etapa_id` ASC),
  INDEX `fk_historico_usuario_idx` (`usuario_id` ASC),
  INDEX `fk_historico_status_anterior_idx` (`status_anterior_id` ASC),
  INDEX `fk_historico_status_novo_idx` (`status_novo_id` ASC),
  INDEX `idx_data_acao` (`data_acao` ASC),
  INDEX `idx_acao` (`acao` ASC),
  CONSTRAINT `fk_historico_execucao_etapa`
    FOREIGN KEY (`execucao_etapa_id`)
    REFERENCES `execucao_etapa` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_usuario`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_status_anterior`
    FOREIGN KEY (`status_anterior_id`)
    REFERENCES `status` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_status_novo`
    FOREIGN KEY (`status_novo_id`)
    REFERENCES `status` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Histórico completo de ações nas etapas';

-- -----------------------------------------------------
-- Table `tipo_notificacao`
-- -----------------------------------------------------
-- Tipos de notificação disponíveis
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipo_notificacao` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `codigo` VARCHAR(50) NOT NULL COMMENT 'Código único',
  `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do tipo',
  `descricao` TEXT NULL COMMENT 'Descrição',
  `template_email` TEXT NULL COMMENT 'Template HTML do email',
  `template_sms` TEXT NULL COMMENT 'Template do SMS',
  `template_sistema` TEXT NULL COMMENT 'Template para notificação no sistema',
  `variaveis_disponiveis` JSON NULL COMMENT 'Variáveis disponíveis para o template',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uk_codigo` (`codigo` ASC)
) ENGINE = InnoDB COMMENT = 'Tipos de notificação com templates';

-- -----------------------------------------------------
-- Table `notificacao`
-- -----------------------------------------------------
-- Notificações enviadas pelo sistema
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `notificacao` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `execucao_etapa_id` INT NOT NULL COMMENT 'Execução relacionada',
  `usuario_destinatario_id` INT NOT NULL COMMENT 'Usuário destinatário',
  `tipo_notificacao_id` INT NOT NULL COMMENT 'Tipo de notificação',
  `canal` ENUM('EMAIL', 'SISTEMA', 'SMS', 'WHATSAPP') NOT NULL DEFAULT 'SISTEMA' COMMENT 'Canal de envio',
  `assunto` VARCHAR(500) NULL COMMENT 'Assunto (para email)',
  `mensagem` TEXT NOT NULL COMMENT 'Conteúdo da mensagem',
  `prioridade` ENUM('BAIXA', 'MEDIA', 'ALTA', 'URGENTE') NOT NULL DEFAULT 'MEDIA' COMMENT 'Prioridade',
  `data_envio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora de envio',
  `data_leitura` TIMESTAMP NULL COMMENT 'Data/hora de leitura',
  `data_expiracao` TIMESTAMP NULL COMMENT 'Data de expiração da notificação',
  `status_envio` ENUM('PENDENTE', 'ENVIADO', 'ERRO', 'LIDO', 'EXPIRADO') NOT NULL DEFAULT 'PENDENTE' COMMENT 'Status do envio',
  `tentativas` INT NOT NULL DEFAULT 0 COMMENT 'Número de tentativas de envio',
  `erro_mensagem` TEXT NULL COMMENT 'Mensagem de erro (se houver)',
  `metadata` JSON NULL COMMENT 'Dados adicionais',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  INDEX `fk_notificacao_execucao_etapa_idx` (`execucao_etapa_id` ASC),
  INDEX `fk_notificacao_usuario_idx` (`usuario_destinatario_id` ASC),
  INDEX `fk_notificacao_tipo_idx` (`tipo_notificacao_id` ASC),
  INDEX `idx_status_envio` (`status_envio` ASC),
  INDEX `idx_data_envio` (`data_envio` ASC),
  INDEX `idx_prioridade` (`prioridade` ASC),
  CONSTRAINT `fk_notificacao_execucao_etapa`
    FOREIGN KEY (`execucao_etapa_id`)
    REFERENCES `execucao_etapa` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notificacao_usuario`
    FOREIGN KEY (`usuario_destinatario_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notificacao_tipo`
    FOREIGN KEY (`tipo_notificacao_id`)
    REFERENCES `tipo_notificacao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Notificações enviadas aos usuários';

-- -----------------------------------------------------
-- Table `transicao_etapa`
-- -----------------------------------------------------
-- Define as transições possíveis entre etapas (fluxo condicional)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `transicao_etapa` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `etapa_fluxo_origem_id` INT NOT NULL COMMENT 'Etapa de origem',
  `etapa_fluxo_destino_id` INT NOT NULL COMMENT 'Etapa de destino',
  `status_condicao_id` INT NULL COMMENT 'Status que dispara a transição',
  `condicao_tipo` ENUM('STATUS', 'VALOR', 'CAMPO_CUSTOMIZADO', 'PRAZO_EXPIRADO', 'MULTIPLA', 'SEMPRE') NOT NULL DEFAULT 'STATUS' COMMENT 'Tipo de condição',
  `condicao_operador` ENUM('=', '!=', '>', '<', '>=', '<=', 'IN', 'NOT IN', 'BETWEEN', 'CONTAINS') NULL COMMENT 'Operador da condição',
  `condicao_valor` TEXT NULL COMMENT 'Valor para comparação (pode ser JSON)',
  `condicao_campo` VARCHAR(100) NULL COMMENT 'Nome do campo para condições customizadas',
  `logica_adicional` ENUM('AND', 'OR') NULL COMMENT 'Lógica para múltiplas condições',
  `prioridade` INT NOT NULL DEFAULT 0 COMMENT 'Prioridade (maior = mais prioritário)',
  `descricao` VARCHAR(500) NULL COMMENT 'Descrição da transição',
  `mensagem_transicao` TEXT NULL COMMENT 'Mensagem exibida na transição',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  INDEX `fk_transicao_etapa_origem_idx` (`etapa_fluxo_origem_id` ASC),
  INDEX `fk_transicao_etapa_destino_idx` (`etapa_fluxo_destino_id` ASC),
  INDEX `fk_transicao_status_idx` (`status_condicao_id` ASC),
  INDEX `idx_prioridade` (`etapa_fluxo_origem_id` ASC, `prioridade` DESC),
  CONSTRAINT `fk_transicao_etapa_origem`
    FOREIGN KEY (`etapa_fluxo_origem_id`)
    REFERENCES `etapa_fluxo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_transicao_etapa_destino`
    FOREIGN KEY (`etapa_fluxo_destino_id`)
    REFERENCES `etapa_fluxo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_transicao_status`
    FOREIGN KEY (`status_condicao_id`)
    REFERENCES `status` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Transições condicionais entre etapas';

-- -----------------------------------------------------
-- Table `etapa_status_opcao`
-- -----------------------------------------------------
-- Define quais status são válidos para cada etapa
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `etapa_status_opcao` (
  `etapa_fluxo_id` INT NOT NULL COMMENT 'Etapa do fluxo',
  `status_id` INT NOT NULL COMMENT 'Status disponível',
  `ordem` INT NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
  `is_padrao` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Status padrão da etapa',
  `mostra_para_responsavel` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Visível ao responsável',
  `requer_justificativa` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Requer justificativa',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  PRIMARY KEY (`etapa_fluxo_id`, `status_id`),
  INDEX `fk_etapa_status_opcao_status_idx` (`status_id` ASC),
  INDEX `idx_ordem` (`etapa_fluxo_id` ASC, `ordem` ASC),
  CONSTRAINT `fk_etapa_status_opcao_etapa`
    FOREIGN KEY (`etapa_fluxo_id`)
    REFERENCES `etapa_fluxo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_etapa_status_opcao_status`
    FOREIGN KEY (`status_id`)
    REFERENCES `status` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Status disponíveis por etapa';

-- -----------------------------------------------------
-- Table `execucao_campo_dinamico`
-- -----------------------------------------------------
-- Campos dinâmicos/customizados das execuções
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `execucao_campo_dinamico` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `execucao_etapa_id` INT NOT NULL COMMENT 'Execução relacionada',
  `nome_campo` VARCHAR(100) NOT NULL COMMENT 'Nome do campo',
  `label_campo` VARCHAR(255) NULL COMMENT 'Label para exibição',
  `tipo_campo` ENUM('TEXTO', 'NUMERO', 'DATA', 'BOOLEANO', 'LISTA', 'JSON') NOT NULL COMMENT 'Tipo do campo',
  `valor_texto` TEXT NULL COMMENT 'Valor se tipo TEXTO',
  `valor_numero` DECIMAL(20,4) NULL COMMENT 'Valor se tipo NUMERO',
  `valor_data` DATE NULL COMMENT 'Valor se tipo DATA',
  `valor_booleano` TINYINT(1) NULL COMMENT 'Valor se tipo BOOLEANO',
  `valor_json` JSON NULL COMMENT 'Valor se tipo JSON ou LISTA',
  `is_obrigatorio` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Campo obrigatório',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  `created_by` INT NULL COMMENT 'Usuário que criou',
  `updated_by` INT NULL COMMENT 'Usuário que atualizou',
  PRIMARY KEY (`id`),
  INDEX `fk_campo_dinamico_execucao_idx` (`execucao_etapa_id` ASC),
  INDEX `idx_nome_campo` (`nome_campo` ASC),
  UNIQUE INDEX `uk_execucao_campo` (`execucao_etapa_id` ASC, `nome_campo` ASC),
  CONSTRAINT `fk_campo_dinamico_execucao`
    FOREIGN KEY (`execucao_etapa_id`)
    REFERENCES `execucao_etapa` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Valores de campos dinâmicos/customizados';

-- -----------------------------------------------------
-- Table `configuracao_notificacao`
-- -----------------------------------------------------
-- Configurações de notificações automáticas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracao_notificacao` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `tipo_fluxo_id` INT NULL COMMENT 'Tipo de fluxo (null = todas)',
  `etapa_fluxo_id` INT NULL COMMENT 'Etapa específica (null = todas)',
  `tipo_notificacao_id` INT NOT NULL COMMENT 'Tipo de notificação',
  `evento` ENUM('INICIO_ETAPA', 'PRAZO_PROXIMO', 'PRAZO_EXPIRADO', 'STATUS_ALTERADO', 'DOCUMENTO_ENVIADO', 'DOCUMENTO_APROVADO', 'DOCUMENTO_REPROVADO') NOT NULL COMMENT 'Evento que dispara',
  `dias_antecedencia_prazo` INT NULL COMMENT 'Dias antes do prazo (para PRAZO_PROXIMO)',
  `destinatarios` ENUM('RESPONSAVEL', 'SOLICITANTE', 'EXECUTOR', 'TODOS', 'CUSTOMIZADO') NOT NULL DEFAULT 'RESPONSAVEL' COMMENT 'Quem recebe',
  `destinatarios_adicionais` JSON NULL COMMENT 'Emails/IDs adicionais',
  `is_ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  PRIMARY KEY (`id`),
  INDEX `fk_config_notif_tipo_fluxo_idx` (`tipo_fluxo_id` ASC),
  INDEX `fk_config_notif_etapa_idx` (`etapa_fluxo_id` ASC),
  INDEX `fk_config_notif_tipo_idx` (`tipo_notificacao_id` ASC),
  INDEX `idx_evento` (`evento` ASC),
  CONSTRAINT `fk_config_notif_tipo_fluxo`
    FOREIGN KEY (`tipo_fluxo_id`)
    REFERENCES `tipo_fluxo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_config_notif_etapa`
    FOREIGN KEY (`etapa_fluxo_id`)
    REFERENCES `etapa_fluxo` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_config_notif_tipo`
    FOREIGN KEY (`tipo_notificacao_id`)
    REFERENCES `tipo_notificacao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Configurações de notificações automáticas';

-- -----------------------------------------------------
-- Table `log_integracao`
-- -----------------------------------------------------
-- Log de integrações com sistemas externos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `log_integracao` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `sistema` ENUM('GMS', 'SAM', 'LDAP', 'EMAIL', 'SMS', 'OUTRO') NOT NULL COMMENT 'Sistema integrado',
  `operacao` VARCHAR(100) NOT NULL COMMENT 'Operação realizada',
  `endpoint` VARCHAR(500) NULL COMMENT 'Endpoint chamado',
  `metodo` VARCHAR(10) NULL COMMENT 'Método HTTP',
  `request_data` JSON NULL COMMENT 'Dados enviados',
  `response_data` JSON NULL COMMENT 'Dados recebidos',
  `status_code` INT NULL COMMENT 'Código de status HTTP',
  `sucesso` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Sucesso, 0=Erro',
  `mensagem_erro` TEXT NULL COMMENT 'Mensagem de erro',
  `tempo_resposta_ms` INT NULL COMMENT 'Tempo de resposta em ms',
  `ip_origem` VARCHAR(45) NULL COMMENT 'IP de origem',
  `usuario_id` INT NULL COMMENT 'Usuário que disparou',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora da integração',
  PRIMARY KEY (`id`),
  INDEX `idx_sistema` (`sistema` ASC),
  INDEX `idx_operacao` (`operacao` ASC),
  INDEX `idx_sucesso` (`sucesso` ASC),
  INDEX `idx_data` (`created_at` ASC),
  INDEX `fk_log_integracao_usuario_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_log_integracao_usuario`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Log de todas as integrações com sistemas externos';

-- =====================================================
-- INSERÇÃO DE DADOS INICIAIS
-- =====================================================

-- Inserir tipos de notificação padrão
INSERT INTO `tipo_notificacao` (`codigo`, `nome`, `descricao`) VALUES
('NOVA_ETAPA', 'Nova Etapa Iniciada', 'Notificação quando uma nova etapa é iniciada'),
('PRAZO_PROXIMO', 'Prazo Próximo', 'Notificação de proximidade do prazo'),
('PRAZO_EXPIRADO', 'Prazo Expirado', 'Notificação de prazo expirado'),
('STATUS_ALTERADO', 'Status Alterado', 'Notificação de mudança de status'),
('DOCUMENTO_PENDENTE', 'Documento Pendente', 'Notificação de documento aguardando análise');

-- Inserir tipos de documento padrão
INSERT INTO `tipo_documento` (`codigo`, `nome`, `extensoes_permitidas`, `tamanho_maximo_mb`) VALUES
('PROJETO_BASICO', 'Projeto Básico', 'pdf,doc,docx', 50),
('PROJETO_EXECUTIVO', 'Projeto Executivo', 'pdf,dwg,doc,docx', 100),
('ORCAMENTO', 'Planilha Orçamentária', 'xls,xlsx,pdf', 20),
('CRONOGRAMA', 'Cronograma Físico-Financeiro', 'xls,xlsx,pdf,mpp', 20),
('ART_RRT', 'ART/RRT', 'pdf', 10),
('LICENCA_AMBIENTAL', 'Licença Ambiental', 'pdf', 10),
('MEMORIAL_DESCRITIVO', 'Memorial Descritivo', 'pdf,doc,docx', 30);

-- Continua a inserção de módulos padrão (linhas que ficaram cortadas)
INSERT INTO `modulo` (`nome`, `tipo`, `descricao`, `icone`, `cor`) VALUES
('Assinatura Conjunta', 'ASSINATURA', 'Módulo para múltiplas assinaturas em cadeia ou paralelo', 'fa-people-arrows', '#6610f2'),
('Validação de Orçamento', 'ANALISE', 'Rotina de checagem de planilhas orçamentárias', 'fa-file-invoice-dollar', '#20c997'),
('Validação Ambiental', 'ANALISE', 'Validação automática de documentos ambientais', 'fa-leaf', '#198754'),
('Emissão de Relatórios', 'ENVIO', 'Geração de relatórios consolidados da obra', 'fa-file-pdf', '#0dcaf0');

-- =====================================================================================
--  CAMPOS DE AUDITORIA PADRÃO (caso precise incluir em tabelas legadas)
--  Exemplo de como adicionar em qualquer tabela que ainda não possua:
-- =====================================================================================
/*
ALTER TABLE <nome_da_tabela>
  ADD COLUMN `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  ADD COLUMN `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
  ADD COLUMN `created_by`  INT NULL COMMENT 'ID do usuário que criou',
  ADD COLUMN `updated_by`  INT NULL COMMENT 'ID do usuário que atualizou';
*/

-- =====================================================================================
--  AJUSTE DE CAMPOS MONETÁRIOS:
--  Trocar VARCHAR por DECIMAL(15,2) se ainda houver valores financeiros como texto.
--  Exemplo (caso precise):
/*
ALTER TABLE acao 
  MODIFY COLUMN valor_estimado   DECIMAL(15,2) NULL,
  MODIFY COLUMN valor_contratado DECIMAL(15,2) NULL,
  MODIFY COLUMN valor_executado  DECIMAL(15,2) NULL;
*/
--  (Repita para outras tabelas que contenham valores numéricos em VARCHAR.)
-- =====================================================================================

-- -----------------------------------------------------------------
--  VIEWS / ÍNDICES ADICIONAIS (opcional)
--  Crie aqui materializações ou índices que acelerem relatórios.
-- -----------------------------------------------------------------
/*
CREATE VIEW vw_acoes_andamento AS
SELECT a.id,
       a.nome,
       a.valor_estimado,
       a.percentual_execucao,
       s.nome AS status_atual
  FROM acao a
  JOIN execucao_etapa ee ON ee.acao_id = a.id
  JOIN status s         ON s.id       = ee.status_id
 WHERE a.status = 'EM_EXECUCAO';
*/

-- -----------------------------------------------------
--  ENCERRAMENTO DO SCRIPT
-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- FIM: Portal de Obras Estaduais – Versão 7.0 Completa
