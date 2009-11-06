<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.exe.php - part of getID3()                           //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getLPACHeaderFilepointer(&$fd, &$ThisFileInfo) {

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	$LPACheader = fread($fd, 14);
	if (substr($LPACheader, 0, 4) != 'LPAC') {
		$ThisFileInfo['error'] .= "\n".'Expected "LPAC" at offset '.$ThisFileInfo['avdataoffset'].', found "'.$StreamMarker.'"';
		return false;
	}
	$ThisFileInfo['avdataoffset'] += 14;

	$ThisFileInfo['lpac']['file_version']      = BigEndian2Int(substr($LPACheader,  4, 1));
	$ThisFileInfo['lpac']['raw']['audio_type'] = BigEndian2Int(substr($LPACheader,  5, 1));
	$ThisFileInfo['lpac']['total_samples']     = BigEndian2Int(substr($LPACheader,  6, 4));
	$ThisFileInfo['lpac']['raw']['parameters'] = BigEndian2Int(substr($LPACheader, 10, 4));

	$ThisFileInfo['lpac']['flags']['is_wave']  = (bool) ($ThisFileInfo['lpac']['raw']['audio_type'] & 0x40);
	$ThisFileInfo['lpac']['flags']['stereo']   = (bool) ($ThisFileInfo['lpac']['raw']['audio_type'] & 0x04);
	$ThisFileInfo['lpac']['flags']['24_bit']   = (bool) ($ThisFileInfo['lpac']['raw']['audio_type'] & 0x02);
	$ThisFileInfo['lpac']['flags']['16_bit']   = (bool) ($ThisFileInfo['lpac']['raw']['audio_type'] & 0x01);

	if ($ThisFileInfo['lpac']['flags']['24_bit'] && $ThisFileInfo['lpac']['flags']['16_bit']) {
		$ThisFileInfo['warning'] .= "\n".'24-bit and 16-bit flags cannot both be set';
	}

	$ThisFileInfo['lpac']['flags']['fast_compress']             =  (bool) ($ThisFileInfo['lpac']['raw']['parameters'] & 0x40000000);
	$ThisFileInfo['lpac']['flags']['random_access']             =  (bool) ($ThisFileInfo['lpac']['raw']['parameters'] & 0x08000000);
	$ThisFileInfo['lpac']['block_length']                       = pow(2, (($ThisFileInfo['lpac']['raw']['parameters'] & 0x07000000) >> 24)) * 256;
	$ThisFileInfo['lpac']['flags']['adaptive_prediction_order'] =  (bool) ($ThisFileInfo['lpac']['raw']['parameters'] & 0x00800000);
	$ThisFileInfo['lpac']['flags']['adaptive_quantization']     =  (bool) ($ThisFileInfo['lpac']['raw']['parameters'] & 0x00400000);
	$ThisFileInfo['lpac']['flags']['joint_stereo']              =  (bool) ($ThisFileInfo['lpac']['raw']['parameters'] & 0x00040000);
	$ThisFileInfo['lpac']['flags']['quantization']              =         ($ThisFileInfo['lpac']['raw']['parameters'] & 0x00001F00) >> 8;
	$ThisFileInfo['lpac']['flags']['max_prediction_order']      =         ($ThisFileInfo['lpac']['raw']['parameters'] & 0x0000003F);

	if ($ThisFileInfo['lpac']['flags']['fast_compress'] && ($ThisFileInfo['lpac']['flags']['max_prediction_order'] != 3)) {
		$ThisFileInfo['warning'] .= "\n".'max_prediction_order expected to be "3" if fast_compress is true, actual value is "'.$ThisFileInfo['lpac']['flags']['max_prediction_order'].'"';
	}
	switch ($ThisFileInfo['lpac']['file_version']) {
		case 6:
			if ($ThisFileInfo['lpac']['flags']['adaptive_quantization']) {
				$ThisFileInfo['warning'] .= "\n".'adaptive_quantization expected to be false in LPAC file stucture v6, actually true';
			}
			if ($ThisFileInfo['lpac']['flags']['quantization'] != 20) {
				$ThisFileInfo['warning'] .= "\n".'Quantization expected to be 20 in LPAC file stucture v6, actually '.$ThisFileInfo['lpac']['flags']['Q'];
			}
			break;

		default:
			$ThisFileInfo['warning'] .= "\n".'This version of getID3() only supports LPAC file format version 6, this file is version '.$ThisFileInfo['lpac']['file_version'].' - please report to info@getid3.org';
			break;
	}

	require_once(GETID3_INCLUDEPATH.'getid3.riff.php');
	$dummy = array('avdataoffset'=>$ThisFileInfo['avdataoffset'], 'avdataend'=>$ThisFileInfo['avdataend'], 'filesize'=>$ThisFileInfo['filesize'], 'error'=>$ThisFileInfo['error'], 'warning'=>$ThisFileInfo['warning'], 'tags'=>$ThisFileInfo['tags'], 'comments'=>$ThisFileInfo['comments']);
	getRIFFHeaderFilepointer($fd, $dummy);
	$ThisFileInfo['avdataoffset']         = $dummy['avdataoffset'];
	$ThisFileInfo['RIFF']                 = $dummy['RIFF'];
	$ThisFileInfo['error']                = $dummy['error'];
	$ThisFileInfo['warning']              = $dummy['warning'];
	$ThisFileInfo['comments']             = $dummy['comments'];
	$ThisFileInfo['audio']['sample_rate'] = $dummy['audio']['sample_rate'];

	$ThisFileInfo['fileformat']               = 'lpac';
	$ThisFileInfo['audio']['dataformat']      = 'lpac';
	$ThisFileInfo['audio']['lossless']        = true;
	$ThisFileInfo['audio']['bitrate_mode']    = 'vbr';
	$ThisFileInfo['audio']['channels']        = ($ThisFileInfo['lpac']['flags']['stereo'] ? 2 : 1);
	if ($ThisFileInfo['lpac']['flags']['24_bit']) {
		$ThisFileInfo['audio']['bits_per_sample'] = $ThisFileInfo['RIFF']['audio'][0]['bits_per_sample'];
	} elseif ($ThisFileInfo['lpac']['flags']['16_bit']) {
		$ThisFileInfo['audio']['bits_per_sample'] = 16;
	} else {
		$ThisFileInfo['audio']['bits_per_sample'] = 8;
	}

	$ThisFileInfo['playtime_seconds'] = $ThisFileInfo['lpac']['total_samples'] / $ThisFileInfo['audio']['sample_rate'];
	$ThisFileInfo['audio']['bitrate'] = (($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / $ThisFileInfo['playtime_seconds'];

	return true;
}

?>