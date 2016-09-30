--

ALTER TABLE `media_category`
ADD COLUMN `modified` TIMESTAMP NOT NULL;

ALTER TABLE `cat_genre`
ADD COLUMN `modified` TIMESTAMP NOT NULL;

INSERT INTO `adm_grp_action_access`
          (`controller_name`,    `action_name`,                 `is_ajax`, `description`)
VALUES    ('video-club',         'video-categories',                    0, 'List of video categories'),
          ('video-club',         'video-categories-list-json',          1, 'List of video categories by page + filters'),
          ('video-club',         'add-video-categories',                1, 'Add video category'),
          ('video-club',         'edit-video-categories',               1, 'Edit video category'),
          ('video-club',         'remove-video-categories',             1, 'Remove the video category'),
          ('video-club',         'check-video-categories-name',         1, 'Validation names video category'),
          ('video-club',         'video-categories-reorder',            1, 'Change the order of categories'),

          ('video-club',         'video-genres',                        0, 'List of video genres'),
          ('video-club',         'video-genres-list-json',              1, 'List of video genres by page + filters'),
          ('video-club',         'save-video-genres',                   1, 'Save video genre'),
          ('video-club',         'remove-video-genres',                 1, 'Remove the video genre'),
          ('video-club',         'check-video-genres-name',             1, 'Validation names video genre');

-- //@UNDO

ALTER TABLE `media_category` DROP COLUMN `modified`;
ALTER TABLE `cat_genre` DROP COLUMN `modified`;

--