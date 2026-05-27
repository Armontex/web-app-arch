CREATE DATABASE IF NOT EXISTS boardy_api
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS boardy_api.comments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    author_name VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_post_id (post_id),
    INDEX idx_author_id (author_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO boardy_api.comments (
    id,
    post_id,
    author_id,
    author_name,
    body,
    created_at,
    updated_at
)
SELECT
    comments.id,
    comments.post_id,
    comments.user_id,
    users.name,
    comments.body,
    comments.created_at,
    comments.updated_at
FROM boardy_main.comments
JOIN boardy_main.users ON users.id = comments.user_id
ON DUPLICATE KEY UPDATE
    post_id = VALUES(post_id),
    author_id = VALUES(author_id),
    author_name = VALUES(author_name),
    body = VALUES(body),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

DROP TABLE boardy_main.comments;
