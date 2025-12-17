CREATE DATABASE IF NOT EXISTS financas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE financas;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  api_key VARCHAR(64) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  type ENUM('entrada','saida','extra','investimento') NOT NULL,
  category VARCHAR(100) DEFAULT NULL,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE goals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category VARCHAR(100),
  amount DECIMAL(10,2) NOT NULL,
  month CHAR(7) NOT NULL
);

ALTER TABLE transactions ADD COLUMN goal_id INT NULL;

INSERT INTO categories (name) VALUES
('Alimentação'),('Transporte'),('Moradia'),('Lazer'),('Salário'),('Investimentos'),('Outros');

INSERT INTO users (name,email,password) VALUES
('Admin','admin@local','$2b$12$euLqMjGKtNByfCwbWC69P.K5.pt1deIc8dK8O1b6fasE8w83v7GGK');