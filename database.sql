CREATE DATABASE mto_system;
USE mto_system;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50)
);

INSERT INTO roles VALUES
(1, 'Администратор'),
(2, 'Кладовщик'),
(3, 'Пользователь');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255),
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

INSERT INTO users (username, password, role_id)
VALUES ('admin', MD5('admin'), 1);

CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    unit VARCHAR(20)
);

CREATE TABLE stock (
    material_id INT PRIMARY KEY,
    quantity INT DEFAULT 0,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE income (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT,
    quantity INT,
    date DATE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE expense (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT,
    quantity INT,
    date DATE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    material_id INT,
    quantity INT,
    status VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);
