ALTER TABLE feedback
ADD COLUMN state ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Inactive';