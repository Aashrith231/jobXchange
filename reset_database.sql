-- Reset Database Script
-- Use this to clear all data and start fresh during testing

USE jobxchange;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Truncate all tables (removes all data but keeps structure)
TRUNCATE TABLE applications;
TRUNCATE TABLE jobs;
TRUNCATE TABLE users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Re-insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@jobxchange.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Optionally insert some test data
-- Uncomment below to add sample users and jobs for testing

/*
-- Test Recruiter
INSERT INTO users (name, email, password, role) VALUES 
('John Recruiter', 'recruiter@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter');

-- Test Candidate
INSERT INTO users (name, email, password, role) VALUES 
('Jane Candidate', 'candidate@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'candidate');

-- Sample Job (recruiter_id = 2, assuming John Recruiter is ID 2)
INSERT INTO jobs (recruiter_id, title, description, skills, salary, type, status) VALUES 
(2, 'PHP Developer Internship', 'Looking for a talented PHP developer intern to join our team. You will work on real-world projects and gain valuable experience.', 'PHP, MySQL, HTML, CSS, JavaScript', '$1500 - $2000/month', 'internship', 'approved');

INSERT INTO jobs (recruiter_id, title, description, skills, salary, type, status) VALUES 
(2, 'Full Stack Developer', 'We are seeking an experienced full-stack developer. Must have strong knowledge of backend and frontend technologies.', 'PHP, JavaScript, React, Node.js, MySQL', '$50,000 - $70,000/year', 'full-time', 'approved');
*/

SELECT 'Database reset complete! Admin user recreated.' as Status;

