CREATE TABLE `user_assets` (
  `uuid` CHAR(36),
  `name` CHAR(50),
  `hash` TINYTEXT,
  `metadata` TINYTEXT,
  PRIMARY KEY (`uuid`, `name`),
  INDEX `uuid` (`uuid`)
);
CREATE TABLE `user_assets_avatarcache` (
  `skinHash` TINYTEXT,
  `avatarHash` TINYTEXT,
  `scale` INT,
  PRIMARY KEY (`skinHash`, `scale`),
  INDEX `skinHash` (`skinHash`)
);