-- University Project Management System - MySQL schema
-- Run this file in MySQL Workbench or phpMyAdmin SQL tab.

CREATE DATABASE IF NOT EXISTS university_project_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE university_project_management;

-- Drop children first when re-running locally
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student', 'supervisor') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  department VARCHAR(120) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  feedback TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_projects_student
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_projects_student (student_id),
  INDEX idx_projects_status (status),
  INDEX idx_projects_created_at (created_at)
);

-- Demo users (password hashes are SHA2 for simple local demo)
INSERT INTO users (name, email, password_hash, role) VALUES
  ('ali', 'ali@student.local', SHA2('1234', 256), 'student'),
  ('sara', 'sara@student.local', SHA2('1234', 256), 'student'),
  ('dr_ahmed', 'dr_ahmed@supervisor.local', SHA2('admin', 256), 'supervisor');
