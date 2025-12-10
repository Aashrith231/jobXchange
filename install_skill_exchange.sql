-- Quick Setup Script for Skill Exchange Feature
-- Just run this file in phpMyAdmin or MySQL command line

-- Use the jobxchange database
USE jobxchange;

-- Drop tables if they exist (optional - remove these lines if you want to keep existing data)
-- DROP TABLE IF EXISTS skill_exchange_responses;
-- DROP TABLE IF EXISTS skill_exchange;

-- Create skill_exchange table
CREATE TABLE IF NOT EXISTS skill_exchange (
    exchange_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    offer_skill VARCHAR(200) NOT NULL,
    request_skill VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'closed', 'in_progress') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create skill_exchange_responses table
CREATE TABLE IF NOT EXISTS skill_exchange_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    exchange_id INT NOT NULL,
    responder_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exchange_id) REFERENCES skill_exchange(exchange_id) ON DELETE CASCADE,
    FOREIGN KEY (responder_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_exchange_id (exchange_id),
    INDEX idx_responder_id (responder_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (optional - remove if you don't want sample data)
-- INSERT INTO skill_exchange (user_id, offer_skill, request_skill, description, status) VALUES
-- (2, 'Web Development (HTML, CSS, JavaScript)', 'Graphic Design & UI/UX', 'I have 3 years of experience in full-stack web development. Looking to learn graphic design to enhance my UI skills.', 'open'),
-- (3, 'Digital Marketing & SEO', 'Python Programming', 'Expert in SEO and content marketing. Want to learn Python for data analysis and automation.', 'open'),
-- (2, 'Database Management (MySQL, MongoDB)', 'Mobile App Development', 'Database administrator with 5 years experience. Interested in learning React Native or Flutter.', 'open');

-- Verify tables were created
SHOW TABLES LIKE 'skill_exchange%';

-- Show structure of created tables
DESCRIBE skill_exchange;
DESCRIBE skill_exchange_responses;

SELECT 'Skill Exchange tables created successfully!' AS Status;

