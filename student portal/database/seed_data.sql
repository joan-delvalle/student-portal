-- Sample data for testing

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, first_name, last_name) VALUES
('admin', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator');

-- Insert sample subjects
INSERT INTO subjects (name, code, description) VALUES
('Mathematics', 'MATH101', 'Basic Mathematics Course'),
('English Literature', 'ENG101', 'Introduction to English Literature'),
('Computer Science', 'CS101', 'Introduction to Programming'),
('Physics', 'PHY101', 'Basic Physics Concepts'),
('Chemistry', 'CHEM101', 'Introduction to Chemistry');

-- Insert sample teachers (password: teacher123)
INSERT INTO users (username, email, password, role, first_name, last_name, phone) VALUES
('john_doe', 'john.doe@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'John', 'Doe', '555-0101'),
('jane_smith', 'jane.smith@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Jane', 'Smith', '555-0102');

-- Insert sample students (password: student123)
INSERT INTO users (username, email, password, role, first_name, last_name, date_of_birth) VALUES
('alice_johnson', 'alice.johnson@student.school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Alice', 'Johnson', '2005-03-15'),
('bob_wilson', 'bob.wilson@student.school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Bob', 'Wilson', '2005-07-22'),
('carol_brown', 'carol.brown@student.school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Carol', 'Brown', '2005-11-08');

-- Insert sample classes
INSERT INTO classes (name, subject_id, teacher_id, academic_year, semester) VALUES
('Math 101 - Section A', 1, 2, '2024-2025', '1'),
('English Lit 101', 2, 3, '2024-2025', '1'),
('Intro to Programming', 3, 2, '2024-2025', '1');

-- Insert sample enrollments
INSERT INTO enrollments (student_id, class_id) VALUES
(4, 1), (4, 2), (4, 3),
(5, 1), (5, 3),
(6, 2), (6, 3);
