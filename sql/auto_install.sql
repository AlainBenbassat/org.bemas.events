/*
 * participants evaluating the event
 */
CREATE TABLE IF NOT EXISTS `civicrm_bemas_eval_participant_event` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nid` int(10) unsigned NOT NULL,
    `sid` int(10) unsigned NOT NULL,
    `event_id` int(10) unsigned NOT NULL,
    `template` varchar(5) NOT NULL,
    `algemene_tevredenheid` int(10) unsigned NULL,
    `invulling` int(10) unsigned NULL,
    `cursusmateriaal` int(10) unsigned NULL,
    `interactie` int(10) unsigned NULL,
    `kwaliteit` int(10) unsigned NULL,
    `bijgeleerd` int(10) unsigned NULL,
    `verwachting` int(10) unsigned NULL,
    `relevantie` int(10) unsigned NULL,
    `administratief_proces` int(10) unsigned NULL,
    `ontvangst` int(10) unsigned NULL,
    `catering` int(10) unsigned NULL,
    `locatie` int(10) unsigned NULL,
    PRIMARY KEY (`id`),
    INDEX (event_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
 * participants evaluating the trainer
 */
CREATE TABLE IF NOT EXISTS `civicrm_bemas_eval_participant_trainer` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nid` int(10) unsigned NOT NULL,
    `sid` int(10) unsigned NOT NULL,
    `contact_id` int(10) unsigned NOT NULL,
    `event_id` int(10) unsigned NOT NULL,
    `template` varchar(5) NOT NULL,
    `expertise` int(10) unsigned NULL,
    `didactische_vaardigheden` int(10) unsigned NULL,
    PRIMARY KEY (`id`),
    INDEX (event_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
 * trainers evaluating the event
 */
CREATE TABLE IF NOT EXISTS `civicrm_bemas_eval_trainer_event` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nid` int(10) unsigned NOT NULL,
    `sid` int(10) unsigned NOT NULL,
    `event_id` int(10) unsigned NOT NULL,
    `template` varchar(5) NOT NULL,
    `ontvangst` int(10) unsigned NULL,
    `catering` int(10) unsigned NULL,
    `locatie` int(10) unsigned NULL,
    `cursusmateriaal` int(10) unsigned NULL,
    `interactie` int(10) unsigned NULL,
    `verwachting` int(10) unsigned NULL,
    PRIMARY KEY (`id`),
    INDEX (event_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
