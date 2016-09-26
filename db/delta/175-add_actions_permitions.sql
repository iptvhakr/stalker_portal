--

INSERT INTO `adm_grp_action_access`
          (`controller_name`,                          `action_name`, `is_ajax`, `description`)
VALUES    ('new-video-club',         'check-video-categories-number',         1, 'Validation of number of the video-category'),
          ('new-video-club',         'delete-video-season',                   1, 'Deleting season of video serial with series and files'),
          ('new-video-club',         'delete-video-season-series',            1, 'Deleting series of video serial with files');

--//@UNDO

--