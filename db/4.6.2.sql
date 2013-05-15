ALTER TABLE karaoke ADD `protocol` varchar(64) NOT NULL default 'nfs';
UPDATE karaoke SET protocol='nfs' where rtsp_url='';
UPDATE karaoke SET protocol='custom' where rtsp_url!='';