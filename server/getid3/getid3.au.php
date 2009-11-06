<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.au.php - part of getID3()                            //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getAUheaderFilepointer(&$fd, &$ThisFileInfo) {

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	$AUheader  = fread($fd, 8);

	if (substr($AUheader, 0, 4) != '.snd') {
		$ThisFileInfo['error'] .= "\n".'Expecting ".snd" at offset '.$ThisFileInfo['avdataoffset'].', found "'.substr($AUheader, 0, 4).'"';
		return false;
	}

	$ThisFileInfo['fileformat']            = 'au';
	$ThisFileInfo['audio']['dataformat']   = 'au';
	$ThisFileInfo['audio']['bitrate_mode'] = 'cbr';

	$ThisFileInfo['au']['header_length']   = BigEndian2Int(substr($AUheader,  4, 4));
	$AUheader .= fread($fd, $ThisFileInfo['au']['header_length'] - 8);
	$ThisFileInfo['avdataoffset'] += $ThisFileInfo['au']['header_length'];

	$ThisFileInfo['au']['data_size']       = BigEndian2Int(substr($AUheader,  8, 4));
	$ThisFileInfo['au']['data_format_id']  = BigEndian2Int(substr($AUheader, 12, 4));
	$ThisFileInfo['au']['sample_rate']     = BigEndian2Int(substr($AUheader, 16, 4));
	$ThisFileInfo['au']['channels']        = BigEndian2Int(substr($AUheader, 20, 4));
	$ThisFileInfo['au']['comment']         =          trim(substr($AUheader, 24));

	$ThisFileInfo['au']['data_format']     = AUdataFormatNameLookup($ThisFileInfo['au']['data_format_id']);
	$ThisFileInfo['au']['used_bits_per_sample'] = AUdataFormatUsedBitsPerSampleLookup($ThisFileInfo['au']['data_format_id']);
	if ($ThisFileInfo['au']['bits_per_sample'] = AUdataFormatBitsPerSampleLookup($ThisFileInfo['au']['data_format_id'])) {
		$ThisFileInfo['audio']['bits_per_sample'] = $ThisFileInfo['au']['bits_per_sample'];
	} else {
		unset($ThisFileInfo['au']['bits_per_sample']);
	}

	$ThisFileInfo['audio']['sample_rate']  = $ThisFileInfo['au']['sample_rate'];
	$ThisFileInfo['audio']['channels']     = $ThisFileInfo['au']['channels'];
	if (!empty($ThisFileInfo['au']['comment'])) {
		$ThisFileInfo['comments']['comment'][]   = $ThisFileInfo['au']['comment'];
	}

	if (($ThisFileInfo['avdataoffset'] + $ThisFileInfo['au']['data_size']) > $ThisFileInfo['avdataend']) {
		$ThisFileInfo['warning'] .= "\n".'Possible truncated file - expecting "'.$ThisFileInfo['au']['data_size'].'" bytes of audio data, only found '.($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']).' bytes"';
	}

	$ThisFileInfo['playtime_seconds'] = $ThisFileInfo['au']['data_size'] / ($ThisFileInfo['au']['sample_rate'] * $ThisFileInfo['au']['channels'] * ($ThisFileInfo['au']['used_bits_per_sample'] / 8));
	$ThisFileInfo['audio']['bitrate'] = ($ThisFileInfo['au']['data_size'] * 8) / $ThisFileInfo['playtime_seconds'];

	return true;
}

function AUdataFormatNameLookup($id) {
	static $AUdataFormatNameLookup = array();
	if (empty($AUdataFormatNameLookup)) {
		$AUdataFormatNameLookup[0]  = 'unspecified format';
		$AUdataFormatNameLookup[1]  = '8-bit mu-law';
		$AUdataFormatNameLookup[2]  = '8-bit linear';
		$AUdataFormatNameLookup[3]  = '16-bit linear';
		$AUdataFormatNameLookup[4]  = '24-bit linear';
		$AUdataFormatNameLookup[5]  = '32-bit linear';
		$AUdataFormatNameLookup[6]  = 'floating-point';
		$AUdataFormatNameLookup[7]  = 'double-precision float';
		$AUdataFormatNameLookup[8]  = 'fragmented sampled data';
		$AUdataFormatNameLookup[9]  = 'SUN_FORMAT_NESTED';
		$AUdataFormatNameLookup[10] = 'DSP program';
		$AUdataFormatNameLookup[11] = '8-bit fixed-point';
		$AUdataFormatNameLookup[12] = '16-bit fixed-point';
		$AUdataFormatNameLookup[13] = '24-bit fixed-point';
		$AUdataFormatNameLookup[14] = '32-bit fixed-point';

		$AUdataFormatNameLookup[16] = 'non-audio display data';
		$AUdataFormatNameLookup[17] = 'SND_FORMAT_MULAW_SQUELCH';
		$AUdataFormatNameLookup[18] = '16-bit linear with emphasis';
		$AUdataFormatNameLookup[19] = '16-bit linear with compression';
		$AUdataFormatNameLookup[20] = '16-bit linear with emphasis + compression';
		$AUdataFormatNameLookup[21] = 'Music Kit DSP commands';
		$AUdataFormatNameLookup[22] = 'SND_FORMAT_DSP_COMMANDS_SAMPLES';
		$AUdataFormatNameLookup[23] = 'CCITT g.721 4-bit ADPCM';
		$AUdataFormatNameLookup[24] = 'CCITT g.722 ADPCM';
		$AUdataFormatNameLookup[25] = 'CCITT g.723 3-bit ADPCM';
		$AUdataFormatNameLookup[26] = 'CCITT g.723 5-bit ADPCM';
		$AUdataFormatNameLookup[27] = 'A-Law 8-bit';
	}
	return (isset($AUdataFormatNameLookup[$id]) ? $AUdataFormatNameLookup[$id] : false);
}

function AUdataFormatBitsPerSampleLookup($id) {
	static $AUdataFormatBitsPerSampleLookup = array();
	if (empty($AUdataFormatBitsPerSampleLookup)) {
		$AUdataFormatBitsPerSampleLookup[1]  = 8;
		$AUdataFormatBitsPerSampleLookup[2]  = 8;
		$AUdataFormatBitsPerSampleLookup[3]  = 16;
		$AUdataFormatBitsPerSampleLookup[4]  = 24;
		$AUdataFormatBitsPerSampleLookup[5]  = 32;
		$AUdataFormatBitsPerSampleLookup[6]  = 32;
		$AUdataFormatBitsPerSampleLookup[7]  = 64;

		$AUdataFormatBitsPerSampleLookup[11] = 8;
		$AUdataFormatBitsPerSampleLookup[12] = 16;
		$AUdataFormatBitsPerSampleLookup[13] = 24;
		$AUdataFormatBitsPerSampleLookup[14] = 32;

		$AUdataFormatBitsPerSampleLookup[18] = 16;
		$AUdataFormatBitsPerSampleLookup[19] = 16;
		$AUdataFormatBitsPerSampleLookup[20] = 16;

		$AUdataFormatBitsPerSampleLookup[23] = 16;

		$AUdataFormatBitsPerSampleLookup[25] = 16;
		$AUdataFormatBitsPerSampleLookup[26] = 16;
		$AUdataFormatBitsPerSampleLookup[27] = 8;
	}
	return (isset($AUdataFormatBitsPerSampleLookup[$id]) ? $AUdataFormatBitsPerSampleLookup[$id] : false);
}

function AUdataFormatUsedBitsPerSampleLookup($id) {
	static $AUdataFormatUsedBitsPerSampleLookup = array();
	if (empty($AUdataFormatUsedBitsPerSampleLookup)) {
		$AUdataFormatUsedBitsPerSampleLookup[1]  = 8;
		$AUdataFormatUsedBitsPerSampleLookup[2]  = 8;
		$AUdataFormatUsedBitsPerSampleLookup[3]  = 16;
		$AUdataFormatUsedBitsPerSampleLookup[4]  = 24;
		$AUdataFormatUsedBitsPerSampleLookup[5]  = 32;
		$AUdataFormatUsedBitsPerSampleLookup[6]  = 32;
		$AUdataFormatUsedBitsPerSampleLookup[7]  = 64;

		$AUdataFormatUsedBitsPerSampleLookup[11] = 8;
		$AUdataFormatUsedBitsPerSampleLookup[12] = 16;
		$AUdataFormatUsedBitsPerSampleLookup[13] = 24;
		$AUdataFormatUsedBitsPerSampleLookup[14] = 32;

		$AUdataFormatUsedBitsPerSampleLookup[18] = 16;
		$AUdataFormatUsedBitsPerSampleLookup[19] = 16;
		$AUdataFormatUsedBitsPerSampleLookup[20] = 16;

		$AUdataFormatUsedBitsPerSampleLookup[23] = 4;

		$AUdataFormatUsedBitsPerSampleLookup[25] = 3;
		$AUdataFormatUsedBitsPerSampleLookup[26] = 5;
		$AUdataFormatUsedBitsPerSampleLookup[27] = 8;
	}
	return (isset($AUdataFormatUsedBitsPerSampleLookup[$id]) ? $AUdataFormatUsedBitsPerSampleLookup[$id] : false);
}

?>