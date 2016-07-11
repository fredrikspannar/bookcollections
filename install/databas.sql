--
-- Tabellstruktur `Book`
--

CREATE TABLE IF NOT EXISTS `Book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `collection_id_index` (`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `BookCollection`
--

CREATE TABLE IF NOT EXISTS `BookCollection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `BookCollection` ADD  `collection_price` FLOAT NULL COMMENT 'if special price, else collection price is sum of all books' AFTER  `name`;

--
-- Relationer för tabell `Book`
--
ALTER TABLE `Book`
  ADD CONSTRAINT `Book_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `BookCollection` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
