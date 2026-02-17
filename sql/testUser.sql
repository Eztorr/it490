CREATE USER 'testUser'@'localhost' IDENTIFIED BY '12345';
GRANT ALL PRIVILEGES ON data.* TO 'testUser'@'localhost';
FLUSH PRIVILEGES;
