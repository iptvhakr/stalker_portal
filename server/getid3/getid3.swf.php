<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.swf.php - part of getID3()                           //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getSWFHeaderFilepointer(&$fd, &$ThisFileInfo) {
	$ThisFileInfo['fileformat']          = 'swf';
	$ThisFileInfo['video']['dataformat'] = 'swf';

	// http://www.openswf.org/spec/SWFfileformat.html

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);

	$SWFheaderData = fread($fd, 12); // 12 bytes NOT including Frame_Size RECT data

	$ThisFileInfo['swf']['header']['signature']   = substr($SWFheaderData, 0, 3);
	if ($ThisFileInfo['swf']['header']['signature'] != 'FWS') {
		$ThisFileInfo['error'] .= "\n".'"FWS" expected at offset '.$ThisFileInfo['avdataoffset'].', "'.$ThisFileInfo['swf']['header']['signature'].'" found instead.';
		return false;
	}
	$ThisFileInfo['swf']['header']['version']      = LittleEndian2Int(substr($SWFheaderData, 3, 1));
	$ThisFileInfo['swf']['header']['length']       = LittleEndian2Int(substr($SWFheaderData, 4, 4));

	$FrameSizeBitsPerValue = (ord(substr($SWFheaderData, 8, 1)) & 0xF8) >> 3;
	$FrameSizeDataLength   = ceil((5 + (4 * $FrameSizeBitsPerValue)) / 8);
	$SWFheaderData        .= fread($fd, $FrameSizeDataLength);
	$FrameSizeDataString   = str_pad(decbin(ord(substr($SWFheaderData, 8, 1)) & 0x07), 3, '0', STR_PAD_LEFT);
	for ($i = 1; $i < $FrameSizeDataLength; $i++) {
		$FrameSizeDataString .= str_pad(decbin(ord(substr($SWFheaderData, 8 + $i, 1))), 8, '0', STR_PAD_LEFT);
	}
	list($X1, $X2, $Y1, $Y2) = explode("\n", wordwrap($FrameSizeDataString, $FrameSizeBitsPerValue, "\n", 1));
	$ThisFileInfo['swf']['header']['frame_width']  = Bin2Dec($X2);
	$ThisFileInfo['swf']['header']['frame_height'] = Bin2Dec($Y2);

	$ThisFileInfo['swf']['header']['frame_delay']  =    FixedPoint8_8(substr($SWFheaderData,  8 + $FrameSizeDataLength, 2));
	$ThisFileInfo['swf']['header']['frame_count']  = LittleEndian2Int(substr($SWFheaderData, 10 + $FrameSizeDataLength, 2));


	$ThisFileInfo['video']['resolution_x']       = round($ThisFileInfo['swf']['header']['frame_width'] / 20);
	$ThisFileInfo['video']['resolution_y']       = round($ThisFileInfo['swf']['header']['frame_height'] / 20);
	$ThisFileInfo['video']['pixel_aspect_ratio'] = (float) 1;
	switch ($ThisFileInfo['swf']['header']['frame_delay']) {
		case 0:
		case 128:
			// invalid / ignore
			break;

		default:
			$ThisFileInfo['video']['frame_rate'] = 1 / $ThisFileInfo['swf']['header']['frame_delay'];
			break;
	}

	return true;
}

?>