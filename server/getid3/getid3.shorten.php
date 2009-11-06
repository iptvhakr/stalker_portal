<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.shorten.php - part of getID3()                       //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getShortenHeaderFilepointer(&$fd, &$ThisFileInfo) {

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);

	$ShortenHeader = fread($fd, 8);
	if (substr($ShortenHeader, 0, 4) != 'ajkg') {
		$ThisFileInfo['error'] .= "\n".'Expecting "ajkg" at offset '.$ThisFileInfo['avdataoffset'].', found "'.substr($ShortenHeader, 0, 4).'"';
		return false;
	}
	$ThisFileInfo['fileformat'] = 'shn';
	$ThisFileInfo['shn']['version'] = LittleEndian2Int(substr($ShortenHeader, 4, 1));



	fseek($fd, $ThisFileInfo['avdataend'] - 12, SEEK_SET);
	$SeekTableSignatureTest = fread($fd, 12);
	$ThisFileInfo['shn']['seektable']['present'] = (bool) (substr($SeekTableSignatureTest, 4, 8) == 'SHNAMPSK');
	if ($ThisFileInfo['shn']['seektable']['present']) {
		$ThisFileInfo['shn']['seektable']['length'] = LittleEndian2Int(substr($SeekTableSignatureTest, 0, 4));
		$ThisFileInfo['shn']['seektable']['offset'] = $ThisFileInfo['avdataend'] - $ThisFileInfo['shn']['seektable']['length'];
		fseek($fd, $ThisFileInfo['shn']['seektable']['offset'], SEEK_SET);
		$SeekTableData = fread($fd, $ThisFileInfo['shn']['seektable']['length'] - 12);
		if (substr($SeekTableData, 0, 4) != 'SEEK') {
			$ThisFileInfo['error'] .= "\n".'Expecting "SEEK" at offset '.$ThisFileInfo['shn']['seektable']['offset'].', found "'.substr($SeekTableData, 0, 4).'"';
			return false;
		}

	}

	$ThisFileInfo['error'] .= "\n".'Shorten parsing not enabled in this version of getID3()';
	return false;

}

?>