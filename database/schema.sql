USE pro88407_alsap;

CREATE TABLE IF NOT EXISTS endereco (
    id BIGINT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) NULL,
    cep VARCHAR(255) NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS narrativa (
    id BIGINT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) NULL,
    idade VARCHAR(255) NULL,
    sexo VARCHAR(255) NULL,
    escolaridade VARCHAR(255) NULL,
    local VARCHAR(255) NULL,
    transcricao TEXT NULL,
    titulo VARCHAR(255) NULL,
    PRIMARY KEY (id),
    KEY idx_narrativa_local (local)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questao (
    id BIGINT NOT NULL AUTO_INCREMENT,
    descricao VARCHAR(255) NULL,
    tipo VARCHAR(255) NULL,
    PRIMARY KEY (id),
    KEY idx_questao_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resposta (
    id BIGINT NOT NULL AUTO_INCREMENT,
    endereco_id BIGINT NOT NULL,
    questao_id BIGINT NOT NULL,
    PRIMARY KEY (id),
    KEY idx_resposta_endereco (endereco_id),
    KEY idx_resposta_questao (questao_id),
    CONSTRAINT fk_resposta_endereco FOREIGN KEY (endereco_id)
        REFERENCES endereco (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_resposta_questao FOREIGN KEY (questao_id)
        REFERENCES questao (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audio (
    id BIGINT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) NULL,
    transcricao VARCHAR(255) NULL,
    audio_url VARCHAR(255) NULL,
    tipo INT NOT NULL DEFAULT 0,
    resposta BIGINT NULL,
    narrativa BIGINT NULL,
    PRIMARY KEY (id),
    KEY idx_audio_resposta (resposta),
    KEY idx_audio_narrativa (narrativa),
    CONSTRAINT fk_audio_resposta FOREIGN KEY (resposta)
        REFERENCES resposta (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_audio_narrativa FOREIGN KEY (narrativa)
        REFERENCES narrativa (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

