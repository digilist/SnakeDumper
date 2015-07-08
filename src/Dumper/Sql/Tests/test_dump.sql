-- ------------------------
-- SnakeDumper SQL Dump
-- ------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `Customer` (`id` INT AUTO_INCREMENT NOT NULL, `name` VARCHAR(10) DEFAULT NULL COLLATE utf8_general_ci, PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `Billing` (`id` INT AUTO_INCREMENT NOT NULL, `customer_id` INT DEFAULT NULL, `product` VARCHAR(100) DEFAULT NULL COLLATE utf8_general_ci, `amount` DOUBLE PRECISION DEFAULT NULL, INDEX `customer_id` (`customer_id`), INDEX `billing_product` (`product`), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `RandomTable` (`id` INT AUTO_INCREMENT NOT NULL, `name` VARCHAR(10) DEFAULT NULL COLLATE utf8_general_ci, PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
INSERT INTO `Customer` (`id`, `name`) VALUES (1, 'Foobar'), (2, 'Foobar'), (3, 'Foobar');
INSERT INTO `Customer` (`id`, `name`) VALUES (4, 'Foobar');
INSERT INTO `Billing` (`id`, `customer_id`, `product`, `amount`) VALUES (1, 1, 'IT', 42), (2, 1, NULL, 1337), (3, 2, 'Some stuff', 1337);
ALTER TABLE `Billing` ADD CONSTRAINT `customer_id` FOREIGN KEY (`customer_id`) REFERENCES `Customer` (`id`);
