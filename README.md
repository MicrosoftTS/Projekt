# Projekt

Potrebno postaviti username i lozinku na host:
Username: root
Lozinka: root

-- Kreiranje baze podataka
CREATE DATABASE firma_app;

CREATE TABLE korisnici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL
);

ALTER TABLE korisnici
ADD COLUMN radnik_id INT,
ADD CONSTRAINT fk_radnik_id
FOREIGN KEY (radnik_id) REFERENCES radnici(id) ON DELETE CASCADE;

INSERT INTO korisnici (username, password, is_admin) VALUES
('david.colovic-topic', SHA2('david.colovic-topic', 256), 1), -- Admin korisnik
('ivan.horvat', SHA2('ivan.horvat', 256), 0);                 -- Korisnik koji nije admin

CREATE TABLE partneri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(255) NOT NULL,
    kontakt_broj VARCHAR(20),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS radnici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(255) NOT NULL,
    prezime VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE
);

