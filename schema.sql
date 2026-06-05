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

CREATE TABLE pages (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    title             VARCHAR(255) NOT NULL,
    slug              VARCHAR(255) NOT NULL UNIQUE,
    status            ENUM('published', 'draft') NOT NULL DEFAULT 'published',
    template          ENUM('standard', 'contact') NOT NULL DEFAULT 'standard',
    nav_label         VARCHAR(255) NULL,
    show_in_nav       TINYINT(1) NOT NULL DEFAULT 0,
    meta_title        VARCHAR(255) NULL,
    meta_description  VARCHAR(320) NULL,
    og_title          VARCHAR(255) NULL,
    og_description    VARCHAR(320) NULL,
    og_image          VARCHAR(500) NULL,
    sort_order        INT DEFAULT 0,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE page_sections (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    page_id     INT NOT NULL,
    heading     VARCHAR(255) NULL,
    content     TEXT NOT NULL,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
);

INSERT INTO pages
    (title, slug, status, template, nav_label, show_in_nav, meta_title, meta_description, og_title, og_description, sort_order)
VALUES
    ('Bio', 'bio', 'published', 'standard', 'Bio', 1, 'Bio — Fornesus Art', 'Biography and background from the Fornesus Art archive.', 'Bio — Fornesus Art', 'Biography and background from the Fornesus Art archive.', 0),
    ('Contact', 'contact', 'published', 'contact', 'Contact', 1, 'Contact — Fornesus Art', 'Send a message to Fornesus Art through the archive contact page.', 'Contact — Fornesus Art', 'Send a message to Fornesus Art through the archive contact page.', 1);

CREATE TABLE contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    message     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE media_files (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    data       LONGBLOB NULL,
    mime_type  VARCHAR(50) NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_deleted (deleted_at)
);
