DROP USER IF EXISTS 'myuser'@'%';
CREATE USER 'myuser'@'%' IDENTIFIED BY 'mypassword';
GRANT ALL PRIVILEGES ON bloomprj.* TO 'myuser'@'%';
FLUSH PRIVILEGES;

CREATE DATABASE IF NOT EXISTS bloomprj;
USE bloomprj;


