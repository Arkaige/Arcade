CREATE DATABASE IF NOT EXISTS jumpgame;
USE jumpgame;

CREATE TABLE `users` (
  `userId` int NOT NULL AUTO_INCREMENT,
  `username` varchar(32) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `joinDate` DATETIME DEFAULT NOW(),
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `gametype` (
  `gameId` INT AUTO_INCREMENT PRIMARY KEY,
  `gameName` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `gametype` (`gameName`) VALUES ('Runner'), ('Slingshot');

CREATE TABLE `matches` (
  `matchId` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `gameId` INT NOT NULL DEFAULT 1,
  `score` INT NOT NULL,
  `playedAt` DATETIME DEFAULT NOW(),
  FOREIGN KEY (`userId`) REFERENCES `users`(`userId`) ON DELETE CASCADE,
  FOREIGN KEY (`gameId`) REFERENCES `gametype`(`gameId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `leaderboard` (
  `leaderboardId` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `gameId` INT NOT NULL DEFAULT 1,
  `bestScore` INT NOT NULL DEFAULT 0,
  `updatedAt` DATETIME DEFAULT NOW(),
  UNIQUE KEY `user_game` (`userId`, `gameId`),
  FOREIGN KEY (`userId`) REFERENCES `users`(`userId`) ON DELETE CASCADE,
  FOREIGN KEY (`gameId`) REFERENCES `gametype`(`gameId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--INSERT INTO `users` (`username`, `password`, `role`) VALUES
--('admin', 'onvsdkjn', 'admin');
