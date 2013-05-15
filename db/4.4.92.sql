ALTER TABLE video ADD `protocol` varchar(64) NOT NULL default 'nfs';
UPDATE video SET protocol='nfs' where rtsp_url='';
UPDATE video SET protocol='custom' where rtsp_url!='';