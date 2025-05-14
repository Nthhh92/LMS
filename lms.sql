-- Script SQL pour la base de données LMS

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(50),
    nom VARCHAR(255),
    prenom VARCHAR(255)
);

CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    teacher_id INT,
    year VARCHAR(20),
    description TEXT,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

CREATE TABLE course_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    type VARCHAR(50),
    title VARCHAR(255),
    description TEXT,
    visible TINYINT(1),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    title VARCHAR(255),
    deadline DATETIME,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT,
    text TEXT,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);

CREATE TABLE answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT,
    text TEXT,
    is_correct TINYINT(1),
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

CREATE TABLE submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT,
    user_id INT,
    score INT,
    date DATETIME,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    recipient_id INT,
    message TEXT,
    created_at DATETIME,
    is_read TINYINT(1) DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (recipient_id) REFERENCES users(id)
); 