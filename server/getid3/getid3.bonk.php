<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.bonk.php - part of getID3()                          //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getBonkHeaderFilepointer(&$fd, &$ThisFileInfo) {

	$ThisFileInfo['bonk']['dataoffset'] = $ThisFileInfo['avdataoffset'];
	$ThisFileInfo['bonk']['dataend']    = $ThisFileInfo['avdataend'];
	$ThisFileInfo['audio']['encoder']   = 'Extended BONK v0.9+'; // overridden below if different

	// scan-from-end method, for v0.6 and higher
	fseek($fd, $ThisFileInfo['bonk']['dataend'] - 8, SEEK_SET);
	$PossibleBonkTag = fread($fd, 8);
	while (BonkIsValidTagName(substr($PossibleBonkTag, 4, 4), true)) {
		$BonkTagSize = LittleEndian2Int(substr($PossibleBonkTag, 0, 4));
		fseek($fd, 0 - $BonkTagSize, SEEK_CUR);
		$BonkTagOffset = ftell($fd);
		$TagHeaderTest = fread($fd, 5);
		if (($TagHeaderTest{0} != "\x00") || (substr($PossibleBonkTag, 4, 4) != strtolower(substr($PossibleBonkTag, 4, 4)))) {
			$ThisFileInfo['error'] .= "\n".'Expecting "Ø'.strtoupper(substr($PossibleBonkTag, 4, 4)).'" at offset '.$BonkTagOffset.', found "'.$TagHeaderTest.'"';
			return false;
		}
		$BonkTagName = substr($TagHeaderTest, 1, 4);

		$ThisFileInfo['bonk']["$BonkTagName"]['size']   = $BonkTagSize;
		$ThisFileInfo['bonk']["$BonkTagName"]['offset'] = $BonkTagOffset;
		HandleBonkTags($fd, $BonkTagName, $ThisFileInfo);
		$NextTagEndOffset = $BonkTagOffset - 8;
		if ($NextTagEndOffset < $ThisFileInfo['bonk']['dataoffset']) {
			return true;
		}
		fseek($fd, $NextTagEndOffset, SEEK_SET);
		$PossibleBonkTag = fread($fd, 8);
	}

	// seek-from-beginning method for v0.4 and v0.5
	if (empty($ThisFileInfo['bonk']['BONK'])) {
		fseek($fd, $ThisFileInfo['bonk']['dataoffset'], SEEK_SET);
		do {
			$TagHeaderTest = fread($fd, 5);
			switch ($TagHeaderTest) {
				case "\x00".'BONK':
					if (empty($ThisFileInfo['audio']['encoder'])) {
						$ThisFileInfo['audio']['encoder'] = 'BONK v0.4';
					}
					break;

				case "\x00".'INFO':
					$ThisFileInfo['audio']['encoder'] = 'Extended BONK v0.5';
					break;

				default:
					break 2;
			}
			$BonkTagName = substr($TagHeaderTest, 1, 4);
			$ThisFileInfo['bonk']["$BonkTagName"]['size']   = $ThisFileInfo['bonk']['dataend'] - $ThisFileInfo['bonk']['dataoffset'];
			$ThisFileInfo['bonk']["$BonkTagName"]['offset'] = $ThisFileInfo['bonk']['dataoffset'];
			HandleBonkTags($fd, $BonkTagName, $ThisFileInfo);

		} while (true);
	}

	// parse META block for v0.6 - v0.8
	if (empty($ThisFileInfo['bonk']['INFO']) && isset($ThisFileInfo['bonk']['META']['tags']['info'])) {
		fseek($fd, $ThisFileInfo['bonk']['META']['tags']['info'], SEEK_SET);
		$TagHeaderTest = fread($fd, 5);
		if ($TagHeaderTest == "\x00".'INFO') {
			$ThisFileInfo['audio']['encoder'] = 'Extended BONK v0.6 - v0.8';

			$BonkTagName = substr($TagHeaderTest, 1, 4);
			$ThisFileInfo['bonk']["$BonkTagName"]['size']   = $ThisFileInfo['bonk']['dataend'] - $ThisFileInfo['bonk']['dataoffset'];
			$ThisFileInfo['bonk']["$BonkTagName"]['offset'] = $ThisFileInfo['bonk']['dataoffset'];
			HandleBonkTags($fd, $BonkTagName, $ThisFileInfo);
		}
	}

	if (empty($ThisFileInfo['bonk']['BONK'])) {
		unset($ThisFileInfo['bonk']);
	}
	return true;

}

function HandleBonkTags(&$fd, &$BonkTagName, &$ThisFileInfo) {

	switch ($BonkTagName) {
		case 'BONK':
			$BonkData = "\x00".$BonkTagName.fread($fd, 17);
			$ThisFileInfo['bonk']['BONK']['version']            =        LittleEndian2Int(substr($BonkData,  5, 1));
			$ThisFileInfo['bonk']['BONK']['number_samples']     =        LittleEndian2Int(substr($BonkData,  6, 4));
			$ThisFileInfo['bonk']['BONK']['sample_rate']        =        LittleEndian2Int(substr($BonkData, 10, 4));

			$ThisFileInfo['bonk']['BONK']['channels']           =        LittleEndian2Int(substr($BonkData, 14, 1));
			$ThisFileInfo['bonk']['BONK']['lossless']           = (bool) LittleEndian2Int(substr($BonkData, 15, 1));
			$ThisFileInfo['bonk']['BONK']['joint_stereo']       = (bool) LittleEndian2Int(substr($BonkData, 16, 1));
			$ThisFileInfo['bonk']['BONK']['number_taps']        =        LittleEndian2Int(substr($BonkData, 17, 2));
			$ThisFileInfo['bonk']['BONK']['downsampling_ratio'] =        LittleEndian2Int(substr($BonkData, 19, 1));
			$ThisFileInfo['bonk']['BONK']['samples_per_packet'] =        LittleEndian2Int(substr($BonkData, 20, 2));


			$ThisFileInfo['avdataoffset'] = $ThisFileInfo['bonk']["$BonkTagName"]['offset'] + 5 + 17;
			$ThisFileInfo['avdataend']    = $ThisFileInfo['bonk']["$BonkTagName"]['offset'] + $ThisFileInfo['bonk']["$BonkTagName"]['size'];

			$ThisFileInfo['fileformat']               = 'bonk';
			$ThisFileInfo['audio']['dataformat']      = 'bonk';
			$ThisFileInfo['audio']['bitrate_mode']    = 'vbr'; // assumed
			$ThisFileInfo['audio']['bits_per_sample'] = 16;    // assumed
			$ThisFileInfo['audio']['channels']        = $ThisFileInfo['bonk']['BONK']['channels'];
			$ThisFileInfo['audio']['sample_rate']     = $ThisFileInfo['bonk']['BONK']['sample_rate'];
			$ThisFileInfo['audio']['channelmode']     = ($ThisFileInfo['bonk']['BONK']['joint_stereo'] ? 'joint stereo' : 'stereo');
			$ThisFileInfo['audio']['lossless']        = $ThisFileInfo['bonk']['BONK']['lossless'];
			$ThisFileInfo['audio']['codec']           = 'bonk';

			$ThisFileInfo['playtime_seconds'] = $ThisFileInfo['bonk']['BONK']['number_samples'] / ($ThisFileInfo['bonk']['BONK']['sample_rate'] * $ThisFileInfo['bonk']['BONK']['channels']);
			if ($ThisFileInfo['playtime_seconds'] > 0) {
				$ThisFileInfo['audio']['bitrate'] = (($ThisFileInfo['bonk']['dataend'] - $ThisFileInfo['bonk']['dataoffset']) * 8) / $ThisFileInfo['playtime_seconds'];
			}
			break;

		case 'INFO':
			$ThisFileInfo['bonk']['INFO']['version'] = LittleEndian2Int(fread($fd, 1));
			$ThisFileInfo['bonk']['INFO']['entries_count'] = 0;
			$NextInfoDataPair = fread($fd, 5);
			if (!BonkIsValidTagName(substr($NextInfoDataPair, 1, 4))) {
				while (!feof($fd)) {
					//$CurrentSeekInfo['offset']  = LittleEndian2Int(substr($NextInfoDataPair, 0, 4));
					//$CurrentSeekInfo['nextbit'] = LittleEndian2Int(substr($NextInfoDataPair, 4, 1));
					//$ThisFileInfo['bonk']['INFO'][] = $CurrentSeekInfo;

					$NextInfoDataPair = fread($fd, 5);
					if (BonkIsValidTagName(substr($NextInfoDataPair, 1, 4))) {
						fseek($fd, -5, SEEK_CUR);
						break;
					}
					$ThisFileInfo['bonk']['INFO']['entries_count']++;
				}
			}
			break;

		case 'META':
			$BonkData = "\x00".$BonkTagName.fread($fd, $ThisFileInfo['bonk']["$BonkTagName"]['size'] - 5);
			$ThisFileInfo['bonk']['META']['version'] = LittleEndian2Int(substr($BonkData,  5, 1));

			$MetaTagEntries = floor(((strlen($BonkData) - 8) - 6) / 8); // BonkData - xxxxmeta - ØMETA
			$offset = 6;
			for ($i = 0; $i < $MetaTagEntries; $i++) {
				$MetaEntryTagName   =                  substr($BonkData, $offset, 4);
				$offset += 4;
				$MetaEntryTagOffset = LittleEndian2Int(substr($BonkData, $offset, 4));
				$offset += 4;
				$ThisFileInfo['bonk']['META']['tags']["$MetaEntryTagName"] = $MetaEntryTagOffset;
			}
			break;

		case ' ID3':
			$ThisFileInfo['audio']['encoder'] = 'Extended BONK v0.9+';
			require_once(GETID3_INCLUDEPATH.'getid3.id3v2.php');
			$ThisFileInfo['bonk'][' ID3']['valid'] = HandleID3v2Tag($fd, $ThisFileInfo, $ThisFileInfo['bonk']["$BonkTagName"]['offset'] + 2);
			break;

		default:
			$ThisFileInfo['warning'] .= "\n".'Unexpected Bonk tag "'.$BonkTagName.'" at offset '.$ThisFileInfo['bonk']["$BonkTagName"]['offset'];
			break;

	}
}

function BonkIsValidTagName($PossibleBonkTag, $ignorecase=false) {
	static $BonkIsValidTagName = array();
	if (empty($BonkIsValidTagName)) {
		$BonkIsValidTagName[] = 'BONK';
		$BonkIsValidTagName[] = 'INFO';
		$BonkIsValidTagName[] = ' ID3';
		$BonkIsValidTagName[] = 'META';
	}
	foreach ($BonkIsValidTagName as $validtagname) {
		if ($ignorecase && (strtolower($validtagname) == strtolower($PossibleBonkTag))) {
			return true;
		} elseif ($validtagname == $PossibleBonkTag) {
			return true;
		}
	}
	return false;
}

?>