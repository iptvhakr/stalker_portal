--

ALTER TABLE `tv_genre`
ADD COLUMN `number` INT(11) NOT NULL DEFAULT 0,
ADD COLUMN `modified` TIMESTAMP NOT NULL;

UPDATE tv_genre SET `number` = `id`;

INSERT INTO `adm_grp_action_access`
          (`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES    ('tv-channels',         'restart-all-archives',         1, 'Restart all TV archives',  0),
          ('tv-channels',         'tv-genres',                    0, 'List of tv genres',  0),
          ('tv-channels',         'tv-genres-list-json',          1, 'List of tv genres by page + filters',  0),
          ('tv-channels',         'add-tv-genres',                1, 'Add tv genre',  0),
          ('tv-channels',         'edit-tv-genres',               1, 'Edit tv genre',  0),
          ('tv-channels',         'remove-tv-genres',             1, 'Remove the tv genre',  0),
          ('tv-channels',         'check-tv-genres-name',         1, 'Validation names tv genre',  0),
          ('tv-channels',         'tv-genres-reorder',            1, 'Change the order of genres',  0);

-- //@UNDO

ALTER TABLE `tv_genre` DROP COLUMN `number`;
ALTER TABLE `tv_genre` DROP COLUMN `modified`;

--