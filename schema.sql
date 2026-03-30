CREATE DATABASE IF NOT EXISTS dissertation_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dissertation_system;

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS supervisors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS dissertations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  version INT NOT NULL,
  status ENUM('Pending', 'Under Review', 'Needs Revision', 'Approved') DEFAULT 'Pending',
  comment TEXT,
  upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dissertations_students FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

INSERT INTO students (name, password)
VALUES
  ('ali', SHA2('1234', 256)),
  ('sara', SHA2('1234', 256))
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO supervisors (name, password)
VALUES
  ('dr_ahmed', SHA2('admin', 256))
ON DUPLICATE KEY UPDATE name = VALUES(name);
