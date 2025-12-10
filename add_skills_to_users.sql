-- Add skills column to users table for Skill Match Score feature
-- Run this in phpMyAdmin after importing the main database

USE jobxchange;

-- Add skills column to users table (for candidates)
ALTER TABLE users 
ADD COLUMN skills TEXT NULL AFTER role;

-- Update admin user to have NULL skills (not needed for admin)
UPDATE users SET skills = NULL WHERE role = 'admin';

-- You can optionally add sample skills to existing candidates for testing:
-- UPDATE users SET skills = 'HTML, CSS, JavaScript, PHP, MySQL' WHERE role = 'candidate' AND user_id = 2;
-- UPDATE users SET skills = 'Python, Django, REST API, PostgreSQL' WHERE role = 'candidate' AND user_id = 3;

SELECT 'Skills column added successfully to users table!' AS Status;

