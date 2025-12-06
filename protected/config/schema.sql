-- ===========================================
-- USERS TABLE (Signup / Login)
-- ===========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================
-- WORKSHOPS TABLE (Admin creates workshops)
-- ===========================================
CREATE TABLE workshops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(150),
    capacity INT NOT NULL DEFAULT 0,
    seats_available INT NOT NULL DEFAULT 0,
    start_date DATE NOT NULL,
    start_time TIME DEFAULT NULL,
    location VARCHAR(255),
    description TEXT,
    image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ===========================================
--  ENROLLMENTS TABLE (Tracks workshop enrollments)
-- ===========================================
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workshop_id INT NOT NULL,
    seats INT NOT NULL DEFAULT 1,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
);
INSERT INTO users (full_name, email, password, role)
VALUES (
    'System Administrator',
    'admin@learningAura.com',
    -- password: admin123 hashed 
    '$2a$12$cpF.S67Lc7G5tpi.786CveFgqd036w9JEFShmINoz3/WJQRt7ItUq',
    'admin'
);




