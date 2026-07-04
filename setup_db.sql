
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE predictions DROP FOREIGN KEY IF EXISTS predictions_ibfk_1;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS email VARCHAR(255) AFTER username;

UPDATE users SET email = CONCAT(username, '@example.com') WHERE email IS NULL OR email = '';

ALTER TABLE users 
MODIFY email VARCHAR(255) NOT NULL UNIQUE,
CHANGE password_hash Password VARCHAR(255) NOT NULL,
CHANGE id ID INT UNSIGNED AUTO_INCREMENT;

-- 2. Update existing 'predictions' table
ALTER TABLE predictions
ADD COLUMN IF NOT EXISTS Top_Factors TEXT AFTER risk_level;

ALTER TABLE predictions
CHANGE risk_probability Risk_probability FLOAT,
CHANGE risk_level Risk_Level VARCHAR(50),
CHANGE prediction_date Prediction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CHANGE user_id User_ID INT UNSIGNED NOT NULL,
CHANGE id ID INT UNSIGNED AUTO_INCREMENT;

-- Re-establish foreign key with new column names
ALTER TABLE predictions ADD CONSTRAINT fk_predictions_users FOREIGN KEY (User_ID) REFERENCES users(ID) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- 3. Create missing tables
CREATE TABLE IF NOT EXISTS advice_log (
    ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Prediction_Id INT UNSIGNED NOT NULL,
    User_Id INT UNSIGNED NOT NULL,
    Ai_Advice TEXT,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_advice_prediction FOREIGN KEY (Prediction_Id) REFERENCES predictions(ID) ON DELETE CASCADE,
    CONSTRAINT fk_advice_user FOREIGN KEY (User_Id) REFERENCES users(ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS patient_profile (
    ID INT UNSIGNED PRIMARY KEY,
    Age INT,
    Ever_Married VARCHAR(50),
    gender VARCHAR(50),
    work_type VARCHAR(100),
    residence_type VARCHAR(100),
    CONSTRAINT fk_profile_user FOREIGN KEY (ID) REFERENCES users(ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS health_record (
    ID INT UNSIGNED PRIMARY KEY,
    All_Other_Patient_Profile TEXT,
    CONSTRAINT fk_health_user FOREIGN KEY (ID) REFERENCES users(ID) ON DELETE CASCADE
) ENGINE=InnoDB;
