<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>			   //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.simple.php - part of getID3()                        //
// Sample script for scanning a single directory and           //
// displaying a few pieces of information for each file        //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

echo '<HTML><HEAD><STYLE>BODY, TD, TH { font-family: sans-serif; font-size: 10pt; }</STYLE></HEAD><BODY>';


// include getID3() library (can be in a different directory if full path is specified)
require_once('getid3.php');

$DirectoryToScan = '.';
$dir = opendir($DirectoryToScan); // change to whatever directory you want to scan
while (($file = readdir($dir)) !== false) {
	$FullFileName = realpath($DirectoryToScan.'/'.$file);
	if (is_file($FullFileName)) {
		set_time_limit(30);
		$ThisFileInfo = GetAllFileInfo($FullFileName, '', true, true, true);

		// re-scan file more aggressively if file is corrupted somehow and first scan did not correctly identify
		if (empty($ThisFileInfo['fileformat']) || ($ThisFileInfo['fileformat'] == 'id3')) {
			if (isset($formatExtensions[strtolower(fileextension($FullFileName))])) {
				$ThisFileInfo = GetAllFileInfo($FullFileName, $formatExtensions[strtolower(fileextension($FullFileName))], true, true, true);
			}
		}

		// output desired information in whatever format you want
		echo $ThisFileInfo['filenamepath'].'<BR>';
		if (!empty($ThisFileInfo['comments']['artist'])) {
			echo implode(', ', $ThisFileInfo['comments']['artist']).'<BR>';
		}
		if (!empty($ThisFileInfo['audio']['bitrate'])) {
			echo round($ThisFileInfo['audio']['bitrate'] / 1000).' kbps<BR>';
		}
		if (!empty($ThisFileInfo['playtime_string'])) {
			echo $ThisFileInfo['playtime_string'].'<BR>';
		}
		echo '<HR>';
	}
}

?>
</BODY>
</HTML>