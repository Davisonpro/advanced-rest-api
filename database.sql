CREATE TABLE IF NOT EXISTS `product` (
    `product_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(32) NOT NULL,
    `description` TEXT NOT NULL,
    `price` FLOAT(10,2) NOT NULL DEFAULT 0.00,
    `category_id` INT(11) NOT NULL,
    `date_upd` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_add` DATETIME NOT NULL,
    PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT = 65;

INSERT INTO `product` (`product_id`, `name`, `description`, `price`, `category_id`, `date_upd`, `date_add`) VALUES
(1, 'LG P880 4X HD', 'My first awesome phone!', '336', 3, '2019-06-01 01:12:26', '2019-05-31 17:12:26'),
(2, 'Google Nexus 4', 'The most awesome phone of 2013!', '299', 2, '2019-06-01 01:12:26', '2019-05-31 17:12:26'),
(3, 'Samsung Galaxy S4', 'How about no?', '600', 3, '2019-06-01 01:12:26', '2019-05-31 17:12:26'),
(6, 'Bench Shirt', 'The best shirt!', '29', 1, '2019-06-01 01:12:26', '2019-05-31 02:12:21'),
(7, 'Lenovo Laptop', 'My business partner.', '399', 2, '2019-06-01 01:13:45', '2019-05-31 02:13:39'),
(8, 'Samsung Galaxy Tab 10.1', 'Good tablet.', '259', 2, '2019-06-01 01:14:13', '2019-05-31 02:14:08'),
(9, 'Spalding Watch', 'My sports watch.', '199', 1, '2019-06-01 01:18:36', '2019-05-31 02:18:31'),
(10, 'Sony Smart Watch', 'The coolest smart watch!', '300', 2, '2019-06-06 17:10:01', '2019-06-05 18:09:51'),
(11, 'Huawei Y300', 'For testing purposes.', '100', 2, '2019-06-06 17:11:04', '2019-06-05 18:10:54'),
(12, 'Abercrombie Lake Arnold Shirt', 'Perfect as gift!', '60', 1, '2019-06-06 17:12:21', '2019-06-05 18:12:11'),
(13, 'Abercrombie Allen Brook Shirt', 'Cool red shirt!', '70', 1, '2019-06-06 17:12:59', '2019-06-05 18:12:49'),
(26, 'Another product', 'Awesome product!', '555', 2, '2019-11-22 19:07:34', '2019-11-21 20:07:34'),
(28, 'Wallet', 'You can absolutely use this one!', '799', 6, '2019-12-04 21:12:03', '2019-12-03 22:12:03'),
(31, 'Amanda Waller Shirt', 'New awesome shirt!', '333', 1, '2019-12-13 00:52:54', '2019-12-12 01:52:54'),
(42, 'Nike Shoes for Men', 'Nike Shoes', '12999', 3, '2015-12-12 06:47:08', '2015-12-12 05:47:08'),
(48, 'Bristol Shoes', 'Awesome shoes.', '999', 5, '2016-01-08 06:36:37', '2016-01-08 05:36:37'),
(60, 'Rolex Watch', 'Luxury watch.', '25000', 1, '2016-01-11 15:46:02', '2016-01-11 14:46:02');

CREATE TABLE IF NOT EXISTS `category` (
 	`category_id` INT(11) NOT NULL AUTO_INCREMENT,
  	`name` VARCHAR(255) NOT NULL,
  	`description` TEXT NOT NULL,
	`date_upd` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_add` DATETIME NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19;

INSERT INTO `category` (`category_id`, `name`, `description`, `date_upd`, `date_add`) VALUES
(1, 'Fashion', 'Category for anything related to fashion.', '2019-06-01 00:35:07', '2019-05-30 17:34:33'),
(2, 'Electronics', 'Gadgets, drones and more.', '2019-06-01 00:35:07', '2019-05-30 17:34:33'),
(3, 'Motors', 'Motor sports and more', '2019-06-01 00:35:07', '2019-05-30 17:34:54'),
(5, 'Movies', 'Movie products.', '0000-00-00 00:00:00', '2016-01-08 13:27:26'),
(6, 'Books', 'Kindle books, audio books and more.', '0000-00-00 00:00:00', '2016-01-08 13:27:47'),
(13, 'Sports', 'Drop into new winter gear.', '2016-01-09 02:24:24', '2016-01-09 01:24:24');
