-- ============================================================
--  church_management_schema.sql
--  Database: church_management
--  Last updated: includes `location` column in members table
--
--  Tables:
--    users           — admin/login accounts
--    members         — church member records
--    family_members  — dependants linked to a member
-- ============================================================

CREATE DATABASE IF NOT EXISTS `church_management`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `church_management`;

-- ------------------------------------------------------------
-- TABLE: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `user_id`       INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(60)      NOT NULL UNIQUE,
    `email`         VARCHAR(120)     NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)     NOT NULL,
    `role`          ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE: members
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `members` (
    `member_id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,

    -- Identity
    `position`             VARCHAR(60)    NOT NULL,
    `first_name`           VARCHAR(80)    NOT NULL,
    `middle_name`          VARCHAR(80)    DEFAULT NULL,
    `surname`              VARCHAR(80)    NOT NULL,

    -- Contact
    `phone1`               VARCHAR(20)    NOT NULL,
    `phone2`               VARCHAR(20)    DEFAULT NULL,
    `emergency_contact`    VARCHAR(20)    NOT NULL,
    `email`                VARCHAR(120)   DEFAULT NULL,

    -- Personal
    `date_of_birth`        DATE           NOT NULL,
    `age`                  TINYINT UNSIGNED DEFAULT NULL,
    `gender`               ENUM('Male','Female','Other') NOT NULL,
    `hometown`             VARCHAR(100)   NOT NULL,
    `nationality`          VARCHAR(80)    NOT NULL,
    `occupation`           VARCHAR(100)   NOT NULL,
    `marital_status`       ENUM('Single','Married','Widowed','Divorced') DEFAULT NULL,

    -- Address  ← NEW: location is the town/city of residence
    `residential_address`  VARCHAR(255)   NOT NULL,
    `location`             VARCHAR(100)   NOT NULL
                           COMMENT 'Town / City of Residence (Personal Details)',

    -- Parents
    `mother_name`          VARCHAR(160)   DEFAULT NULL,
    `mother_status`        VARCHAR(40)    DEFAULT NULL,
    `father_name`          VARCHAR(160)   DEFAULT NULL,
    `father_status`        VARCHAR(40)    DEFAULT NULL,

    -- Church information
    `water_baptised`       ENUM('Yes','No') DEFAULT NULL,
    `water_baptism_date`   DATE           DEFAULT NULL,
    `holyspirit_baptised`  ENUM('Yes','No') DEFAULT NULL,
    `ministry`             VARCHAR(80)    DEFAULT NULL,
    `status`               VARCHAR(60)    DEFAULT NULL,
    `zone`                 VARCHAR(80)    DEFAULT NULL
                           COMMENT 'Location Group / Zone (Church Info)',
    `gps_address`          VARCHAR(60)    DEFAULT NULL,

    -- Spouse
    `spouse_name`          VARCHAR(160)   DEFAULT NULL,
    `spouse_phone`         VARCHAR(20)    DEFAULT NULL,
    `spouse_occupation`    VARCHAR(100)   DEFAULT NULL,
    `spouse_hometown`      VARCHAR(100)   DEFAULT NULL,
    `spouse_religion`      VARCHAR(80)    DEFAULT NULL,
    `place_of_worship`     VARCHAR(120)   DEFAULT NULL,

    -- Meta
    `created_by`           INT UNSIGNED   DEFAULT NULL,
    `created_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`member_id`),
    KEY `idx_status`        (`status`),
    KEY `idx_ministry`      (`ministry`),
    KEY `idx_zone`          (`zone`),
    KEY `idx_location`      (`location`),
    CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`)
        REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLE: family_members
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `family_members` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `member_id`     INT UNSIGNED   NOT NULL,
    `full_name`     VARCHAR(160)   NOT NULL,
    `age`           TINYINT UNSIGNED DEFAULT NULL,
    `relationship`  VARCHAR(60)    NOT NULL,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_member_id` (`member_id`),
    CONSTRAINT `fk_family_member`
        FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- MIGRATION: add `location` to an existing members table
-- Run this block ONLY if the table already exists without the column.
-- ------------------------------------------------------------
-- ALTER TABLE `members`
--     ADD COLUMN `location` VARCHAR(100) NOT NULL DEFAULT ''
--         COMMENT 'Town / City of Residence (Personal Details)'
--         AFTER `residential_address`,
--     ADD INDEX `idx_location` (`location`);
