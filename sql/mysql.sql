CREATE TABLE `user_assets` (
  `uuid` CHAR(36),
  `name` CHAR(50),
  `hash` TINYTEXT,
  `metadata` TINYTEXT,
  PRIMARY KEY (`uuid`, `name`),
  INDEX `uuid` (`uuid`)
);
