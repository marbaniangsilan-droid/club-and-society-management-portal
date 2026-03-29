-- 1. Users Table (With the role column for your Admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    department VARCHAR(100),
    password VARCHAR(255),
    role VARCHAR(20) DEFAULT 'student'
);

-- 2. Clubs Table
CREATE TABLE clubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_name VARCHAR(100),
    description TEXT
);

-- 3. Club Members Table (Links Users to Clubs)
CREATE TABLE club_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    club_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);

-- 4. Events Table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100),
    description TEXT
);

-- 5. Event Registration Table
CREATE TABLE event_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100),
    user_email VARCHAR(100)
);
-- 6. Notification to club members only
CREATE TABLE club_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);
-- 7. Noification to event attendies only
CREATE TABLE event_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- 8. Announcement
CREATE TABLE general_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);