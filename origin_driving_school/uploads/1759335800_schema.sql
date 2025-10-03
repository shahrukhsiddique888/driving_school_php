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
-- Branches
-- ================================================
CREATE TABLE branches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  address VARCHAR(255),
  phone VARCHAR(30),
  email VARCHAR(150),
  manager VARCHAR(120),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO branches (name, address, phone, email, manager) VALUES
('City HQ', '123 Queen St, Melbourne VIC', '03 9000 1111', 'city@origindrive.com', 'Samantha Lee'),
('Bay Side', '45 Marine Parade, St Kilda VIC', '03 9333 2222', 'bayside@origindrive.com', 'Marcus Finn');

-- ================================================
-- Students
-- ================================================
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  branch_id INT,
  phone VARCHAR(30),
  license_status ENUM('none','learner','provisional','full') DEFAULT 'none',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

INSERT INTO students (user_id, branch_id, phone, license_status) VALUES
(2, 1, '0400123456', 'learner');

-- ================================================
-- Instructors
-- ================================================
CREATE TABLE instructors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  branch_id INT,
  specialty VARCHAR(120),
  phone VARCHAR(30),
  availability TEXT,
  hourly_rate DECIMAL(10,2) DEFAULT 0,
  bio TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

INSERT INTO instructors (user_id, branch_id, specialty, phone, availability, hourly_rate, bio) VALUES
(3, 1, 'Automatic Transmission', '0400555123', 'Mon-Fri 9am-5pm', 65.00, 'Patient instructor specialising in auto vehicles.');

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
  branch_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

INSERT INTO vehicles (make, model, year, transmission, rego, branch_id) VALUES
('Toyota', 'Corolla', 2022, 'automatic', 'ABC123', 1),
('Hyundai', 'i30', 2023, 'manual', 'XYZ789', 2);

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
  branch_id INT,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status ENUM('booked','completed','cancelled') DEFAULT 'booked',
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Dummy schedule record
INSERT INTO schedule (student_id, instructor_id, vehicle_id, branch_id, start_time, end_time, status) VALUES
(1, 1, 1, 1, '2025-10-01 10:00:00', '2025-10-01 11:00:00', 'booked');

-- ================================================
-- Reservations
-- ================================================
CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_name VARCHAR(100) NOT NULL,
  pickup VARCHAR(150) NOT NULL,
  dropoff VARCHAR(150) NOT NULL,
  date DATE NOT NULL,
  branch_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO reservations (student_name, pickup, dropoff, date, branch_id) VALUES
('Emily Davis', 'Sydney CBD', 'Bondi Beach', '2025-10-05', 1);

-- ================================================
-- Invoices
-- ================================================
CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('draft','sent','partial','paid','overdue') DEFAULT 'draft',
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
-- Student Notes & Progress Tracking
-- ================================================
CREATE TABLE student_notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  created_by INT,
  note TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE student_progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  instructor_id INT,
  lesson_date DATE NOT NULL,
  skill_area VARCHAR(120),
  rating TINYINT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE SET NULL
);

INSERT INTO student_notes (student_id, created_by, note) VALUES
(1, 1, 'Great improvement with clutch control this week.'),
(1, 3, 'Needs more practise with parallel parking.');

INSERT INTO student_progress (student_id, instructor_id, lesson_date, skill_area, rating, notes) VALUES
(1, 1, '2025-09-18', 'Parking', 4, 'Successfully completed reverse parallel parking.'),
(1, 1, '2025-09-25', 'Road Rules', 3, 'Revise give-way rules at roundabouts.');

-- ================================================
-- Payments
-- ================================================
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('cash','card','bank_transfer','online') DEFAULT 'online',
  reference VARCHAR(120),
  paid_at DATETIME NOT NULL,
  notes TEXT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO payments (invoice_id, amount, method, reference, paid_at, created_by) VALUES
(1, 150.00, 'card', 'PAY-001', '2025-09-22 14:30:00', 1);

-- ================================================
-- Reminders & Notifications
-- ================================================
CREATE TABLE reminders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  invoice_id INT,
  schedule_id INT,
  reminder_type ENUM('payment','lesson','custom') DEFAULT 'custom',
  reminder_at DATETIME NOT NULL,
  message TEXT NOT NULL,
  sent_at DATETIME,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (schedule_id) REFERENCES schedule(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO reminders (user_id, invoice_id, reminder_type, reminder_at, message, created_by) VALUES
(2, 1, 'payment', '2025-09-27 09:00:00', 'Payment reminder for Beginner Driving Lessons invoice.', 1);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  link VARCHAR(255),
  read_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO notifications (user_id, title, message, link) VALUES
(2, 'Upcoming Lesson', 'You have a lesson scheduled on 1 Oct at 10:00 AM.', '/origin_driving_school/schedule.php');

CREATE TABLE communications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT,
  audience_type ENUM('student','instructor','staff','all') NOT NULL,
  target_user_id INT,
  channel ENUM('email','sms','in_app') DEFAULT 'in_app',
  subject VARCHAR(150),
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO communications (sender_id, audience_type, channel, subject, body) VALUES
(1, 'student', 'email', 'Welcome to Origin Driving School', 'We are excited to help you become a safe driver!');

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

CREATE TABLE cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ==========================================
-- User Files (Profile Pictures & Documents)
-- ==========================================
CREATE TABLE user_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  file_type ENUM('profile_pic','document') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
