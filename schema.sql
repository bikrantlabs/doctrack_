CREATE TABLE users
(
    id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(255) UNIQUE NOT NULL,
    fullname   VARCHAR(255)        NOT NULL,
    password   VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE projects
(
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_projects
(
    id         BIGINT AUTO_INCREMENT PRIMARY KEY,

    user_id    BIGINT                                         NOT NULL,
    project_id BIGINT                                         NOT NULL,

    role       ENUM ("owner", "reviewer", "editor", "viewer") NOT NULL,

    UNIQUE KEY uniq_user_project (user_id, project_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);


CREATE TABLE documents
(
    id                 BIGINT PRIMARY KEY,
    project_id         BIGINT NOT NULL,
    title              VARCHAR(255),

    created_by         BIGINT NOT NULL,
    current_version_id BIGINT NOT NULL,

    FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE,

    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE document_versions
(
    ID             BIGINT PRIMARY KEY,
    document_id    BIGINT               NOT NULL,
    version_number INT                  NOT NULL,
    file_path      VARCHAR(500)         NOT NULL,
    file_type      ENUM ("pdf", "docx") NOT NULL,
    uploaded_by    BIGINT               NOT NULL,
    change_summary TEXT,
    status         ENUM ("draft", "under_review", "approved"),
    is_locked      BOOLEAN   DEFAULT FALSE,

    UNIQUE (document_id, version_number),
    FOREIGN KEY (document_id) REFERENCES documents (id),
    FOREIGN KEY (uploaded_by) REFERENCES users (id),

    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE review_threads
(
    id          BIGINT PRIMARY KEY,
    document_id BIGINT NOT NULL,
    created_by  BIGINT NOT NULL,
    title       VARCHAR(255),

    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents (id),
    FOREIGN KEY (created_by) REFERENCES users (id)

);

CREATE TABLE review_comments
(
    id                  BIGINT PRIMARY KEY,
    review_thread_id    BIGINT NOT NULL,
    document_version_id BIGINT NOT NULL,
    reviewer_id         BIGINT NOT NULL,
    page_number         INT    NOT NULL,
    comment             TEXT   NOT NULL,
    created_at          TIMESTAMP,

    FOREIGN KEY (review_thread_id) REFERENCES review_threads (id),
    FOREIGN KEY (document_version_id) REFERENCES document_versions (id),
    FOREIGN KEY (reviewer_id) REFERENCES users (id)
);

CREATE TABLE review_status
(
    review_thread_id    BIGINT,
    document_version_id BIGINT,
    status              ENUM ("open","resolved") NOT NULL,
    resolved_by         BIGINT                   NULL,
    resolved_at         TIMESTAMP                NULL,

    PRIMARY KEY (review_thread_id, document_version_id),
    FOREIGN KEY (review_thread_id) REFERENCES review_threads (id),
    FOREIGN KEY (document_version_id) REFERENCES document_versions (id),
    FOREIGN KEY (resolved_by) REFERENCES users (id)
);

CREATE TABLE project_invitations
(
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,

    project_id      BIGINT                                   NOT NULL,
    invited_user_id BIGINT                                   NOT NULL,
    invited_by      BIGINT                                   NOT NULL,

    role            ENUM ("reviewer", "editor", "viewer")    NOT NULL,
    status          ENUM ("pending", "accepted", "rejected") NOT NULL DEFAULT "pending",

    UNIQUE KEY uniq_project_invitation (project_id, invited_user_id),
    FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    FOREIGN KEY (invited_user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users (id) ON DELETE CASCADE,

    created_at      TIMESTAMP                                         DEFAULT CURRENT_TIMESTAMP
);