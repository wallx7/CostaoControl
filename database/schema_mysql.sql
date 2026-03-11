-- Schema do Banco (MySQL/MariaDB — CAMP/XAMPP)

CREATE TABLE IF NOT EXISTS usuarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  papel VARCHAR(32) NOT NULL DEFAULT 'admin',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS equipamentos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  tipo VARCHAR(100),
  marca VARCHAR(100),
  modelo VARCHAR(100),
  especificacoes TEXT,
  fornecedor VARCHAR(150),
  numero_serie VARCHAR(150),
  patrimonio VARCHAR(150),
  departamento VARCHAR(150),
  localizacao VARCHAR(150),
  status VARCHAR(50) DEFAULT 'Em uso',
  data_aquisicao DATE NULL,
  garantia_fim DATE NULL,
  valor DECIMAL(10,2) DEFAULT 0,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  icon VARCHAR(150),
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_atividades (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NULL,
  acao VARCHAR(50) NOT NULL,
  entidade VARCHAR(50),
  entidade_id BIGINT UNSIGNED NULL,
  mensagem TEXT,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_usuario (usuario_id),
  CONSTRAINT fk_logs_usuario_id FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS termos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(50) NOT NULL,
  equipamento_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  usuario_responsavel VARCHAR(255) NULL,
  observacoes TEXT NULL,
  assinado TINYINT(1) NOT NULL DEFAULT 0,
  codigo VARCHAR(50) NULL,
  data_geracao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  assinado_em TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_termos_equipamento (equipamento_id),
  CONSTRAINT fk_termos_equipamentos FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE CASCADE,
  CONSTRAINT fk_termos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS checklists (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  equipamento_id BIGINT UNSIGNED NOT NULL,
  tipo VARCHAR(50) NULL,
  itens_json JSON NULL,
  status_final VARCHAR(50) NULL,
  observacoes TEXT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_checklist_equip (equipamento_id),
  CONSTRAINT fk_checklists_equip FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anexos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  entidade VARCHAR(50) NOT NULL,
  entidade_id BIGINT UNSIGNED NOT NULL,
  caminho_arquivo TEXT NOT NULL,
  nome_original VARCHAR(255) NULL,
  tipo_mime VARCHAR(100) NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_anexos_entidade (entidade, entidade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS colaboradores (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NULL,
  cpf VARCHAR(20) NULL,
  departamento VARCHAR(150) NULL,
  cargo VARCHAR(150) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY idx_colaboradores_email (email),
  UNIQUE KEY idx_colaboradores_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionais
CREATE INDEX IF NOT EXISTS idx_equipamentos_nome ON equipamentos(nome);
CREATE INDEX IF NOT EXISTS idx_equipamentos_status ON equipamentos(status);

-- Seeds básicos
INSERT INTO usuarios (nome, email, senha_hash, papel)
SELECT 'Administrador', 'admin@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email='admin@empresa.com');

INSERT INTO categorias (nome, icon)
SELECT 'Desktop', 'solar:monitor-bold-duotone'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nome='Desktop');

INSERT INTO categorias (nome, icon)
SELECT 'Notebook', 'solar:monitor-smartphone-bold-duotone'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nome='Notebook');

INSERT INTO categorias (nome, icon)
SELECT 'Celular', 'solar:smartphone-2-bold-duotone'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nome='Celular');

