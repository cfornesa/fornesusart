CREATE TABLE categories (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL UNIQUE,
    thumbnail_type  ENUM('upload','link') NULL,
    thumbnail_value VARCHAR(500)          NULL,
    description     TEXT                  NULL,
    sort_order      INT DEFAULT 0,
    deleted_at      TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE artworks (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    category_id      INT NULL,
    title            VARCHAR(255) NOT NULL,
    slug             VARCHAR(255) NOT NULL UNIQUE,
    year             VARCHAR(10),
    description      TEXT,
    thumbnail_type   ENUM('upload','link') NULL DEFAULT NULL,
    thumbnail_value  VARCHAR(500)          NULL DEFAULT NULL,
    piece_type       ENUM('image_upload','image_link','embed') NOT NULL,
    piece_value      TEXT NOT NULL,
    sort_order       INT DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at       TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE exhibits (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL UNIQUE,
    description     TEXT,
    thumbnail_type  ENUM('upload','link') NULL,
    thumbnail_value VARCHAR(500)          NULL,
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE exhibit_artworks (
    exhibit_id  INT NOT NULL,
    artwork_id  INT NOT NULL,
    sort_order  INT DEFAULT 0,
    PRIMARY KEY (exhibit_id, artwork_id),
    FOREIGN KEY (exhibit_id) REFERENCES exhibits(id)  ON DELETE CASCADE,
    FOREIGN KEY (artwork_id) REFERENCES artworks(id)  ON DELETE CASCADE
);

CREATE TABLE bio_sections (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    heading     VARCHAR(255),
    content     TEXT NOT NULL,
    sort_order  INT DEFAULT 0
);

CREATE TABLE contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    message     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE media_files (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    path       VARCHAR(500) NOT NULL,
    subfolder  VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_deleted (deleted_at)
);
