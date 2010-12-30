--
-- Database: `recipemaster`
--

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE IF NOT EXISTS `ingredients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `unit` text NOT NULL,
  `qty` float NOT NULL,
  `kcal` int(11) NOT NULL,
  `carb` float NOT NULL,
  `sugar` float NOT NULL,
  `fibre` float NOT NULL,
  `protein` float NOT NULL,
  `fat` float NOT NULL,
  `sat_fat` float NOT NULL,
  `sodium` int(11) NOT NULL,
  `cholesterol` int(11) NOT NULL,
  `others` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `instructions` text NOT NULL,
  `time_estimate` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `recipe_photos`
--

CREATE TABLE IF NOT EXISTS `recipe_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `photo_path` text NOT NULL,
  `photo_caption` text NOT NULL,
  `photo_mime` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (parent_id) references recipes(id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `rec_ing`
--

CREATE TABLE IF NOT EXISTS `rec_ing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `ingredient_qty` float NOT NULL,
  `method` text NOT NULL,
  `ingredient_unit` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (recipe_id) references recipes(id),
  FOREIGN KEY (ingredient_id) references ingredients(id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
