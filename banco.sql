CREATE TABLE FRASE_produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE FRASE_categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE FRASE_mensagens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    texto TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE FRASE_produtos_categorias (
    produto_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (produto_id, categoria_id),
    CONSTRAINT fk_produtos_categorias_produto
        FOREIGN KEY (produto_id) REFERENCES FRASE_produtos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_produtos_categorias_categoria
        FOREIGN KEY (categoria_id) REFERENCES FRASE_categorias(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE FRASE_mensagens_categorias (
    mensagem_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (mensagem_id, categoria_id),
    CONSTRAINT fk_mensagens_categorias_mensagem
        FOREIGN KEY (mensagem_id) REFERENCES FRASE_mensagens(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_mensagens_categorias_categoria
        FOREIGN KEY (categoria_id) REFERENCES FRASE_categorias(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
