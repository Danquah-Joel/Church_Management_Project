-- ============================================================
--  Church of Pentecost — Service Register Database Schema
--  Engine: MySQL 5.7+ / MariaDB 10.3+
--  Charset: utf8mb4 (full Unicode + emoji support)
-- ============================================================

CREATE DATABASE IF NOT EXISTS church_register
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE church_register;

-- ------------------------------------------------------------
-- Table 1: attendance_records
--   One row per service/Sunday recorded in the register.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance_records (
  id                INT UNSIGNED      NOT NULL AUTO_INCREMENT,

  -- Service info
  service_date      DATE              NOT NULL,
  service_type      VARCHAR(100)      NOT NULL DEFAULT '',
  service_time      VARCHAR(20)       NOT NULL DEFAULT '',
  minister          VARCHAR(150)      NOT NULL DEFAULT '',

  -- Congregation headcount
  cnt_apostles      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_apostles_wife SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_pastors       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_pastors_wife  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_elders        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_dcn           SMALLINT UNSIGNED NOT NULL DEFAULT 0,   -- Deacons
  cnt_dcns          SMALLINT UNSIGNED NOT NULL DEFAULT 0,   -- Deaconesses
  cnt_men           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_women         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_youth         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_children      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_visitors      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_new_converts  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cnt_guests        SMALLINT UNSIGNED NOT NULL DEFAULT 0,

  -- Computed totals (stored for fast reporting; recalculated on save)
  adult_total       SMALLINT UNSIGNED NOT NULL DEFAULT 0,   -- excl. children
  grand_total       SMALLINT UNSIGNED NOT NULL DEFAULT 0,   -- all attendees
  officers_total    SMALLINT UNSIGNED NOT NULL DEFAULT 0,   -- elders+dcn+dcns

  -- Financials
  offering_amount   DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
  tithe_amount      DECIMAL(10,2)     NOT NULL DEFAULT 0.00,

  -- Communion participants
  com_officers      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  com_male          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  com_female        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  communion_total   SMALLINT UNSIGNED NOT NULL DEFAULT 0,

  -- Bible study participants
  bs_male           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  bs_female         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  bs_total          SMALLINT UNSIGNED NOT NULL DEFAULT 0,

  -- Free-text fields
  activities        TEXT              NOT NULL DEFAULT '',
  prayer_request    TEXT              NOT NULL DEFAULT '',
  notes             TEXT              NOT NULL DEFAULT '',

  -- Audit
  created_at        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                      ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_service_date (service_date),
  INDEX idx_service_type (service_type)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- Table 2: attendance_others
--   Individual Guest / Visitor / New Convert entries linked
--   to a parent attendance_record (one-to-many).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance_others (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  record_id     INT UNSIGNED  NOT NULL,               -- FK → attendance_records.id
  category      ENUM('Guest','Visitor','New Convert') NOT NULL,
  first_name    VARCHAR(100)  NOT NULL DEFAULT '',
  second_name   VARCHAR(100)  NOT NULL DEFAULT '',
  location      VARCHAR(150)  NOT NULL DEFAULT '',
  phone         VARCHAR(30)   NOT NULL DEFAULT '',
  status        VARCHAR(50)   NOT NULL DEFAULT '',     -- Elder / Deacon / Deaconess / Pastor / etc.

  PRIMARY KEY (id),
  INDEX idx_record_id (record_id),
  CONSTRAINT fk_others_record
    FOREIGN KEY (record_id)
    REFERENCES attendance_records (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- Table 3: users  (for check_session.php / login)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  username      VARCHAR(80)   NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,               -- bcrypt via password_hash()
  full_name     VARCHAR(150)  NOT NULL DEFAULT '',
  role          ENUM('admin','recorder') NOT NULL DEFAULT 'recorder',
  created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Default admin account  (password: Admin@1234 — CHANGE IMMEDIATELY after setup)
INSERT IGNORE INTO users (username, password_hash, full_name, role)
VALUES (
  'admin',
  '$2y$12$placeholder_hash_replace_me',   -- replace with: password_hash('Admin@1234', PASSWORD_BCRYPT)
  'Administrator',
  'admin'
);
