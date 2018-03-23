/**
 * Roundcube Tasklist plugin database
 *
 * @version @package_version@
 * @author William Le Berre

 */

CREATE TABLE IF NOT EXISTS `PREFIX_tags` (
  `uid` int(255) NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `uid2` varchar(255) DEFAULT NULL,
  `categorie` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `id_user` (`id_user`),
CONSTRAINT `fk_user_id_tags` FOREIGN KEY (`id_user`) REFERENCES `PREFIX_users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_tag2mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned NOT NULL,
  `uid` int(255) NOT NULL,
  `member` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
   KEY `id_user` (`id_user`),
   KEY `uid` (`uid`),
  CONSTRAINT `fk_tag2mail_user_id` FOREIGN KEY (`id_user`) REFERENCES `PREFIX_users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tag2mail_tags` FOREIGN KEY (`uid`)  REFERENCES `PREFIX_tags`(`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

