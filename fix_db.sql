USE uni_dms;

-- Ensure degrees has the right columns
ALTER TABLE degrees 
ADD COLUMN IF NOT EXISTS type ENUM('Undergraduate', 'Postgraduate', 'Diploma') DEFAULT 'Undergraduate',
ADD COLUMN IF NOT EXISTS duration VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS medium VARCHAR(50) DEFAULT 'English';

-- Ensure departments has faculty
-- Wait, what does line 136 insert into?
-- Let's check line 136 of setup_local_db.sql
