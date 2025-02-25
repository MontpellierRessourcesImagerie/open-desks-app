-- Select the database to use, or create it.
CREATE DATABASE IF NOT EXISTS mri_cia_open_desks;
USE mri_cia_open_desks;

-- Reset the tables if they already exist.
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS engineers;

-- Create the table containing users.
CREATE TABLE IF NOT EXISTS users (
    email      VARCHAR(255) NOT NULL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    institute  VARCHAR(100) NOT NULL,
    team       VARCHAR(100) NOT NULL
);

-- Create the table containing the list of open-desk sessions.
CREATE TABLE IF NOT EXISTS sessions (
    session_date     DATE         NOT NULL PRIMARY KEY,
    session_location VARCHAR(255) NOT NULL,
    n_engineers      INT          NOT NULL
);


-- Create the table of appointments.
CREATE TABLE IF NOT EXISTS appointments (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    user_id                  VARCHAR(255) NOT NULL,
    session_id               DATE         NOT NULL,
    problem_description      TEXT         NOT NULL,
    time_start               TIME         NOT NULL,
    images_link              VARCHAR(255),
    FOREIGN KEY (user_id)    REFERENCES users(email),
    FOREIGN KEY (session_id) REFERENCES sessions(session_date),
    UNIQUE (user_id, session_id)
);

-- Create the table of engineers.
CREATE TABLE IF NOT EXISTS engineers (
    username      VARCHAR(100) NOT NULL PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    active_token  VARCHAR(128)          DEFAULT NULL,
    accepted      BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at    TIMESTAMP             DEFAULT NULL
);

