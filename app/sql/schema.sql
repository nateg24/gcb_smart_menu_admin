CREATE DATABASE IF NOT EXISTS brew_menu;
USE brew_menu;

CREATE TABLE IF NOT EXISTS menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  version INT NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  style VARCHAR(100) NULL,
  abv DECIMAL(4,2) NULL,
  price DECIMAL(6,2) NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

INSERT INTO menus (name) VALUES ('Main Menu');
SET @menu_id = LAST_INSERT_ID();

INSERT INTO menu_items (menu_id, name, style, abv, price, is_available, sort_order) VALUES
(@menu_id, 'Hazy IPA', 'IPA', 6.50, 7.00, 1, 1),
(@menu_id, 'Pilsner', 'Lager', 5.00, 6.00, 1, 2),
(@menu_id, 'Stout', 'Stout', 7.20, 7.50, 1, 3);
