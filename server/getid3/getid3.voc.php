<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.voc.php - part of getID3()                           //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getVOCheaderFilepointer(&$fd, &$ThisFileInfo) {

	$OriginalAVdataOffset = $ThisFileInfo['avdataoffset'];
	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	$VOCheader  = fread($fd, 26);

	if (substr($VOCheader, 0, 19) != 'Creative Voice File') {
		$ThisFileInfo['error'] .= "\n".'Expecting "Creative Voice File" at offset '.$ThisFileInfo['avdataoffset'].', found "'.substr($VOCheader, 0, 19).'"';
		return false;
	}

	$ThisFileInfo['fileformat']               = 'voc';
	$ThisFileInfo['audio']['dataformat']      = 'voc';
	$ThisFileInfo['audio']['bitrate_mode']    = 'cbr';
	$ThisFileInfo['audio']['channels']        = 1; // might be overriden below
	$ThisFileInfo['audio']['bits_per_sample'] = 8; // might be overriden below

	// byte #     Description
	// ------     ------------------------------------------
	// 00-12      'Creative Voice File'
	// 13         1A (eof to abort printing of file)
	// 14-15      Offset of first datablock in .voc file (std 1A 00 in Intel Notation)
	// 16-17      Version number (minor,major) (VOC-HDR puts 0A 01)
	// 18-19      2's Comp of Ver. # + 1234h (VOC-HDR puts 29 11)

	$ThisFileInfo['voc']['header']['datablock_offset'] = LittleEndian2Int(substr($VOCheader, 20, 2));
	$ThisFileInfo['voc']['header']['minor_version']    = LittleEndian2Int(substr($VOCheader, 22, 1));
	$ThisFileInfo['voc']['header']['major_version']    = LittleEndian2Int(substr($VOCheader, 23, 1));

	do {

		$BlockOffset    = ftell($fd);
		$BlockData      = fread($fd, 4);
		$BlockType      = LittleEndian2Int(substr($BlockData, 0, 1));
		$BlockSize      = LittleEndian2Int(substr($BlockData, 1, 3));
		$ThisBlock      = array();

		switch ($BlockType) {
			case 0:  // Terminator
				// do nothing, we'll break out of the loop down below
				break;

			case 1:  // Sound data
				$BlockData .= fread($fd, 2);
				if ($ThisFileInfo['avdataoffset'] <= $OriginalAVdataOffset) {
					$ThisFileInfo['avdataoffset'] = ftell($fd);
				}
				fseek($fd, $BlockSize - 2, SEEK_CUR);

				$ThisBlock['sample_rate_id']   = LittleEndian2Int(substr($BlockData, 4, 1));
				$ThisBlock['compression_type'] = LittleEndian2Int(substr($BlockData, 5, 1));

				$ThisBlock['compression_name'] = VOCcompressionTypeLookup($ThisBlock['compression_type']);
				if ($ThisBlock['compression_type'] <= 3) {
					$ThisFileInfo['voc']['compressed_bits_per_sample'] = CastAsInt(str_replace('-bit', '', $ThisBlock['compression_name']));
				}

				if (empty($ThisFileInfo['audio']['sample_rate'])) {
					// Less accurate than the Extended block (#8) data

					// SR byte = 256-(1000000/sample_rate)
					$ThisFileInfo['audio']['sample_rate'] = trunc((1000000 / (256 - $ThisBlock['sample_rate_id'])) / $ThisFileInfo['audio']['channels']);
				}
				break;

			case 2:  // Sound continue
			case 3:  // Silence
			case 4:  // Marker
			case 6:  // Repeat
			case 7:  // End repeat
				// nothing useful, just skip
				fseek($fd, $BlockSize, SEEK_CUR);
				break;

			case 8:  // Extended
				$BlockData .= fread($fd, 4);

				//00-01  Time Constant:
				//   Mono: 65536 - (256000000 / sample_rate)
				// Stereo: 65536 - (256000000 / (sample_rate * 2))
				$ThisBlock['time_constant'] =        LittleEndian2Int(substr($BlockData, 4, 2));
				$ThisBlock['pack_method']   =        LittleEndian2Int(substr($BlockData, 6, 1));
				$ThisBlock['stereo']        = (bool) LittleEndian2Int(substr($BlockData, 7, 1));

				$ThisFileInfo['audio']['channels']    = ($ThisBlock['stereo'] ? 2 : 1);
				$ThisFileInfo['audio']['sample_rate'] = trunc((256000000 / (65536 - $ThisBlock['time_constant'])) / $ThisFileInfo['audio']['channels']);
				break;

			case 9:  // data block that supersedes blocks 1 and 8. Used for stereo, 16 bit
				$BlockData .= fread($fd, 12);
				if ($ThisFileInfo['avdataoffset'] <= $OriginalAVdataOffset) {
					$ThisFileInfo['avdataoffset'] = ftell($fd);
				}
				fseek($fd, $BlockSize - 12, SEEK_CUR);

				$ThisBlock['sample_rate']      = LittleEndian2Int(substr($BlockData,  4, 4));
				$ThisBlock['bits_per_sample']  = LittleEndian2Int(substr($BlockData,  8, 1));
				$ThisBlock['channels']         = LittleEndian2Int(substr($BlockData,  9, 1));
				$ThisBlock['wFormat']          = LittleEndian2Int(substr($BlockData, 10, 2));

				$ThisBlock['compression_name'] = VOCwFormatLookup($ThisBlock['wFormat']);
				if (VOCwFormatActualBitsPerSampleLookup($ThisBlock['wFormat'])) {
					$ThisFileInfo['voc']['compressed_bits_per_sample'] = VOCwFormatActualBitsPerSampleLookup($ThisBlock['wFormat']);
				}

				$ThisFileInfo['audio']['sample_rate']     = $ThisBlock['sample_rate'];
				$ThisFileInfo['audio']['bits_per_sample'] = $ThisBlock['bits_per_sample'];
				$ThisFileInfo['audio']['channels']        = $ThisBlock['channels'];
				break;

			default:
				$ThisFileInfo['warning'] .= "\n".'Unhandled block type "'.$BlockType.'" at offset '.$BlockOffset;
				fseek($fd, $BlockSize, SEEK_CUR);
				break;
		}

		if (!empty($ThisBlock)) {
			$ThisBlock['block_offset']  = $BlockOffset;
			$ThisBlock['block_size']    = $BlockSize;
			$ThisBlock['block_type_id'] = $BlockType;
			$ThisFileInfo['voc']['blocks'][] = $ThisBlock;
		}

	} while (!feof($fd) && ($BlockType != 0));

	// Terminator block doesn't have size field, so seek back 3 spaces
	fseek($fd, -3, SEEK_CUR);

	if (!empty($ThisFileInfo['voc']['compressed_bits_per_sample'])) {
		$ThisFileInfo['playtime_seconds'] = (($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / ($ThisFileInfo['voc']['compressed_bits_per_sample'] * $ThisFileInfo['audio']['channels'] * $ThisFileInfo['audio']['sample_rate']);
		$ThisFileInfo['audio']['bitrate'] = (($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / $ThisFileInfo['playtime_seconds'];
	}

	return true;
}

function VOCcompressionTypeLookup($index) {
	static $VOCcompressionTypeLookup = array();
	if (empty($VOCcompressionTypeLookup)) {
		$VOCcompressionTypeLookup[0] = '8-bit';
		$VOCcompressionTypeLookup[1] = '4-bit';
		$VOCcompressionTypeLookup[2] = '2.6-bit';
		$VOCcompressionTypeLookup[3] = '2-bit';
	}
	return (isset($VOCcompressionTypeLookup[$index]) ? $VOCcompressionTypeLookup[$index] : 'Multi DAC ('.($index - 3).') channels');
}

function VOCwFormatLookup($index) {
	static $VOCwFormatLookup = array();
	if (empty($VOCwFormatLookup)) {
		$VOCwFormatLookup[0x0000] = '8-bit unsigned PCM';
		$VOCwFormatLookup[0x0001] = 'Creative 8-bit to 4-bit ADPCM';
		$VOCwFormatLookup[0x0002] = 'Creative 8-bit to 3-bit ADPCM';
		$VOCwFormatLookup[0x0003] = 'Creative 8-bit to 2-bit ADPCM';
		$VOCwFormatLookup[0x0004] = '16-bit signed PCM';
		$VOCwFormatLookup[0x0006] = 'CCITT a-Law';
		$VOCwFormatLookup[0x0007] = 'CCITT u-Law';
		$VOCwFormatLookup[0x2000] = 'Creative 16-bit to 4-bit ADPCM';
	}
	return (isset($VOCwFormatLookup[$index]) ? $VOCwFormatLookup[$index] : false);
}

function VOCwFormatActualBitsPerSampleLookup($index) {
	static $VOCwFormatLookup = array();
	if (empty($VOCwFormatLookup)) {
		$VOCwFormatLookup[0x0000] = 8;
		$VOCwFormatLookup[0x0001] = 4;
		$VOCwFormatLookup[0x0002] = 3;
		$VOCwFormatLookup[0x0003] = 2;
		$VOCwFormatLookup[0x0004] = 16;
		$VOCwFormatLookup[0x0006] = 8;
		$VOCwFormatLookup[0x0007] = 8;
		$VOCwFormatLookup[0x2000] = 4;
	}
	return (isset($VOCwFormatLookup[$index]) ? $VOCwFormatLookup[$index] : false);
}

?>