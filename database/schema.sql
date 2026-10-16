-- Drogacare - schema MySQL 5.7+/8 e MariaDB 10.3+ (InnoDB, utf8mb4).
-- Idempotente: pode ser executado mais de uma vez (CREATE TABLE IF NOT EXISTS).

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  -- RBAC: níveis de acesso
  role ENUM('SUPERADMIN','EDITOR_CONTEUDO','VISUALIZADOR') NOT NULL DEFAULT 'VISUALIZADOR',
  active TINYINT(1) NOT NULL DEFAULT 1,
  -- Troca de senha obrigatória (ex.: admin inicial)
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  -- Recuperação de senha: guardamos apenas o HASH (sha256) do token enviado por e-mail
  reset_token CHAR(64) NULL,
  reset_token_expiry DATETIME NULL,
  -- Proteção contra força bruta
  failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role),
  KEY idx_users_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auditoria (LGPD): quem fez o quê e quando. user_id vira NULL se o usuário for excluído;
-- user_email mantém o "snapshot" para preservar a rastreabilidade.
CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  user_email VARCHAR(190) NULL,
  action VARCHAR(60) NOT NULL,
  entity VARCHAR(60) NOT NULL,
  entity_id VARCHAR(40) NULL,
  details VARCHAR(500) NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_created (created_at),
  KEY idx_audit_user (user_id),
  KEY idx_audit_entity (entity, entity_id),
  KEY idx_audit_ip_action (ip, action, created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  excerpt VARCHAR(300) NULL,
  content MEDIUMTEXT NOT NULL,
  cover_url VARCHAR(500) NULL,
  status ENUM('DRAFT','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
  -- SEO
  seo_title VARCHAR(70) NULL,
  seo_description VARCHAR(160) NULL,
  seo_keywords VARCHAR(200) NULL,
  og_image_url VARCHAR(500) NULL,
  published_at DATETIME NULL,
  author_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status (status, published_at),
  CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  excerpt VARCHAR(300) NULL,
  content MEDIUMTEXT NOT NULL,
  cover_url VARCHAR(500) NULL,
  -- 1 = aparece como cartão no feed da página inicial (depois do Contato)
  show_on_home TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('DRAFT','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
  seo_title VARCHAR(70) NULL,
  seo_description VARCHAR(160) NULL,
  seo_keywords VARCHAR(200) NULL,
  og_image_url VARCHAR(500) NULL,
  published_at DATETIME NULL,
  author_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pages_slug (slug),
  KEY idx_pages_status (status),
  KEY idx_pages_feed (status, show_on_home, published_at),
  CONSTRAINT fk_pages_author FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados globais da empresa: SINGLETON (sempre id = 1).
CREATE TABLE IF NOT EXISTS company_info (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  name VARCHAR(100) NOT NULL DEFAULT 'Drogacare',
  -- Logo: imagem enviada + o que mostrar no topo/rodapé (image = só imagem, name = só nome, both = os dois)
  logo_url VARCHAR(500) NOT NULL DEFAULT '',
  logo_display ENUM('both','image','name') NOT NULL DEFAULT 'both',
  tagline VARCHAR(120) NOT NULL DEFAULT 'Farmácia de Manipulação',
  -- Dados legais exibidos no rodapé
  legal_name VARCHAR(150) NOT NULL DEFAULT '',
  cnpj VARCHAR(18) NOT NULL DEFAULT '',
  legal_extra VARCHAR(300) NOT NULL DEFAULT '',
  founded_year SMALLINT UNSIGNED NOT NULL DEFAULT 1991,
  -- Farmacêutico Responsável
  pharmacist_name VARCHAR(120) NOT NULL DEFAULT '',
  pharmacist_crf VARCHAR(40) NOT NULL DEFAULT '',
  pharmacist_photo_url VARCHAR(500) NOT NULL DEFAULT '',
  -- Contato
  phone VARCHAR(40) NOT NULL DEFAULT '',
  whatsapp VARCHAR(20) NOT NULL DEFAULT '',
  email VARCHAR(120) NOT NULL DEFAULT '',
  address VARCHAR(250) NOT NULL DEFAULT '',
  maps_embed_url VARCHAR(500) NOT NULL DEFAULT '',
  -- Vídeo e redes sociais
  hero_video_url VARCHAR(500) NOT NULL DEFAULT '',
  youtube_url VARCHAR(500) NOT NULL DEFAULT '',
  instagram_url VARCHAR(500) NOT NULL DEFAULT '',
  facebook_url VARCHAR(500) NOT NULL DEFAULT '',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT chk_company_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Textos e imagens editáveis da página inicial (chave/valor). Sem linha = usa o texto padrão do código.
CREATE TABLE IF NOT EXISTS site_settings (
  skey VARCHAR(60) NOT NULL,
  svalue TEXT NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (skey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
