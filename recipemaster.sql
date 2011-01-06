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
  `info` text,
  `unit` text,
  `qty` float NOT NULL,
  `typical_qty` float,
  `typical_unit` text,
  `kcal` int(11),
  `carb` float,
  `sugar` float,
  `fibre` float,
  `protein` float,
  `fat` float,
  `sat_fat` float,
  `sodium` int(11),
  `cholesterol` int(11),
  `others` text,
  `mtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text,
  `instructions` text,
  `time_estimate` int(11),
  `serves` int(11) NOT NULL DEFAULT '1',
  `mtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `photo_caption` text,
  `photo_mime` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (parent_id) references recipes(id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ingredient_photos`
--

CREATE TABLE IF NOT EXISTS `ingredient_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `photo_path` text NOT NULL,
  `photo_caption` text,
  `photo_mime` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (parent_id) references ingredients(id)
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
  `method` text,
  `ingredient_unit` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (recipe_id) references recipes(id),
  FOREIGN KEY (ingredient_id) references ingredients(id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `nutrients`
--

CREATE TABLE IF NOT EXISTS `nutrients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `info` text,
  `rdi` float NOT NULL,
  `unit` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ing_nutrients`
--

CREATE TABLE IF NOT EXISTS `ing_nutrients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ingredient_id` int(11) NOT NULL,
  `nutrient_id` int(11) NOT NULL,
  `qty` float NOT NULL,
  `unit` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (ingredient_id) references ingredients(id),
  FOREIGN KEY (nutrient_id) references nutrients(id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

