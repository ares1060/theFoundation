/* SQLEditor (MySQL (2))*/

CREATE TABLE `pp_blog_tags`
(
`t_id` INTEGER AUTO_INCREMENT,
`name_de` VARCHAR(255),
PRIMARY KEY (`t_id`)
);

CREATE TABLE `pp_blog_eintrag`
(
`e_id` INTEGER AUTO_INCREMENT,
`title_de` VARCHAR(255),
`desc_de` TEXT,
`content_de` TEXT,
`u_id` INTEGER,
`creation_date` DATETIME,
`last_edit_date` DATETIME,
`status` INTEGER,
PRIMARY KEY (`e_id`)
);

CREATE TABLE `pp_blog_eintrag_tags`
(
`t_id` INTEGER,
`e_id` INTEGER
);

CREATE TABLE `pp_blog_kategorien`
(
`k_id` INTEGER AUTO_INCREMENT,
`name_de` VARCHAR(30),
PRIMARY KEY (`k_id`)
);

CREATE TABLE `pp_blog_eintrag_kategorien`
(
`e_id` INTEGER,
`k_id` INTEGER
);

ALTER TABLE `pp_blog_eintrag_tags` ADD FOREIGN KEY t_id_idxfk (`t_id`) REFERENCES `pp_blog_tags` (`t_id`);

ALTER TABLE `pp_blog_eintrag_tags` ADD FOREIGN KEY e_id_idxfk (`e_id`) REFERENCES `pp_blog_eintrag` (`e_id`);

ALTER TABLE `pp_blog_eintrag_kategorien` ADD FOREIGN KEY e_id_idxfk_1 (`e_id`) REFERENCES `pp_blog_eintrag` (`e_id`);

ALTER TABLE `pp_blog_eintrag_kategorien` ADD FOREIGN KEY k_id_idxfk (`k_id`) REFERENCES `pp_blog_kategorien` (`k_id`);
