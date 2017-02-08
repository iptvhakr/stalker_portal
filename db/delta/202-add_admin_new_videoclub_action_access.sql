--

INSERT INTO `adm_grp_action_access`
        (`controller_name`,               `action_name`,    `is_ajax`, `description`                  )
VALUES  ('new-video-club',    'get-one-video-file-json',            1, 'Getting info by one file of episode'),
        ('new-video-club',  'get-video-files-list-json',            1, 'Getting list of files of episode'),
        ('new-video-club', 'get-video-season-list-json',            1, 'Getting list of seasons and episodes of video'),
        ('new-video-club',        'get-media-info-json',            1, 'Getting media-info from source'),
        ('audio-club',            'get-media-info-json',            1, 'Getting media-info from source');
--//@UNDO

--