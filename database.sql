CREATE TABLE IF NOT EXISTS students (
  id INT(11) NOT NULL AUTO_INCREMENT,
  student_id VARCHAR(20) NOT NULL UNIQUE,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  course VARCHAR(100) NOT NULL,
  major VARCHAR(100) DEFAULT NULL,
  year_level TINYINT(1) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Enrolled',
  gpa VARCHAR(10) DEFAULT '1.75',
  remarks VARCHAR(100) DEFAULT 'Good Standing',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO students (student_id, first_name, last_name, email, course, major, year_level, status, gpa, remarks) VALUES
('2026-0101', 'Juan', 'Dela Cruz', 'juan.delacruz@example.com', 'BSIT', 'Web Development', 3, 'Enrolled', '1.25', 'Dean\'s Lister'),
('2026-0102', 'Maria', 'Santos', 'maria.santos@example.com', 'BSCS', 'Data Science', 2, 'Enrolled', '1.50', 'Dean\'s Lister'),
('2026-0103', 'Carlo', 'Reyes', 'carlo.reyes@example.com', 'BSIT', 'Network & Security', 1, 'Inactive', '2.50', 'Passed'),
('2026-0104', 'Ana', 'Lopez', 'ana.lopez@example.com', 'BSBA', 'Financial Management', 4, 'Enrolled', '1.75', 'Good Standing');
