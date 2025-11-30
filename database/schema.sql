-- Database Schema for Arisan System
-- Created for Sistem Arisan Digital

-- Create Database
CREATE DATABASE IF NOT EXISTS arisan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arisan_db;

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admin (username, password, name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Members Table
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Members Data
INSERT INTO members (name, phone, address) VALUES 
('Siti Aminah', '081234567890', 'Jl. Mawar No. 12, Jakarta'),
('Dewi Sartika', '081234567891', 'Jl. Melati No. 45, Jakarta'),
('Ratna Dewi', '081234567892', 'Jl. Anggrek No. 78, Jakarta'),
('Rina Susanti', '081234567893', 'Jl. Kenanga No. 23, Jakarta'),
('Sri Wahyuni', '081234567894', 'Jl. Dahlia No. 56, Jakarta'),
('Lestari Wati', '081234567895', 'Jl. Cempaka No. 89, Jakarta'),
('Nurul Hidayah', '081234567896', 'Jl. Tulip No. 34, Jakarta'),
('Fitri Handayani', '081234567897', 'Jl. Sakura No. 67, Jakarta'),
('Maya Sari', '081234567898', 'Jl. Lily No. 90, Jakarta'),
('Indah Permata', '081234567899', 'Jl. Orchid No. 12, Jakarta');

-- Arisan Periods Table
CREATE TABLE IF NOT EXISTS arisan_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_name VARCHAR(100) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    duration_months INT NOT NULL,
    status ENUM('active', 'completed', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Arisan Periods
INSERT INTO arisan_periods (period_name, amount, duration_months, status) VALUES 
('Arisan Bulanan Januari 2024', 500000, 12, 'active'),
('Arisan Bulanan Februari 2024', 300000, 10, 'active');

-- Winners Table
CREATE TABLE IF NOT EXISTS winners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arisan_id INT NOT NULL,
    member_id INT NOT NULL,
    draw_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (arisan_id) REFERENCES arisan_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_winner (arisan_id, member_id),
    INDEX idx_arisan (arisan_id),
    INDEX idx_member (member_id),
    INDEX idx_draw_date (draw_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    arisan_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    status ENUM('paid', 'pending', 'cancelled') DEFAULT 'paid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (arisan_id) REFERENCES arisan_periods(id) ON DELETE CASCADE,
    INDEX idx_member (member_id),
    INDEX idx_arisan (arisan_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Payments
INSERT INTO payments (member_id, arisan_id, amount, payment_date, status) VALUES 
(1, 1, 500000, '2024-01-15', 'paid'),
(2, 1, 500000, '2024-01-15', 'paid'),
(3, 1, 500000, '2024-01-16', 'paid'),
(4, 2, 300000, '2024-02-10', 'paid'),
(5, 2, 300000, '2024-02-10', 'paid');

-- Create Views for Easy Reporting

-- View: Member Statistics
CREATE OR REPLACE VIEW view_member_stats AS
SELECT 
    m.id,
    m.name,
    m.phone,
    COUNT(DISTINCT w.id) as total_wins,
    COUNT(DISTINCT p.id) as total_payments,
    COALESCE(SUM(p.amount), 0) as total_paid
FROM members m
LEFT JOIN winners w ON m.id = w.member_id
LEFT JOIN payments p ON m.id = p.member_id AND p.status = 'paid'
WHERE m.status = 'active'
GROUP BY m.id, m.name, m.phone;

-- View: Arisan Period Statistics
CREATE OR REPLACE VIEW view_arisan_stats AS
SELECT 
    a.id,
    a.period_name,
    a.amount,
    a.duration_months,
    a.status,
    COUNT(DISTINCT w.id) as total_winners,
    COUNT(DISTINCT p.id) as total_payments,
    COALESCE(SUM(p.amount), 0) as total_collected
FROM arisan_periods a
LEFT JOIN winners w ON a.id = w.arisan_id
LEFT JOIN payments p ON a.id = p.arisan_id AND p.status = 'paid'
GROUP BY a.id, a.period_name, a.amount, a.duration_months, a.status;

-- Indexes for Performance
CREATE INDEX idx_members_name ON members(name);
CREATE INDEX idx_arisan_period_name ON arisan_periods(period_name);

-- Success Message
SELECT 'Database arisan_db created successfully!' as message;
SELECT 'Default admin credentials - Username: admin, Password: admin123' as credentials;
