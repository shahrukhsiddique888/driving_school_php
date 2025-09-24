-- ================================================
-- Database: origin_driving_school
-- ================================================
DROP DATABASE IF EXISTS origin_driving_school;
CREATE DATABASE origin_driving_school;
USE origin_driving_school;

-- ================================================
-- Users
-- ================================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','instructor','admin') DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dummy users (password = "password" hashed with bcrypt)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@drivingschool.com', '$2y$10$7iLpn3nRcP7Ff6cV8oSeN.3Z2KphmXhF3hQyFydF2iY8d1ShW7h5a', 'admin'),
('Emily Davis', 'emily@student.com', '$2y$10$2QxyloGZ3bqIVo4n.6Ai4OPaM3AlErXjRITqYjM7VgFFoUy0dDq1C', 'student'),
('John Smith', 'john@drivingschool.com', '$2y$10$2QxyloGZ3bqIVo4n.6Ai4OPaM3AlErXjRITqYjM7VgFFoUy0dDq1C', 'instructor');

-- ================================================
-- Students
-- ================================================
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  phone VARCHAR(30),
  license_status ENUM('none','learner','provisional','full') DEFAULT 'none',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO students (user_id, phone, license_status) VALUES
(2, '0400123456', 'learner');

-- ================================================
-- Instructors
-- ================================================
CREATE TABLE instructors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  specialty VARCHAR(120),
  availability TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO instructors (user_id, specialty, availability) VALUES
(3, 'Automatic Transmission', 'Mon-Fri 9am-5pm');

-- ================================================
-- Vehicles
-- ================================================
CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  make VARCHAR(80) NOT NULL,
  model VARCHAR(80) NOT NULL,
  year YEAR NOT NULL,
  transmission ENUM('automatic','manual') DEFAULT 'automatic',
  rego VARCHAR(20) UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO vehicles (make, model, year, transmission, rego) VALUES
('Toyota', 'Corolla', 2022, 'automatic', 'ABC123');

-- ================================================
-- Courses
-- ================================================
CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  description TEXT,
  duration VARCHAR(50),
  price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO courses (title, description, duration, price) VALUES
('Beginner Driving Lessons', '5 lessons for first-time drivers.', '5 Lessons', 250.00),
('Test Preparation', 'Mock tests and feedback sessions.', '3 Lessons', 150.00),
('Advanced Driving', 'Highway and defensive driving techniques.', '4 Lessons', 220.00);

-- ================================================
-- Schedule
-- ================================================
CREATE TABLE schedule (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  instructor_id INT NOT NULL,
  vehicle_id INT,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status ENUM('booked','completed','cancelled') DEFAULT 'booked',
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

-- Dummy schedule record
INSERT INTO schedule (student_id, instructor_id, vehicle_id, start_time, end_time, status) VALUES
(1, 1, 1, '2025-10-01 10:00:00', '2025-10-01 11:00:00', 'booked');

-- ================================================
-- Reservations
-- ================================================
CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_name VARCHAR(100) NOT NULL,
  pickup VARCHAR(150) NOT NULL,
  dropoff VARCHAR(150) NOT NULL,
  date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO reservations (student_name, pickup, dropoff, date) VALUES
('Emily Davis', 'Sydney CBD', 'Bondi Beach', '2025-10-05');

-- ================================================
-- Invoices
-- ================================================
CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('draft','sent','paid','overdue') DEFAULT 'draft',
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  description VARCHAR(255) NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

INSERT INTO invoices (student_id, issue_date, due_date, total, status) VALUES
(1, '2025-09-20', '2025-09-30', 250.00, 'sent');

INSERT INTO invoice_items (invoice_id, description, qty, unit_price) VALUES
(1, 'Beginner Driving Lessons (5 Lessons)', 1, 250.00);

-- ================================================
-- Testimonials
-- ================================================
CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_name VARCHAR(100),
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO testimonials (student_name, message) VALUES
('Emily Davis', 'I passed my test on the first try thanks to their patient instructors!'),
('Michael Brown', 'Booking lessons online was super easy.'),
('Sophia Lee', 'The cars are modern and safe. Loved the experience!');
