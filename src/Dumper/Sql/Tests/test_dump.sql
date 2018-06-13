-- ------------------------
-- SnakeDumper SQL Dump
-- ------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `Customer` (`id` INT AUTO_INCREMENT NOT NULL, `name` VARCHAR(10) DEFAULT NULL COLLATE utf8_unicode_ci, `test` VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci COMMENT '(DC2Type:test)', PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `Billing` (`id` INT AUTO_INCREMENT NOT NULL, `customer_id` INT DEFAULT NULL, `product` VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, `amount` DOUBLE PRECISION DEFAULT NULL, INDEX `customer_id` (`customer_id`), INDEX `billing_product` (`product`), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `RandomTable` (`id` INT AUTO_INCREMENT NOT NULL, `name` VARCHAR(10) DEFAULT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
INSERT INTO `Customer` (`id`, `name`, `test`) VALUES ('1', 'Foobar', 'today'), ('2', 'Foobar', 'yesterday'), ('3', 'Foobar', 'tomorrow');
INSERT INTO `Customer` (`id`, `name`, `test`) VALUES ('4', 'Foobar', 'always');
INSERT INTO `Billing` (`id`, `customer_id`, `product`, `amount`) VALUES ('1', '1', 'IT', '42'), ('2', '1', NULL, '1337'), ('3', '2', 'Some stuff', '1337');
ALTER TABLE `Billing` ADD CONSTRAINT `customer_id` FOREIGN KEY (`customer_id`) REFERENCES `Customer` (`id`);
