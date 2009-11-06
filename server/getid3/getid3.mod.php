<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.mod.php - part of getID3()                           //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getMODheaderFilepointer(&$fd, &$ThisFileInfo) {

	fseek($fd, $ThisFileInfo['avdataoffset'] + 1080);
	$FormatID = fread($fd, 4);
	if (!ereg('^(M.K.|[5-9]CHN|[1-3][0-9]CH)$', $FormatID)) {
		$ThisFileInfo['error'] .= "\n".'This is not a known type of MOD file';
		return false;
	}

	$ThisFileInfo['fileformat'] = 'mod';

	$ThisFileInfo['error'] .= "\n".'MOD parsing not enabled in this version of getID3()';
	return false;
}

function getXMheaderFilepointer(&$fd, &$ThisFileInfo) {

	fseek($fd, $ThisFileInfo['avdataoffset']);
	$FormatID = fread($fd, 15);
	if (!ereg('^Extended Module$', $FormatID)) {
		$ThisFileInfo['error'] .= "\n".'This is not a known type of XM-MOD file';
		return false;
	}

	$ThisFileInfo['fileformat'] = 'xm';

	$ThisFileInfo['error'] .= "\n".'XM-MOD parsing not enabled in this version of getID3()';
	return false;
}

function getS3MheaderFilepointer(&$fd, &$ThisFileInfo) {

	fseek($fd, $ThisFileInfo['avdataoffset'] + 44);
	$FormatID = fread($fd, 4);
	if (!ereg('^SCRM$', $FormatID)) {
		$ThisFileInfo['error'] .= "\n".'This is not a ScreamTracker MOD file';
		return false;
	}

	$ThisFileInfo['fileformat'] = 's3m';

	$ThisFileInfo['error'] .= "\n".'ScreamTracker parsing not enabled in this version of getID3()';
	return false;
}

function getITheaderFilepointer(&$fd, &$ThisFileInfo) {

	fseek($fd, $ThisFileInfo['avdataoffset']);
	$FormatID = fread($fd, 4);
	if (!ereg('^IMPM$', $FormatID)) {
		$ThisFileInfo['error'] .= "\n".'This is not an ImpulseTracker MOD file';
		return false;
	}

	$ThisFileInfo['fileformat'] = 'it';

	$ThisFileInfo['error'] .= "\n".'ImpulseTracker parsing not enabled in this version of getID3()';
	return false;
}

?>