<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.scandir.php - part of getID3()                       //
// Sample script for recursively scanning directories and      //
// storing the results in a database                           //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

echo '<HTML><HEAD><STYLE>BODY, TD, TH { font-family: sans-serif; font-size: 10pt; }</STYLE></HEAD><BODY>';

if (!mysql_connect('localhost', 'getid3', 'getid3')) {
	die('Could not connect to MySQL host');
}
if (!mysql_select_db('getid3')) {
	die('Could not select database');
}

require_once('getid3.php');



function safe_mysql_query($SQLquery) {
	$result = @mysql_query($SQLquery);
	if (mysql_error()) {
		die('<FONT COLOR="red">'.mysql_error().'</FONT><HR><TT>'.$SQLquery.'</TT>');
	}
	return $result;
}

function mysql_table_exists($tablename) {
	return (bool) mysql_query('DESCRIBE '.$tablename);
}

function AcceptableExtensions($fileformat, $audio_dataformat='', $video_dataformat='') {
	static $AcceptableExtensionsAudio = array();
	if (empty($AcceptableExtensionsAudio)) {
		$AcceptableExtensionsAudio['mp3']['mp3']  = array('mp3');
		$AcceptableExtensionsAudio['mp2']['mp2']  = array('mp2');
		$AcceptableExtensionsAudio['mp1']['mp1']  = array('mp1');
		$AcceptableExtensionsAudio['asf']['asf']  = array('asf');
		$AcceptableExtensionsAudio['asf']['wma']  = array('wma');
		$AcceptableExtensionsAudio['riff']['mp3'] = array('wav');
		$AcceptableExtensionsAudio['riff']['wav'] = array('wav');
	}
	static $AcceptableExtensionsVideo = array();
	if (empty($AcceptableExtensionsVideo)) {
		$AcceptableExtensionsVideo['mp3']['mp3']  = array('mp3');
		$AcceptableExtensionsVideo['mp2']['mp2']  = array('mp2');
		$AcceptableExtensionsVideo['mp1']['mp1']  = array('mp1');
		$AcceptableExtensionsVideo['asf']['asf']  = array('asf');
		$AcceptableExtensionsVideo['asf']['wmv']  = array('wmv');
		$AcceptableExtensionsVideo['gif']['gif']  = array('gif');
		$AcceptableExtensionsVideo['jpg']['jpg']  = array('jpg');
		$AcceptableExtensionsVideo['png']['png']  = array('png');
		$AcceptableExtensionsVideo['bmp']['bmp']  = array('bmp');
	}
	if (!empty($video_dataformat)) {
		return (isset($AcceptableExtensionsVideo[$fileformat][$video_dataformat]) ? $AcceptableExtensionsVideo[$fileformat][$video_dataformat] : array());
	} else {
		return (isset($AcceptableExtensionsAudio[$fileformat][$audio_dataformat]) ? $AcceptableExtensionsAudio[$fileformat][$audio_dataformat] : array());
	}
}

function ClosestStandardMP3Bitrate($bitrate) {
	static $StandardBitrates = array(320, 256, 224, 192, 160, 128, 112, 96, 80, 64, 56, 48, 40, 32);
	static $BitrateTable = array(0=>'-');
	$roundbitrate = round($bitrate / 1000);
	if (!isset($BitrateTable[$roundbitrate])) {
		if ($roundbitrate > 320) {
			$BitrateTable[$roundbitrate] = round($bitrate / 10000) * 10000;
		} else {
			$LastBitrate = 320;
			foreach ($StandardBitrates as $StandardBitrate) {
				$BitrateTable[$roundbitrate] = $StandardBitrate;
				if ($roundbitrate >= $StandardBitrate - (($LastBitrate - $StandardBitrate) / 2)) {
					break;
				}
				$LastBitrate = $StandardBitrate;
			}
		}
	}
	return $BitrateTable[$roundbitrate];
}


if (!empty($_REQUEST['scan'])) {
	if (mysql_table_exists('files')) {
		$SQLquery  = 'DROP TABLE files';
		safe_mysql_query($SQLquery);
	}
}
if (!mysql_table_exists('files')) {
	$SQLquery  = 'CREATE TABLE files (';
	$SQLquery .= '  ID mediumint(8) unsigned NOT NULL auto_increment,';
	$SQLquery .= '  filename text NOT NULL,';
	$SQLquery .= '  md5_file varchar(32) NOT NULL default "",';
	$SQLquery .= '  md5_data varchar(32) NOT NULL default "",';
	$SQLquery .= '  md5_data_source varchar(32) NOT NULL default "",';
	$SQLquery .= '  filesize int(10) unsigned NOT NULL default "0",';
	$SQLquery .= '  fileformat varchar(255) NOT NULL default "",';
	$SQLquery .= '  audio_dataformat varchar(255) NOT NULL default "",';
	$SQLquery .= '  video_dataformat varchar(255) NOT NULL default "",';
	$SQLquery .= '  audio_bitrate float NOT NULL default "0",';
	$SQLquery .= '  video_bitrate float NOT NULL default "0",';
	$SQLquery .= '  playtime_seconds varchar(255) NOT NULL default "",';
	$SQLquery .= '  tags varchar(255) NOT NULL default "",';
	$SQLquery .= '  artist varchar(255) NOT NULL default "",';
	$SQLquery .= '  title varchar(255) NOT NULL default "",';
	$SQLquery .= '  album varchar(255) NOT NULL default "",';
	$SQLquery .= '  genre varchar(255) NOT NULL default "",';
	$SQLquery .= '  comment varchar(255) NOT NULL default "",';
	$SQLquery .= '  track tinyint(3) unsigned NOT NULL default "0",';
	$SQLquery .= '  warning text NOT NULL,';
	$SQLquery .= '  error text NOT NULL,';
	$SQLquery .= '  vbr_method varchar(255) NOT NULL default "",';
	$SQLquery .= '  PRIMARY KEY  (ID)';
	$SQLquery .= ') TYPE=MyISAM;';
	safe_mysql_query($SQLquery);
}


if (!empty($_REQUEST['scan']) || !empty($_REQUEST['newscan']) || !empty($_REQUEST['rescanerrors'])) {
	if (!empty($_REQUEST['rescanerrors'])) {

		echo 'Re-scanning all media files already in database that had errors and/or warnings in last scan<HR>';

		$SQLquery = 'SELECT filename FROM files WHERE (error <> "") OR (warning <> "") ORDER BY filename ASC';
		$result = safe_mysql_query($SQLquery);
		while ($row = mysql_fetch_array($result)) {

			if (!file_exists($row['filename'])) {
				echo '<B>File missing: '.$row['filename'].'</B><BR>';
				$SQLquery = 'DELETE FROM files WHERE (filename = "'.FixDBFields($row['filename']).'")';
				safe_mysql_query($SQLquery);
			} else {
				$FilesInDir[] = $row['filename'];
			}

		}

	} elseif (!empty($_REQUEST['scan']) || !empty($_REQUEST['newscan'])) {

		echo 'Scanning all media files in <B>'.realpath(!empty($_REQUEST['scan']) ? $_REQUEST['scan'] : $_REQUEST['newscan']).'</B> (and subdirectories)<HR>';

		if (!empty($_REQUEST['newscan'])) {
			$AlreadyInDatabase = array();
			set_time_limit(60);
			$SQLquery = 'SELECT filename FROM files ORDER BY filename ASC';
			$result = safe_mysql_query($SQLquery);
			while ($row = mysql_fetch_array($result)) {
				$AlreadyInDatabase[] = strtolower($row['filename']);
			}
		}

		$DirectoriesToScan	= array(realpath(!empty($_REQUEST['scan']) ? $_REQUEST['scan'] : $_REQUEST['newscan']));
		$DirectoriesScanned = array();
		$FilesInDir         = array();
		while (count($DirectoriesToScan) > 0) {
			foreach ($DirectoriesToScan as $DirectoryKey => $startingdir) {
				if ($dir = @opendir($startingdir)) {
					set_time_limit(30);
					echo '<B>'.$startingdir.'</B><BR>';
					flush();
					while (($file = readdir($dir)) !== false) {
						if (($file != '.') && ($file != '..')) {
							$RealPathName = realpath($startingdir.'/'.$file);
							if (is_dir($RealPathName)) {
								if (!in_array($RealPathName, $DirectoriesScanned) && !in_array($RealPathName, $DirectoriesToScan)) {
									$DirectoriesToScan[] = $RealPathName;
								}
							} else if (is_file($RealPathName)) {
								if (!empty($_REQUEST['newscan'])) {
									if (!in_array(strtolower(str_replace('\\', '/', $RealPathName)), $AlreadyInDatabase)) {
										$FilesInDir[] = $RealPathName;
									} else {
									}
								} elseif (!empty($_REQUEST['scan'])) {
									$FilesInDir[] = $RealPathName;
								}
							}
						}
					}
					closedir($dir);
				}
				$DirectoriesScanned[] = $startingdir;
				unset($DirectoriesToScan[$DirectoryKey]);
			}
		}
		echo '<I>List of files to scan complete (added '.number_format(count($FilesInDir)).' files to scan)</I><HR>';
		flush();
	}

	$FilesInDir = array_unique($FilesInDir);
	sort($FilesInDir);

	$starttime = time();
	$rowcounter = 0;
	$totaltoprocess = count($FilesInDir);

	$formatExtensions = array('mp3'=>'mp3', 'ogg'=>'ogg', 'zip'=>'zip', 'wav'=>'riff', 'avi'=>'riff', 'mid'=>'midi', 'mpg'=>'mpeg');
	foreach ($FilesInDir as $filename) {
		set_time_limit(30);

		echo date('H:i:s').' ['.number_format(++$rowcounter).' / '.number_format($totaltoprocess).'] '.$filename.'<BR>';
		flush();

		$ThisFileInfo = GetAllFileInfo($filename, '', true, true, true);
		if (empty($ThisFileInfo['fileformat']) || ($ThisFileInfo['fileformat'] == 'id3')) {
			if (isset($formatExtensions[strtolower(fileextension($filename))])) {
				$ThisFileInfo = GetAllFileInfo($filename, $formatExtensions[strtolower(fileextension($filename))], true, true, true);
			}
		}

		if (!empty($_REQUEST['rescanerrors'])) {

			$SQLquery  = 'UPDATE files SET ';
			$SQLquery .= 'md5_file = "'.addslashes(@$ThisFileInfo['md5_file']).'", ';
			$SQLquery .= 'md5_data = "'.addslashes(@$ThisFileInfo['md5_data']).'", ';
			$SQLquery .= 'md5_data_source = "'.addslashes(@$ThisFileInfo['md5_data_source']).'", ';
			$SQLquery .= 'filesize = "'.addslashes(@$ThisFileInfo['filesize']).'", ';
			$SQLquery .= 'fileformat = "'.addslashes(@$ThisFileInfo['fileformat']).'", ';
			$SQLquery .= 'audio_dataformat = "'.addslashes(@$ThisFileInfo['audio']['dataformat']).'", ';
			$SQLquery .= 'video_dataformat = "'.addslashes(@$ThisFileInfo['video']['dataformat']).'", ';
			$SQLquery .= 'audio_bitrate = "'.addslashes(@$ThisFileInfo['audio']['bitrate']).'", ';
			$SQLquery .= 'video_bitrate = "'.addslashes(@$ThisFileInfo['video']['bitrate']).'", ';
			$SQLquery .= 'playtime_seconds = "'.addslashes(@$ThisFileInfo['playtime_seconds']).'", ';
			$SQLquery .= 'tags = "'.addslashes(@implode(',', @$ThisFileInfo['tags'])).'", ';
			$SQLquery .= 'artist = "'.addslashes(@implode(',', @$ThisFileInfo['comments']['artist'])).'", ';
			$SQLquery .= 'title = "'.addslashes(@implode(',', @$ThisFileInfo['comments']['title'])).'", ';
			$SQLquery .= 'album = "'.addslashes(@implode(',', @$ThisFileInfo['comments']['album'])).'", ';
			$SQLquery .= 'genre = "'.addslashes(@implode(',', @$ThisFileInfo['comments']['genre'])).'", ';
			$SQLquery .= 'comment = "'.addslashes(@implode(',', @$ThisFileInfo['comments']['comment'])).'", ';
			$SQLquery .= 'track = "'.addslashes(@implode(',', @$ThisFileInfo['comments']['track'])).'", ';
			$SQLquery .= 'warning = "'.addslashes(@$ThisFileInfo['warning']).'", ';
			$SQLquery .= 'error = "'.addslashes(@$ThisFileInfo['error']).'", ';
			$SQLquery .= 'vbr_method = "'.addslashes(@$ThisFileInfo['mpeg']['audio']['VBR_method']).'"';
			$SQLquery .= 'WHERE (filename = "'.addslashes(@$ThisFileInfo['filenamepath']).'")';

		} elseif (!empty($_REQUEST['scan']) || !empty($_REQUEST['newscan'])) {

			$SQLquery  = 'INSERT INTO files (filename, md5_file, md5_data, md5_data_source, filesize, fileformat, audio_dataformat, video_dataformat, audio_bitrate, video_bitrate, playtime_seconds, tags, artist, title, album, genre, comment, track, warning, error, vbr_method) VALUES (';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['filenamepath']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['md5_file']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['md5_data']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['md5_data_source']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['filesize']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['fileformat']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['audio']['dataformat']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['video']['dataformat']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['audio']['bitrate']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['video']['bitrate']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['playtime_seconds']).'", ';
			$SQLquery .= '"'.addslashes(@implode(',', @$ThisFileInfo['tags'])).'", ';
			$SQLquery .= '"'.addslashes(@implode(',', @$ThisFileInfo['comments']['artist'])).'", ';
			$SQLquery .= '"'.addslashes(@implode(',', @$ThisFileInfo['comments']['title'])).'", ';
			$SQLquery .= '"'.addslashes(@implode(',', @$ThisFileInfo['comments']['album'])).'", ';
			$SQLquery .= '"'.addslashes(@implode(',', @$ThisFileInfo['comments']['genre'])).'", ';
			$SQLquery .= '"'.addslashes(@implode(',', @$ThisFileInfo['comments']['comment'])).'", ';
			$SQLquery .= '"'.addslashes(@implode(',', @$ThisFileInfo['comments']['track'])).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['warning']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['error']).'", ';
			$SQLquery .= '"'.addslashes(@$ThisFileInfo['mpeg']['audio']['VBR_method']).'")';

		}
		safe_mysql_query($SQLquery);

	}

	echo '<HR>Done scanning!<HR>';

} elseif (!empty($_REQUEST['deadfilescheck'])) {

	$SQLquery  = 'SELECT filename, filesize FROM files ORDER BY filename ASC';
	$result = safe_mysql_query($SQLquery);
	$totalchecked = 0;
	$totalremoved = 0;
	while ($row = mysql_fetch_array($result)) {
		$totalchecked++;
		set_time_limit(30);
		if (!file_exists($row['filename']) || (filesize($row['filename']) != $row['filesize'])) {
			$totalremoved++;
			echo $row['filename'].'<BR>';
			flush();
			$SQLquery = 'DELETE FROM files WHERE (filename = "'.FixDBFields($row['filename']).'")';
			safe_mysql_query($SQLquery);
		}
	}

	echo '<HR><B>'.number_format($totalremoved).' of '.number_format($totalchecked).' files in database no longer exist - removed from database.</B><HR>';


} elseif (!empty($_REQUEST['audiobitrates'])) {

	$BitrateDistribution = array();
	$SQLquery  = 'SELECT ROUND(audio_bitrate) AS RoundBitrate, COUNT(*) AS num FROM files GROUP BY ROUND(audio_bitrate)';
	$result = safe_mysql_query($SQLquery);
	while ($row = mysql_fetch_array($result)) {
		safe_inc($BitrateDistribution[ClosestStandardMP3Bitrate($row['RoundBitrate'])], $row['num']);
	}

	echo '<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="3">';
	echo '<TR><TH>Bitrate</TH><TH>Count</TH></TR>';
	foreach ($BitrateDistribution as $Bitrate => $Count) {
		echo '<TR>';
		echo '<TD ALIGN="RIGHT">'.$Bitrate.' kbps</TD>';
		echo '<TD ALIGN="RIGHT">'.number_format($Count).'</TD>';
		echo '</TR>';
	}
	echo '</TABLE>';

} elseif (!empty($_REQUEST['tagtypes'])) {

	echo 'Files with tags:<BR>';
	echo '<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="3">';
	echo '<TR><TH>Tags</TH><TH>Count</TH></TR>';

	$IgnoreNoTagFormats = array('', 'png', 'jpg', 'gif', 'bmp', 'swf', 'zip', 'mid', 'mod', 'xm', 'it', 's3m');

	$SQLquery  = 'SELECT tags, COUNT(*) AS num FROM files';
	$SQLquery .= ' WHERE (fileformat NOT LIKE "'.implode('") AND (fileformat NOT LIKE "', $IgnoreNoTagFormats).'")';
	$SQLquery .= ' GROUP BY tags';
	$SQLquery .= ' ORDER BY num DESC';
	$result = safe_mysql_query($SQLquery);
	while ($row = mysql_fetch_array($result)) {
		echo '<TR>';
		echo '<TD>'.$row['tags'].'</TD>';
		echo '<TD ALIGN="RIGHT"><A HREF="'.$_SERVER['PHP_SELF'].'?tagtypes=1&showtagfiles='.($row['tags'] ? urlencode($row['tags']) : '').'">'.number_format($row['num']).'</A></TD>';
		echo '</TR>';
	}
	echo '</TABLE><HR>';

	if (isset($_REQUEST['showtagfiles'])) {
		echo '<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="3">';
		$SQLquery  = 'SELECT filename, tags FROM files';
		$SQLquery .= ' WHERE (tags LIKE "'.addslashes($_REQUEST['showtagfiles']).'")';
		$SQLquery .= ' AND (fileformat NOT LIKE "'.implode('") AND (fileformat NOT LIKE "', $IgnoreNoTagFormats).'")';
		$SQLquery .= ' ORDER BY filename ASC';
		$result = safe_mysql_query($SQLquery);
		while ($row = mysql_fetch_array($result)) {
			echo '<TR>';
			echo '<TD><A HREF="getid3.demo.check.php?filename='.rawurlencode($row['filename']).'">'.$row['filename'].'</A></TD>';
			echo '<TD>'.$row['tags'].'</TD>';
			echo '</TR>';
		}
		echo '</TABLE>';
	}


} elseif (!empty($_REQUEST['md5datadupes'])) {

	$SQLquery  = 'SELECT md5_data, filename, COUNT(*) AS num';
	$SQLquery .= ' FROM files';
	$SQLquery .= ' GROUP BY md5_data';
	$SQLquery .= ' ORDER BY num DESC';
	$result = safe_mysql_query($SQLquery);
	echo 'Duplicated MD5_DATA:<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="2">';
	while (($row = mysql_fetch_array($result)) && ($row['num'] > 1)) {
		set_time_limit(30);

		$filenames = array();
		$tags      = array();
		$md5_data  = array();
		$SQLquery  = 'SELECT filename, tags FROM files';
		$SQLquery .= ' WHERE (md5_data = "'.addslashes($row['md5_data']).'")';
		$SQLquery .= ' ORDER BY filename ASC';
		$result2 = safe_mysql_query($SQLquery);
		while ($row2 = mysql_fetch_array($result2)) {
			$filenames[] = $row2['filename'];
			$tags[]      = $row2['tags'];
			$md5_data[]  = $row['md5_data'];
		}

		echo '<TR>';
		echo '<TD VALIGN="TOP" STYLE="font-family: monospace;">'.implode('<BR>', $md5_data).'</TD>';
		echo '<TD VALIGN="TOP" NOWRAP>'.implode('<BR>', $tags).'</TD>';
		echo '<TD VALIGN="TOP">'.implode('<BR>', $filenames).'</TD>';
		echo '</TR>';
	}
	echo '</TABLE><HR>';


} elseif (!empty($_REQUEST['artisttitledupes'])) {

	$SQLquery  = 'SELECT artist, title, filename, COUNT(*) AS num FROM files';
	$SQLquery .= ' WHERE (artist <> "")';
	$SQLquery .= ' AND (title <> "")';
	$SQLquery .= ' GROUP BY artist, title';
	$SQLquery .= ' ORDER BY num DESC';
	$result = safe_mysql_query($SQLquery);
	echo 'Duplicated aritst + title:<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="2">';
	$uniquetitles = 0;
	$uniquefiles  = 0;
	while (($row = mysql_fetch_array($result)) && ($row['num'] > 1)) {
		$uniquetitles++;
		set_time_limit(30);

		$filenames = array();
		$artists   = array();
		$titles    = array();
		$bitrates  = array();
		$playtimes = array();
		$SQLquery  = 'SELECT filename, artist, title, audio_bitrate, vbr_method, playtime_seconds FROM files';
		$SQLquery .= ' WHERE (artist = "'.addslashes($row['artist']).'")';
		$SQLquery .= ' AND (title = "'.addslashes($row['title']).'")';
		$SQLquery .= ' ORDER BY filename ASC';
		$result2 = safe_mysql_query($SQLquery);
		while ($row2 = mysql_fetch_array($result2)) {
			$uniquefiles++;
			$filenames[] = $row2['filename'];
			$artists[]   = $row2['artist'];
			$titles[]    = $row2['title'];
			$bitrates[]  = round($row2['audio_bitrate'] / 1000).'k'.($row2['vbr_method'] ? 'v' : '&nbsp;');
			$playtimes[] = PlaytimeString($row2['playtime_seconds']);
		}

		echo '<TR>';
		echo '<TD NOWRAP VALIGN="TOP">';
		foreach ($filenames as $file) {
			echo '<A HREF="getid3.demo.check.php?deletefile='.urlencode($file).'" onClick="return confirm(\'Are you sure you want to delete '.addslashes($file).'? \n(this action cannot be un-done)\');" TITLE="Permanently delete '."\n".FixTextFields($file)."\n".'">delete</A><BR>';
		}
		echo '</TD>';
		echo '<TD NOWRAP VALIGN="TOP">'.implode('<BR>', $artists).'</TD>';
		echo '<TD NOWRAP VALIGN="TOP">'.implode('<BR>', $titles).'</TD>';
		echo '<TD NOWRAP VALIGN="TOP" ALIGN="RIGHT">'.implode('<BR>', $bitrates).'</TD>';
		echo '<TD NOWRAP VALIGN="TOP" ALIGN="RIGHT">'.implode('<BR>', $playtimes).'</TD>';
		echo '<TD NOWRAP VALIGN="TOP">';
		foreach ($filenames as $file) {
			echo '<A HREF="getid3.demo.check.php?filename='.rawurlencode($file).'">'.$file.'</A><BR>';
		}
		echo '</TD>';
		echo '</TR>';
	}
	echo '</TABLE>';
	echo number_format($uniquefiles).' files with '.number_format($uniquetitles).' unique <I>aritst + title</I><BR>';
	echo '<HR>';

} elseif (!empty($_REQUEST['filetypelist'])) {

	list($fileformat, $audioformat) = explode('|', $_REQUEST['filetypelist']);
	$SQLquery  = 'SELECT filename, fileformat, audio_dataformat';
	$SQLquery .= ' FROM files';
	$SQLquery .= ' WHERE (fileformat = "'.FixDBFields($fileformat).'")';
	$SQLquery .= ' AND (audio_dataformat = "'.FixDBFields($audioformat).'")';
	$SQLquery .= ' ORDER BY filename ASC';
	$result = safe_mysql_query($SQLquery);
	echo 'Files of format <B>'.$fileformat.'.'.$audioformat.':<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="4">';
	echo '<TR><TH>file</TH><TH>audio</TH><TH>filename</TH></TR>';
	while ($row = mysql_fetch_array($result)) {
		echo '<TR>';
		echo '<TD>'.$row['fileformat'].'</TD>';
		echo '<TD>'.$row['audio_dataformat'].'</TD>';
		echo '<TD><A HREF="getid3.demo.check.php?filename='.rawurlencode($row['filename']).'">'.$row['filename'].'</A></TD>';
		echo '</TR>';
	}
	echo '</TABLE><HR>';

} elseif (!empty($_REQUEST['fileextensions'])) {

	$SQLquery  = 'SELECT filename, fileformat, audio_dataformat, video_dataformat, tags';
	$SQLquery .= ' FROM files';
	$SQLquery .= ' ORDER BY filename ASC';
	$result = safe_mysql_query($SQLquery);
	$invalidextensionfiles = 0;
	$invalidextensionline  = '<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="4">';
	$invalidextensionline .= '<TR><TH>file</TH><TH>audio</TH><TH>video</TH><TH>tags</TH><TH>actual</TH><TH>correct</TH><TH>filename</TH></TR>';
	while ($row = mysql_fetch_array($result)) {
		set_time_limit(30);

		$acceptableextensions = AcceptableExtensions($row['fileformat'], $row['audio_dataformat'], $row['video_dataformat']);
		$actualextension      = strtolower(fileextension($row['filename']));
		if ($acceptableextensions && !in_array($actualextension, $acceptableextensions)) {
			$invalidextensionfiles++;

			$invalidextensionline .= '<TR>';
			$invalidextensionline .= '<TD>'.$row['fileformat'].'</TD>';
			$invalidextensionline .= '<TD>'.$row['audio_dataformat'].'</TD>';
			$invalidextensionline .= '<TD>'.$row['video_dataformat'].'</TD>';
			$invalidextensionline .= '<TD>'.$row['tags'].'</TD>';
			$invalidextensionline .= '<TD>'.$actualextension.'</TD>';
			$invalidextensionline .= '<TD>'.implode('; ', $acceptableextensions).'</TD>';
			$invalidextensionline .= '<TD><A HREF="getid3.demo.check.php?filename='.rawurlencode($row['filename']).'">'.$row['filename'].'</A></TD>';
			$invalidextensionline .= '</TR>';
		}
	}
	$invalidextensionline .= '</TABLE><HR>';
	echo number_format($invalidextensionfiles).' files with incorrect filename extension:<BR>';
	echo $invalidextensionline;

} elseif (!empty($_REQUEST['formatdistribution'])) {

	$SQLquery  = 'SELECT fileformat, audio_dataformat, COUNT(*) AS num';
	$SQLquery .= ' FROM files';
	$SQLquery .= ' GROUP BY fileformat, audio_dataformat';
	$SQLquery .= ' ORDER BY num DESC';
	$result = safe_mysql_query($SQLquery);
	echo 'File format distribution:<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="4">';
	echo '<TR><TH>Number</TH><TH>Format</TH></TR>';
	while ($row = mysql_fetch_array($result)) {
		echo '<TR>';
		echo '<TD ALIGN="RIGHT">'.number_format($row['num']).'</TD>';
		echo '<TD><A HREF="'.$_SERVER['PHP_SELF'].'?filetypelist='.$row['fileformat'].'|'.$row['audio_dataformat'].'">'.($row['fileformat'] ? $row['fileformat'] : '<I>unknown</I>').(($row['audio_dataformat'] && ($row['audio_dataformat'] != $row['fileformat'])) ? '.'.$row['audio_dataformat'] : '').'</A></TD>';
		echo '</TR>';
	}
	echo '</TABLE><HR>';

} elseif (!empty($_REQUEST['errorswarnings'])) {

	$SQLquery  = 'SELECT filename, error, warning';
	$SQLquery .= ' FROM files';
	$SQLquery .= ' WHERE (error <> "")';
	$SQLquery .= ' OR (warning <> "")';
	$SQLquery .= ' ORDER BY filename ASC';
	$result = safe_mysql_query($SQLquery);
	echo number_format(mysql_num_rows($result)).' files with errors or warnings:<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="4">';
	echo '<TR><TH>Filename</TH><TH>Error</TH><TH>Warning</TH></TR>';
	while ($row = mysql_fetch_array($result)) {
		echo '<TR>';
		echo '<TD><A HREF="getid3.demo.check.php?filename='.rawurlencode($row['filename']).'">'.$row['filename'].'</A></TD>';
		echo '<TD><UL>'.str_replace("\n", '<LI>', FixTextFields($row['error'])).'</UL></TD>';
		echo '<TD><UL>'.str_replace("\n", '<LI>', FixTextFields($row['warning'])).'</UL></TD>';
		echo '</TR>';
	}
	echo '</TABLE><HR>';

} elseif (!empty($_REQUEST['vbrmethod'])) {

	if ($_REQUEST['vbrmethod'] == '1') {

		$SQLquery  = 'SELECT COUNT(*) AS num, vbr_method';
		$SQLquery .= ' FROM files';
		$SQLquery .= ' GROUP BY vbr_method';
		$SQLquery .= ' ORDER BY vbr_method';
		$result = safe_mysql_query($SQLquery);
		echo 'VBR methods:<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="4">';
		echo '<TR><TH>Count</TH><TH>VBR Method</TH></TR>';
		while ($row = mysql_fetch_array($result)) {
			echo '<TR>';
			echo '<TD ALIGN="RIGHT">'.FixTextFields(number_format($row['num'])).'</TD>';
			echo '<TD><A HREF="'.$_SERVER['PHP_SELF'].'?vbrmethod='.$row['vbr_method'].'">'.FixTextFields($row['vbr_method']).'</A></TD>';
			echo '</TR>';
		}
		echo '</TABLE>';

	} else {

		$SQLquery  = 'SELECT filename';
		$SQLquery .= ' FROM files';
		$SQLquery .= ' WHERE (vbr_method = "'.FixDBFields($_REQUEST['vbrmethod']).'")';
		$result = safe_mysql_query($SQLquery);
		echo number_format(mysql_num_rows($result)).' files with VBR_method of "'.$_REQUEST['vbrmethod'].'":<BR><BR>>';
		while ($row = mysql_fetch_array($result)) {
			echo '<A HREF="getid3.demo.check.php?filename='.rawurlencode($row['filename']).'">'.$row['filename'].'</A><BR>';
		}

	}
	echo '<HR>';

}


echo '<HR><FORM ACTION="'.FixTextFields($_SERVER['PHP_SELF']).'">';
echo '<B>Warning:</B> Scanning a new directory will erase all previous entries in the database!<BR>';
echo 'Directory: <INPUT TYPE="TEXT" NAME="scan" VALUE="'.FixTextFields(!empty($_REQUEST['scan']) ? $_REQUEST['scan'] : '').'"> ';
echo '<INPUT TYPE="SUBMIT" VALUE="Go">';
echo '</FORM>';
echo '<HR><FORM ACTION="'.FixTextFields($_SERVER['PHP_SELF']).'">';
echo 'Re-scanning a new directory will only add new, previously unscanned files into the list (and not erase the database).<BR>';
echo 'Directory: <INPUT TYPE="TEXT" NAME="newscan" VALUE="'.FixTextFields(!empty($_REQUEST['newscan']) ? $_REQUEST['newscan'] : '').'"> ';
echo '<INPUT TYPE="SUBMIT" VALUE="Go">';
echo '</FORM><HR>';
echo '<UL>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?deadfilescheck=1">Remove deleted or changed files from database</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?md5datadupes=1">List files with identical MD5_DATA values</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?artisttitledupes=1">List files with identical artist + title</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?fileextensions=1">File with incorrect file extension</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?formatdistribution=1">File Format Distribution</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?audiobitrates=1">Audio Bitrate Distribution</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?vbrmethod=1">VBR_Method Distribution</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?tagtypes=1">Tag Type Distribution</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?errorswarnings=1">Files with Errors and/or Warnings</A></LI>';
echo '<LI><A HREF="'.$_SERVER['PHP_SELF'].'?rescanerrors=1">Re-scan only files with Errors and/or Warnings</A></LI>';
echo '</UL>';

?>
</BODY>
</HTML>