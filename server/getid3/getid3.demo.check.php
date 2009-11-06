<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.check.php - part of getID3()                         //
// Sample script for browsing/scanning files and displaying    //
// information returned by getID3()                            //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

require_once('getid3.php');

$getID3checkColor_Head           = 'CCCCDD';
$getID3checkColor_DirectoryLight = 'EEBBBB';
$getID3checkColor_DirectoryDark  = 'FFCCCC';
$getID3checkColor_FileLight      = 'EEEEEE';
$getID3checkColor_FileDark       = 'DDDDDD';
$getID3checkColor_UnknownLight   = 'CCCCFF';
$getID3checkColor_UnknownDark    = 'BBBBDD';


echo '<HTML><HEAD>';
echo '<TITLE>getID3() - getid3.check.php (sample script)</TITLE>';
echo '<STYLE>BODY,TD,TH { font-family: sans-serif; font-size: 9pt; }</STYLE>';
echo '</HEAD><BODY>';

if (isset($_REQUEST['deletefile'])) {
	if (file_exists($_REQUEST['deletefile'])) {
		if (unlink($_REQUEST['deletefile'])) {
			echo '<SCRIPT LANGUAGE="JavaScript">alert("Successfully deleted '.addslashes($_REQUEST['deletefile']).'");</SCRIPT>';
		} else {
			echo '<SCRIPT LANGUAGE="JavaScript">alert("FAILED to delete '.addslashes($_REQUEST['deletefile']).'");</SCRIPT>';
		}
	} else {
		echo '<SCRIPT LANGUAGE="JavaScript">alert("'.addslashes($_REQUEST['deletefile']).' does not exist - cannot delete");</SCRIPT>';
	}
}

$AssumeFormatExtensions = ListOfAssumeFormatExtensions();

if (isset($_REQUEST['filename'])) {
	if (!file_exists($_REQUEST['filename'])) {
		die($_REQUEST['filename'].' does not exist');
	}
	$starttime = getmicrotime();
	$AutoGetMD5data = (bool) (filesize($_REQUEST['filename']) < 52428800);
	$AutoGetMD5file = (bool) (filesize($_REQUEST['filename']) < 52428800);
	if (isset($_REQUEST['assumeFormat'])) {
		$ThisFileInfo = GetAllFileInfo($_REQUEST['filename'], $_REQUEST['assumeFormat'], $AutoGetMD5file, $AutoGetMD5data, true); // auto-get md5_data if filesize < 50MB
	} else {
		$ThisFileInfo = GetAllFileInfo($_REQUEST['filename'], '', $AutoGetMD5file, $AutoGetMD5data, true); // auto-get md5_data if filesize < 50MB
		if (empty($ThisFileInfo['fileformat']) || ($ThisFileInfo['fileformat'] == 'id3')) {
			if (isset($AssumeFormatExtensions[strtolower(fileextension($_REQUEST['filename']))])) {
				$ThisFileInfo = GetAllFileInfo($_REQUEST['filename'], $AssumeFormatExtensions[strtolower(fileextension($_REQUEST['filename']))], $AutoGetMD5file, $AutoGetMD5data, true); // auto-get md5_data if filesize < 50MB
			}
		}
	}

	$listdirectory = dirname(SafeStripSlashes($_REQUEST['filename']));
	$listdirectory = realpath($listdirectory); // get rid of /../../ references

	if (substr(php_uname(), 0, 7) == 'Windows') {
		// this mostly just gives a consistant look to Windows and *nix filesystems
		// (windows uses \ as directory seperator, *nix uses /)
		$listdirectory = str_replace('\\', '/', $listdirectory.'/');
	}

	if (strstr($_REQUEST['filename'], 'http://') || strstr($_REQUEST['filename'], 'ftp://')) {
		echo '<I>Cannot browse remote filesystems</I><BR>';
	} else {
		echo 'Browse: <A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'">'.$listdirectory.'</A><BR>';
	}

	echo 'Parse this file as: ';
	$allowedFormats = array('zip', 'ogg', 'riff', 'mpeg', 'midi', 'aac', 'mp3');
	foreach ($allowedFormats as $possibleFormat) {
		if (isset($_REQUEST['assumeFormat']) && ($_REQUEST['assumeFormat'] == $possibleFormat)) {
			echo '<B>'.$possibleFormat.'</B> | ';
		} else {
			echo '<A HREF="'.$_SERVER['PHP_SELF'].'?filename='.urlencode($_REQUEST['filename']).'&assumeFormat='.$possibleFormat.'">'.$possibleFormat.'</A> | ';
		}
	}
	if (isset($_REQUEST['assumeFormat'])) {
		echo '<A HREF="'.$_SERVER['PHP_SELF'].'?filename='.urlencode($_REQUEST['filename']).'">default</A><BR>';
	} else {
		echo '<B>default</B><BR>';
	}

	echo table_var_dump($ThisFileInfo);
	$endtime = getmicrotime();
	echo 'File parsed in '.number_format($endtime - $starttime, 3).' seconds.<BR>';

} else {

	$listdirectory = (isset($_REQUEST['listdirectory']) ? SafeStripSlashes($_REQUEST['listdirectory']) : '.');
	$listdirectory = realpath($listdirectory); // get rid of /../../ references
	$currentfulldir = $listdirectory.'/';

	if (substr(php_uname(), 0, 7) == 'Windows') {
		// this mostly just gives a consistant look to Windows and *nix filesystems
		// (windows uses \ as directory seperator, *nix uses /)
		$currentfulldir = str_replace('\\', '/', $listdirectory.'/');
	}

	if ($handle = @opendir($listdirectory)) {

		echo str_repeat(' ', 300); // IE buffers the first 300 or so chars, making this progressive display useless - fill the buffer with spaces
		echo 'Processing';

		$starttime = getmicrotime();

		$TotalScannedKnownFiles   = 0;
		$TotalScannedUnknownFiles = 0;
		$TotalScannedFilesize     = 0;
		$TotalScannedPlaytime     = 0;
		$TotalScannedBitrate      = 0;

		while ($file = readdir($handle)) {
			set_time_limit(30); // allocate another 30 seconds to process this file - should go much quicker than this unless intense processing (like bitrate histogram analysis) is enabled
			echo ' .'; // progress indicator dot
			flush();  // make sure the dot is shown, otherwise it's useless
			$currentfilename = $listdirectory.'/'.$file;

			// symbolic-link-resolution enhancements by davidbullock@tech-center.com
			$TargetObject     = realpath($currentfilename);  // Find actual file path, resolve if it's a symbolic link
			$TargetObjectType = filetype($TargetObject);     // Check file type without examining extension

			if($TargetObjectType == 'dir') {
				switch ($file) {
					case '..':
						$ParentDir = realpath($file.'/..').'/';
						if (substr(php_uname(), 0, 7) == 'Windows') {
							$ParentDir = str_replace('\\', '/', $ParentDir);
						}
						$DirectoryContents["$currentfulldir"]['dir']["$file"]['filename'] = $ParentDir;
						break;

					case '.':
						// ignore
						break;

					default:
						$DirectoryContents["$currentfulldir"]['dir']["$file"]['filename'] = $file;
						break;
				}

			} elseif ($TargetObjectType == 'file') {

				$fileinformation = GetAllFileInfo($currentfilename, false, isset($_REQUEST['ShowMD5']), isset($_REQUEST['ShowMD5']));

				$TotalScannedFilesize += @$fileinformation['filesize'];

				if (empty($fileinformation['fileformat']) || ($fileinformation['fileformat'] == 'id3')) {
					// auto-detect couldn't find the file format (probably corrupt header?), re-scan based on extension, if applicable
					if (isset($AssumeFormatExtensions[fileextension($currentfilename)])) {
						$fileinformation = GetAllFileInfo($currentfilename, $AssumeFormatExtensions[fileextension($currentfilename)], isset($_REQUEST['ShowMD5']), isset($_REQUEST['ShowMD5']));
					}
				}


				if (!empty($fileinformation['fileformat'])) {
					$DirectoryContents["$currentfulldir"]['known']["$file"] = $fileinformation;
					$TotalScannedPlaytime += @$fileinformation['playtime_seconds'];
					$TotalScannedBitrate  += @$fileinformation['bitrate'];
					$TotalScannedKnownFiles++;
				} else {
					$DirectoryContents["$currentfulldir"]['other']["$file"] = $fileinformation;
					$DirectoryContents["$currentfulldir"]['other']["$file"]['playtime_string'] = '-';
					$TotalScannedUnknownFiles++;
				}
			}
		}
		$endtime = getmicrotime();
		closedir($handle);
		echo 'done<BR>';
		echo 'Directory scanned in '.number_format($endtime - $starttime, 2).' seconds.<BR>';
		flush();

		$columnsintable = 14;
		echo '<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="3">';

		echo '<TR BGCOLOR="#'.$getID3checkColor_Head.'"><TH COLSPAN="'.$columnsintable.'">Files in '.$currentfulldir.'</TH></TR>';
		$rowcounter = 0;
		foreach ($DirectoryContents as $dirname => $val) {
			if (is_array($DirectoryContents["$dirname"]['dir'])) {
				uksort($DirectoryContents["$dirname"]['dir'], 'MoreNaturalSort');
				foreach ($DirectoryContents["$dirname"]['dir'] as $filename => $fileinfo) {
					echo '<TR BGCOLOR="#'.(($rowcounter++ % 2) ? $getID3checkColor_DirectoryDark : $getID3checkColor_DirectoryLight).'">';
					if ($filename == '..') {
						echo '<TD COLSPAN="'.$columnsintable.'">Parent directory: <A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($dirname.$filename).'"><B>';
						if (substr(php_uname(), 0, 7) == 'Windows') {
							echo str_replace('\\', '/', realpath($dirname.$filename));
						} else {
							echo realpath($dirname.$filename);
						}
						echo '/</B></A></TD>';
					} else {
						echo '<TD COLSPAN="'.$columnsintable.'"><A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($dirname.$filename).'"><B>'.$filename.'</B></A></TD>';
					}
					echo '</TR>';
				}
			}

			echo '<TR BGCOLOR="#'.$getID3checkColor_Head.'">';
			echo '<TH>Filename</TH>';
			echo '<TH>File Size</TH>';
			echo '<TH>Format</TH>';
			echo '<TH>Playtime</TH>';
			echo '<TH>Bitrate</TH>';
			echo '<TH>Artist</TH>';
			echo '<TH>Title</TH>';
			if (isset($_REQUEST['ShowMD5'])) {
				echo '<TH>MD5 File (File) (<A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'">disable</A>)</TH>';
				echo '<TH>MD5 Data (File) (<A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'">disable</A>)</TH>';
				echo '<TH>MD5 Data (Source) (<A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'">disable</A>)</TH>';
			} else {
				echo '<TH COLSPAN="3">MD5 Data (<A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'&ShowMD5=1">enable</A>)</TH>';
			}
			echo '<TH>Tags</TH>';
			echo '<TH>Errors</TH>';
			echo '<TH>Edit</TH>';
			echo '<TH>Delete</TH>';
			echo '</TR>';

			if (isset($DirectoryContents["$dirname"]['known']) && is_array($DirectoryContents["$dirname"]['known'])) {
				uksort($DirectoryContents["$dirname"]['known'], 'MoreNaturalSort');
				foreach ($DirectoryContents["$dirname"]['known'] as $filename => $fileinfo) {
					echo '<TR BGCOLOR="#'.(($rowcounter++ % 2) ? $getID3checkColor_FileDark : $getID3checkColor_FileLight).'">';
					echo '<TD><A HREF="'.$_SERVER['PHP_SELF'].'?filename='.urlencode($dirname.$filename).'" TITLE="View detailed analysis">'.SafeStripSlashes($filename).'</A></TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.number_format($fileinfo['filesize']).'</TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.NiceDisplayFiletypeFormat($fileinfo).'</TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.(isset($fileinfo['playtime_string']) ? $fileinfo['playtime_string'] : '-').'</TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.(isset($fileinfo['bitrate']) ? BitrateText($fileinfo['bitrate'] / 1000) : '-').'</TD>';
					echo '<TD ALIGN="LEFT">&nbsp;'.(isset($fileinfo['comments']['artist']) ? implode("\n", $fileinfo['comments']['artist']) : '').'</TD>';
					echo '<TD ALIGN="LEFT">&nbsp;'.(isset($fileinfo['comments']['title']) ? implode("\n", $fileinfo['comments']['title']) : '').'</TD>';
					if (isset($_REQUEST['ShowMD5'])) {
						echo '<TD ALIGN="LEFT"><TT>'.(isset($fileinfo['md5_file'])        ? $fileinfo['md5_file']        : '&nbsp;').'</TT></TD>';
						echo '<TD ALIGN="LEFT"><TT>'.(isset($fileinfo['md5_data'])        ? $fileinfo['md5_data']        : '&nbsp;').'</TT></TD>';
						echo '<TD ALIGN="LEFT"><TT>'.(isset($fileinfo['md5_data_source']) ? $fileinfo['md5_data_source'] : '&nbsp;').'</TT></TD>';
					} else {
						echo '<TD ALIGN="CENTER" COLSPAN="3">-</TD>';
					}
					echo '<TD ALIGN="LEFT">&nbsp;'.implode(', ', $fileinfo['tags']).'</TD>';

					echo '<TD ALIGN="LEFT">&nbsp;';
					if (!empty($fileinfo['warning'])) {
						echo '<A HREF="javascript:alert(\''.str_replace("\n", '\\n', FixTextFields($fileinfo['warning'])).'\');" TITLE="'.FixTextFields($fileinfo['warning']).'">warning</ACRONYM><BR>';
					}
					if (!empty($fileinfo['error'])) {
						echo '<A HREF="javascript:alert(\''.str_replace("\n", '\\n', FixTextFields($fileinfo['error'])).'\');" TITLE="'.FixTextFields($fileinfo['error']).'">error</ACRONYM><BR>';
					}
					echo '</TD>';

					echo '<TD ALIGN="LEFT">&nbsp;';
					if (in_array('id3v1', $fileinfo['tags']) || (in_array('id3v2', $fileinfo['tags']) && ($fileinfo['fileformat'] == 'mp3')) || in_array('ape', $fileinfo['tags'])) {
						echo '<A HREF="getid3.demo.write.php?EditorFilename='.urlencode($dirname.$filename).'" TITLE="Edit ID3 tag">edit&nbsp;ID3';
					} elseif (in_array('vorbiscomment', $fileinfo['tags'])) {
						echo '<A HREF="getid3.demo.write.php?EditorFilename='.urlencode($dirname.$filename).'" TITLE="Edit Ogg comment tags">edit&nbsp;tags';
					}
					echo '</TD>';
					echo '<TD ALIGN="LEFT">&nbsp;<A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename).'" onClick="return confirm(\'Are you sure you want to delete '.addslashes($dirname.$filename).'? \n(this action cannot be un-done)\');" TITLE="Permanently delete '."\n".FixTextFields($filename)."\n".' from'."\n".' '.FixTextFields($dirname).'">delete</A></TD>';
					echo '</TR>';
				}
			}

			if (isset($DirectoryContents["$dirname"]['other']) && is_array($DirectoryContents["$dirname"]['other'])) {
				uksort($DirectoryContents["$dirname"]['other'], 'MoreNaturalSort');
				foreach ($DirectoryContents["$dirname"]['other'] as $filename => $fileinfo) {
					echo '<TR BGCOLOR="#'.(($rowcounter++ % 2) ? $getID3checkColor_UnknownDark : $getID3checkColor_UnknownLight).'">';
					echo '<TD><A HREF="'.$_SERVER['PHP_SELF'].'?filename='.urlencode($dirname.$filename).'"><I>'.$filename.'</I></A></TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.(isset($fileinfo['filesize']) ? number_format($fileinfo['filesize']) : '-').'</TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.NiceDisplayFiletypeFormat($fileinfo).'</TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.(isset($fileinfo['playtime_string']) ? $fileinfo['playtime_string'] : '-').'</TD>';
					echo '<TD ALIGN="RIGHT">&nbsp;'.(isset($fileinfo['bitrate']) ? BitrateText($fileinfo['bitrate'] / 1000) : '-').'</TD>';
					echo '<TD ALIGN="LEFT">&nbsp;</TD>'; // Artist
					echo '<TD ALIGN="LEFT">&nbsp;</TD>'; // Title
					echo '<TD ALIGN="LEFT" COLSPAN="3">&nbsp;</TD>'; // MD5_data
					echo '<TD ALIGN="LEFT">&nbsp;</TD>'; // Tags
					echo '<TD ALIGN="LEFT">&nbsp;</TD>'; // Warning/Error
					echo '<TD ALIGN="LEFT">&nbsp;</TD>'; // Edit
					echo '<TD ALIGN="LEFT">&nbsp;<A HREF="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename).'" onClick="return confirm(\'Are you sure you want to delete '.addslashes($dirname.$filename).'? \n(this action cannot be un-done)\');" TITLE="Permanently delete '.addslashes($dirname.$filename).'">delete</A></TD>';
					echo '</TR>';
				}
			}

			echo '<TR BGCOLOR="#'.$getID3checkColor_Head.'">';
			echo '<TD><B>Average:</B></TD>';
			echo '<TD ALIGN="RIGHT">'.number_format($TotalScannedFilesize / max($TotalScannedKnownFiles, 1)).'</TD>';
			echo '<TD>&nbsp;</TD>';
			echo '<TD ALIGN="RIGHT">'.PlaytimeString($TotalScannedPlaytime / max($TotalScannedKnownFiles, 1)).'</TD>';
			echo '<TD ALIGN="RIGHT">'.BitrateText(round(($TotalScannedBitrate / 1000) / max($TotalScannedKnownFiles, 1))).'</TD>';
			echo '<TD ROWSPAN="2" COLSPAN="'.($columnsintable - 5).'">Identified Files: '.$TotalScannedKnownFiles.'<BR>Unknown Files: '.$TotalScannedUnknownFiles.'</TD>';
			echo '</TR>';
			echo '<TR BGCOLOR="#'.$getID3checkColor_Head.'">';
			echo '<TD><B>Total:</B></TD>';
			echo '<TD ALIGN="RIGHT">'.number_format($TotalScannedFilesize).'</TD>';
			echo '<TD>&nbsp;</TD>';
			echo '<TD ALIGN="RIGHT">'.PlaytimeString($TotalScannedPlaytime).'</TD>';
			echo '<TD>&nbsp;</TD>';
			echo '</TR>';
		}
		echo '</TABLE>';
	} else {
		echo '<B>ERROR: Could not open directory: <U>'.$currentfulldir.'</U></B><BR>';
	}
}
echo PoweredBygetID3();
echo '</BODY></HTML>';

function NiceDisplayFiletypeFormat(&$fileinfo) {

	if (empty($fileinfo['fileformat'])) {
		return '-';
	}

	$output  = $fileinfo['fileformat'];
	if (empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		return $output;  // 'gif'
	}
	if (empty($fileinfo['video']['dataformat']) && !empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['audio']['dataformat']) {
			return $output; // 'mp3'
		}
		$output .= '.'.$fileinfo['audio']['dataformat']; // 'ogg.flac'
		return $output;
	}
	if (!empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'mpeg'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // 'riff.avi'
		return $output;
	}
	if ($fileinfo['video']['dataformat'] == $fileinfo['audio']['dataformat']) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'real'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // any examples?
		return $output;
	}
	$output .= '.'.$fileinfo['video']['dataformat'];
	$output .= '.'.$fileinfo['audio']['dataformat']; // asf.wmv.wma
	return $output;

}

function ListOfAssumeFormatExtensions() {
	// These values should almost never get used - the only use for them
	// is to possibly help getID3() correctly identify a file that has
	// garbage data at the beginning of the file, but a correct filename
	// extension.

	//$AssumeFormatExtensions[<filename extension>]  = <file format>;

	$AssumeFormatExtensions['aac']  = 'aac';
	$AssumeFormatExtensions['iff']  = 'aiff';
	$AssumeFormatExtensions['aif']  = 'aiff';
	$AssumeFormatExtensions['aifc'] = 'aiff';
	$AssumeFormatExtensions['iff']  = 'aiff';
	$AssumeFormatExtensions['aiff'] = 'aiff';
	$AssumeFormatExtensions['wmv']  = 'asf';
	$AssumeFormatExtensions['wma']  = 'asf';
	$AssumeFormatExtensions['asf']  = 'asf';
	$AssumeFormatExtensions['au']   = 'au';
	$AssumeFormatExtensions['bmp']  = 'bmp';
	$AssumeFormatExtensions['mod']  = 'bonk';
	$AssumeFormatExtensions['bonk'] = 'bonk';
	$AssumeFormatExtensions['flac'] = 'flac';
	$AssumeFormatExtensions['gif']  = 'gif';
	$AssumeFormatExtensions['iso']  = 'iso';
	$AssumeFormatExtensions['jpeg'] = 'jpg';
	$AssumeFormatExtensions['jpg']  = 'jpg';
	$AssumeFormatExtensions['la']   = 'la';
	$AssumeFormatExtensions['pac']  = 'lpac';
	$AssumeFormatExtensions['mac']  = 'mac';
	$AssumeFormatExtensions['ape']  = 'mac';
	$AssumeFormatExtensions['mid']  = 'midi';
	$AssumeFormatExtensions['midi'] = 'midi';
	$AssumeFormatExtensions['mid']  = 'midi';
	$AssumeFormatExtensions['xm']   = 'mod';
	$AssumeFormatExtensions['it']   = 'mod';
	$AssumeFormatExtensions['s3m']  = 'mod';
	$AssumeFormatExtensions['mp3']  = 'mp3';
	$AssumeFormatExtensions['mp2']  = 'mp3';
	$AssumeFormatExtensions['mp1']  = 'mp3';
	$AssumeFormatExtensions['mpc']  = 'mpc';
	$AssumeFormatExtensions['mpg']  = 'mpeg';
	$AssumeFormatExtensions['mpeg'] = 'mpeg';
	$AssumeFormatExtensions['nsv']  = 'nsv';
	$AssumeFormatExtensions['ofr']  = 'ofr';
	$AssumeFormatExtensions['spx']  = 'ogg';
	$AssumeFormatExtensions['ogg']  = 'ogg';
	$AssumeFormatExtensions['png']  = 'png';
	$AssumeFormatExtensions['mov']  = 'quicktime';
	$AssumeFormatExtensions['qt']   = 'quicktime';
	$AssumeFormatExtensions['rar']  = 'rar';
	$AssumeFormatExtensions['ra']   = 'real';
	$AssumeFormatExtensions['ram']  = 'real';
	$AssumeFormatExtensions['rm']   = 'real';
	$AssumeFormatExtensions['wav']  = 'riff';
	$AssumeFormatExtensions['wv']   = 'riff';
	$AssumeFormatExtensions['vox']  = 'riff';
	$AssumeFormatExtensions['cda']  = 'riff';
	$AssumeFormatExtensions['xvid'] = 'riff';
	$AssumeFormatExtensions['avi']  = 'riff';
	$AssumeFormatExtensions['divx'] = 'riff';
	$AssumeFormatExtensions['avi']  = 'riff';
	$AssumeFormatExtensions['wav']  = 'riff';
	$AssumeFormatExtensions['rka']  = 'rkau';
	$AssumeFormatExtensions['swf']  = 'swf';
	$AssumeFormatExtensions['sz']   = 'szip';
	$AssumeFormatExtensions['voc']  = 'voc';
	$AssumeFormatExtensions['vqf']  = 'vqf';
	$AssumeFormatExtensions['zip']  = 'zip';

	return $AssumeFormatExtensions;
}

?>