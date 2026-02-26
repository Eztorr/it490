CREATE USER 'userInfo'@'localhost' IDENTIFIED BY 'theBestPassword';
GRANT USAGE ON data.* TO 'userInfo'@'localhost';
GRANT SELECT, INSERT, DELETE ON data.Users TO 'testUser'@'localhost';
GRANT SELECT, INSERT, DELETE ON data.Sessions TO 'testUser'@'localhost';
FLUSH PRIVILEGES;
