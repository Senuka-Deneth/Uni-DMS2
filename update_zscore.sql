ALTER TABLE zscore_cutoffs ADD COLUMN subject1 VARCHAR(100) DEFAULT NULL;
ALTER TABLE zscore_cutoffs ADD COLUMN subject2 VARCHAR(100) DEFAULT NULL;
ALTER TABLE zscore_cutoffs ADD COLUMN subject3 VARCHAR(100) DEFAULT NULL;
ALTER TABLE zscore_cutoffs ADD COLUMN district VARCHAR(100) DEFAULT NULL;

-- Example seed data based on user input
-- University of Moratuwa (ID usually 3, but let's assume existence)
-- Assuming we have degrees and cutoffs mapped properly.
-- Use this file to migrate the database schema and add the necessary records.
