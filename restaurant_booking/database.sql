-- ============================================================
-- Project: Restaurant Booking  |  DB: restaurant_booking_db
-- Run this file once in phpMyAdmin or:
--   mysql -u root < database.sql
-- ============================================================

DROP DATABASE IF EXISTS restaurant_booking_db;
CREATE DATABASE restaurant_booking_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_booking_db;

-- ─── USERS ──────────────────────────────────────────────────
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  roles_json    VARCHAR(255) NOT NULL DEFAULT '["ROLE_USER"]',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── TIME SLOTS ─────────────────────────────────────────────
CREATE TABLE time_slots (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label        VARCHAR(80) NOT NULL,
  start_time   TIME NOT NULL,
  end_time     TIME NOT NULL,
  capacity_max INT UNSIGNED NOT NULL DEFAULT 30,
  is_active    TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ─── RESERVATIONS ───────────────────────────────────────────
CREATE TABLE reservations (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  slot_id    INT UNSIGNED NOT NULL,
  date       DATE NOT NULL,
  guests     TINYINT UNSIGNED NOT NULL,
  phone      VARCHAR(30) NOT NULL,
  notes      TEXT,
  status     ENUM('CONFIRMED','CANCELLED') NOT NULL DEFAULT 'CONFIRMED',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (slot_id) REFERENCES time_slots(id)  ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ─── DEMO ACCOUNTS ─────────────────────────────────────────
-- admin@test.com / admin123
-- user1@test.com / user123
-- user2@test.com / user123
-- Generate hashes: php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
INSERT INTO users (email, password_hash, roles_json) VALUES
  ('admin@test.com', '$2y$12$G/ozOfpxjPmzcVQbzqT/FeBnHOwGEXCUN7LZG7GYR9HrbcQTRK/AW', '["ROLE_ADMIN","ROLE_USER"]'),
  ('user1@test.com',  '$2y$12$kbo52cV4DlBUgC1MbdlXeu.LyJcYWVwKp/msBlDz6V7qj6iAbt8ri', '["ROLE_USER"]'),
  ('user2@test.com',  '$2y$12$kbo52cV4DlBUgC1MbdlXeu.LyJcYWVwKp/msBlDz6V7qj6iAbt8ri', '["ROLE_USER"]');

-- ─── DEMO TIME SLOTS ───────────────────────────────────────
INSERT INTO time_slots (label, start_time, end_time, capacity_max, is_active) VALUES
  ('Petit-déjeuner',  '08:00:00', '10:00:00', 20, 1),
  ('Déjeuner – 1er service',  '12:00:00', '13:00:00', 30, 1),
  ('Déjeuner – 2e service',   '13:00:00', '14:30:00', 30, 1),
  ('Dîner – 1er service',     '19:00:00', '20:30:00', 25, 1),
  ('Dîner – 2e service',      '20:30:00', '22:00:00', 25, 1),
  ('Brunch Dimanche',          '10:30:00', '14:00:00', 35, 1);

-- ─── DEMO RESERVATIONS ─────────────────────────────────────
INSERT INTO reservations (user_id, slot_id, date, guests, phone, notes, status) VALUES
  (2, 2, '2026-03-20', 4, '+212 600 123 456', 'Table près de la fenêtre svp', 'CONFIRMED'),
  (2, 4, '2026-03-21', 2, '+212 600 123 456', NULL, 'CONFIRMED'),
  (3, 3, '2026-03-20', 6, '+212 611 789 012', 'Anniversaire – gâteau prévu', 'CONFIRMED'),
  (3, 5, '2026-03-22', 3, '+212 611 789 012', 'Allergies : noix', 'CONFIRMED'),
  (2, 5, '2026-03-19', 2, '+212 600 123 456', NULL, 'CANCELLED'),
  (3, 1, '2026-03-23', 2, '+212 611 789 012', 'Petit-déj business', 'CONFIRMED');
