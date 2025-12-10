-- Skill Exchange Tables for JobXchange Platform
-- Run this script to add the Skill Exchange feature to your database

USE jobxchange;

-- Table for skill exchange posts
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

-- Table for responses to skill exchange requests
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

