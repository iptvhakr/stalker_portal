<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.optimfrog.php - part of getID3()                     //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getOptimFROGHeaderFilepointer(&$fd, &$ThisFileInfo) {
	$ThisFileInfo['fileformat']            = 'ofr';
	$ThisFileInfo['audio']['dataformat']   = 'ofr';
	$ThisFileInfo['audio']['bitrate_mode'] = 'vbr';
	$ThisFileInfo['audio']['lossless']     = true;

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	$OFRheader  = fread($fd, 8);
	if (substr($OFRheader, 0, 5) == '*RIFF') {

		return ParseOptimFROGheader42($fd, $ThisFileInfo);

	} elseif (substr($OFRheader, 0, 3) == 'OFR') {

		return ParseOptimFROGheader45($fd, $ThisFileInfo);

	}

	$ThisFileInfo['error'] .= "\n".'Expecting "*RIFF" or "OFR " at offset '.$ThisFileInfo['avdataoffset'].', found "'.$OFRheader.'"';
	unset($ThisFileInfo['fileformat']);
	unset($ThisFileInfo['audio']);
	return false;
}


function ParseOptimFROGheader42(&$fd, &$ThisFileInfo) {
	// for fileformat of v4.21 and older

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	$OptimFROGheaderData = fread($fd, 45);
	$ThisFileInfo['avdataoffset'] = 45;

	$OptimFROGencoderVersion = LittleEndian2Int(substr($OptimFROGheaderData, 0, 1)) / 10;
	$RIFFdata                =                  substr($OptimFROGheaderData, 1, 44);
	$OrignalRIFFheaderSize   = LittleEndian2Int(substr($RIFFdata,  4, 4)) +  8;
	$OrignalRIFFdataSize     = LittleEndian2Int(substr($RIFFdata, 40, 4)) + 44;

	if ($OrignalRIFFheaderSize > $OrignalRIFFdataSize) {
		$ThisFileInfo['avdataend'] -= ($OrignalRIFFheaderSize - $OrignalRIFFdataSize);
		fseek($fd, $ThisFileInfo['avdataend'], SEEK_SET);
		$RIFFdata .= fread($fd, $OrignalRIFFheaderSize - $OrignalRIFFdataSize);
	}

	require_once(GETID3_INCLUDEPATH.'getid3.riff.php');
	// move the data chunk after all other chunks (if any)
	// so that the RIFF parser doesn't see EOF when trying
	// to skip over the data chunk
	$RIFFdata = substr($RIFFdata, 0, 36).substr($RIFFdata, 44).substr($RIFFdata, 36, 8);
	ParseRIFFdata($RIFFdata, $ThisFileInfo);

	$ThisFileInfo['audio']['encoder']         = 'OptimFROG '.round($OptimFROGencoderVersion, 1);
	$ThisFileInfo['audio']['channels']        = $ThisFileInfo['RIFF']['audio'][0]['channels'];
	$ThisFileInfo['audio']['sample_rate']     = $ThisFileInfo['RIFF']['audio'][0]['sample_rate'];
	$ThisFileInfo['audio']['bits_per_sample'] = $ThisFileInfo['RIFF']['audio'][0]['bits_per_sample'];
	$ThisFileInfo['playtime_seconds']         = $OrignalRIFFdataSize / ($ThisFileInfo['audio']['channels'] * $ThisFileInfo['audio']['sample_rate'] * ($ThisFileInfo['audio']['bits_per_sample'] / 8));
	$ThisFileInfo['audio']['bitrate']         = (($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / $ThisFileInfo['playtime_seconds'];

	return true;
}


function ParseOptimFROGheader45(&$fd, &$ThisFileInfo) {
	// for fileformat of v4.50a and higher

	$RIFFdata = '';
	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	while (!feof($fd) && (ftell($fd) < $ThisFileInfo['avdataend'])) {
		$BlockOffset = ftell($fd);
		$BlockData   = fread($fd, 8);
		$offset      = 8;
		$BlockName   =                  substr($BlockData, 0, 4);
		$BlockSize   = LittleEndian2Int(substr($BlockData, 4, 4));

		switch ($BlockName) {
			case 'OFR ':
				$ThisFileInfo['OFR']["$BlockName"]['offset'] = $BlockOffset;
				$ThisFileInfo['OFR']["$BlockName"]['size']   = $BlockSize;

				$ThisFileInfo['audio']['encoder'] = 'OptimFROG 4.50 alpha';
				switch ($BlockSize) {
					case 12:
					case 15:
						// good
						break;

					default:
						$ThisFileInfo['warning'] .= "\n".'"'.$BlockName.'" contains more data than expected (expected 12 or 15 bytes, found '.$BlockSize.' bytes)';
						break;
				}
				$BlockData .= fread($fd, $BlockSize);

				$ThisFileInfo['OFR']["$BlockName"]['total_samples']      = LittleEndian2Int(substr($BlockData, $offset, 6));
				$offset += 6;
				$ThisFileInfo['OFR']["$BlockName"]['raw']['sample_type'] = LittleEndian2Int(substr($BlockData, $offset, 1));
				$ThisFileInfo['OFR']["$BlockName"]['sample_type']        = OptimFROGsampleTypeLookup($ThisFileInfo['OFR']["$BlockName"]['raw']['sample_type']);
				$offset += 1;
				$ThisFileInfo['OFR']["$BlockName"]['channel_config']     = LittleEndian2Int(substr($BlockData, $offset, 1));
				$ThisFileInfo['OFR']["$BlockName"]['channels']           = $ThisFileInfo['OFR']["$BlockName"]['channel_config'];
				$offset += 1;
				$ThisFileInfo['OFR']["$BlockName"]['sample_rate']        = LittleEndian2Int(substr($BlockData, $offset, 4));
				$offset += 4;

				if ($BlockSize > 12) {

					// OFR 4.504b or higher
					$ThisFileInfo['OFR']["$BlockName"]['channels']           = OptimFROGchannelConfigNumChannelsLookup($ThisFileInfo['OFR']["$BlockName"]['channel_config']);
					$ThisFileInfo['OFR']["$BlockName"]['raw']['encoder_id']  = LittleEndian2Int(substr($BlockData, $offset, 2));
					$ThisFileInfo['OFR']["$BlockName"]['encoder']            = OptimFROGencoderNameLookup($ThisFileInfo['OFR']["$BlockName"]['raw']['encoder_id']);
					$offset += 2;
					$ThisFileInfo['OFR']["$BlockName"]['raw']['compression'] = LittleEndian2Int(substr($BlockData, $offset, 1));
					$ThisFileInfo['OFR']["$BlockName"]['compression']        = OptimFROGcompressionLookup($ThisFileInfo['OFR']["$BlockName"]['raw']['compression']);
					$offset += 1;

					$ThisFileInfo['audio']['encoder'] = 'OptimFROG '.$ThisFileInfo['OFR']["$BlockName"]['encoder'];

				}

				$ThisFileInfo['audio']['channels']        = $ThisFileInfo['OFR']["$BlockName"]['channels'];
				$ThisFileInfo['audio']['sample_rate']     = $ThisFileInfo['OFR']["$BlockName"]['sample_rate'];
				$ThisFileInfo['audio']['bits_per_sample'] = OptimFROGbitsPerSampleTypeLookup($ThisFileInfo['OFR']["$BlockName"]['raw']['sample_type']);
				break;


			case 'COMP':
				// unlike other block types, there CAN be multiple COMP blocks

				$COMPdata['offset'] = $BlockOffset;
				$COMPdata['size']   = $BlockSize;

				if ($ThisFileInfo['avdataoffset'] == 0) {
					$ThisFileInfo['avdataoffset'] = $BlockOffset;
				}

				// Only interested in first 14 bytes (only first 12 needed for v4.50 alpha), not actual audio data
				$BlockData .= fread($fd, 14);
				fseek($fd, $BlockSize - 14, SEEK_CUR);

				$COMPdata['crc_32']                       = LittleEndian2Int(substr($BlockData, $offset, 4));
				$offset += 4;
				$COMPdata['sample_count']                 = LittleEndian2Int(substr($BlockData, $offset, 4));
				$offset += 4;
				$COMPdata['raw']['sample_type']           = LittleEndian2Int(substr($BlockData, $offset, 1));
				$COMPdata['sample_type']                  = OptimFROGsampleTypeLookup($COMPdata['raw']['sample_type']);
				$offset += 1;
				$COMPdata['raw']['channel_configuration'] = LittleEndian2Int(substr($BlockData, $offset, 1));
				$COMPdata['channel_configuration']        = OptimFROGchannelConfigurationLookup($COMPdata['raw']['channel_configuration']);
				$offset += 1;
				$COMPdata['raw']['algorithm_id']          = LittleEndian2Int(substr($BlockData, $offset, 2));
				//$COMPdata['algorithm']                    = OptimFROGalgorithmNameLookup($COMPdata['raw']['algorithm_id']);
				$offset += 2;

				if ($ThisFileInfo['OFR']['OFR ']['size'] > 12) {

					// OFR 4.504b or higher
					$COMPdata['raw']['encoder_id']        = LittleEndian2Int(substr($BlockData, $offset, 2));
					$COMPdata['encoder']                  = OptimFROGencoderNameLookup($COMPdata['raw']['encoder_id']);
					$offset += 2;

				}

				if ($COMPdata['crc_32'] == 0x454E4F4E) {
					// ASCII value of 'NONE' - placeholder value in v4.50a
					$COMPdata['crc_32'] = false;
				}

				$ThisFileInfo['OFR']["$BlockName"][] = $COMPdata;
				break;

			case 'HEAD':
				$ThisFileInfo['OFR']["$BlockName"]['offset'] = $BlockOffset;
				$ThisFileInfo['OFR']["$BlockName"]['size']   = $BlockSize;

				$RIFFdata .= fread($fd, $BlockSize);
				break;

			case 'TAIL':
				$ThisFileInfo['OFR']["$BlockName"]['offset'] = $BlockOffset;
				$ThisFileInfo['OFR']["$BlockName"]['size']   = $BlockSize;

				$ThisFileInfo['avdataend'] = $BlockOffset;

				$RIFFdata .= fread($fd, $BlockSize);
				break;

			case 'RECV':
				// block contains no useful meta data - simply note and skip

				$ThisFileInfo['OFR']["$BlockName"]['offset'] = $BlockOffset;
				$ThisFileInfo['OFR']["$BlockName"]['size']   = $BlockSize;

				fseek($fd, $BlockSize, SEEK_CUR);
				break;


			default:
				$ThisFileInfo['OFR']["$BlockName"]['offset'] = $BlockOffset;
				$ThisFileInfo['OFR']["$BlockName"]['size']   = $BlockSize;

				$ThisFileInfo['warning'] .= "\n".'Unhandled OptimFROG block type "'.$BlockName.'" at offset '.$ThisFileInfo['OFR']["$BlockName"]['offset'];
				fseek($fd, $BlockSize, SEEK_CUR);
				break;
		}
	}

	$ThisFileInfo['playtime_seconds'] = (float) $ThisFileInfo['OFR']['OFR ']['total_samples'] / ($ThisFileInfo['audio']['channels'] * $ThisFileInfo['audio']['sample_rate']);
	$ThisFileInfo['audio']['bitrate'] = (($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / $ThisFileInfo['playtime_seconds'];

	require_once(GETID3_INCLUDEPATH.'getid3.riff.php');
	// move the data chunk after all other chunks (if any)
	// so that the RIFF parser doesn't see EOF when trying
	// to skip over the data chunk
	$RIFFdata = substr($RIFFdata, 0, 36).substr($RIFFdata, 44).substr($RIFFdata, 36, 8);
	ParseRIFFdata($RIFFdata, $ThisFileInfo);

	return true;
}


function OptimFROGsampleTypeLookup($SampleType) {
	static $OptimFROGsampleTypeLookup = array();
	if (empty($OptimFROGsampleTypeLookup)) {
		$OptimFROGsampleTypeLookup[0]  = 'unsigned int (8-bit)';
		$OptimFROGsampleTypeLookup[1]  = 'signed int (8-bit)';
		$OptimFROGsampleTypeLookup[2]  = 'unsigned int (16-bit)';
		$OptimFROGsampleTypeLookup[3]  = 'signed int (16-bit)';
		$OptimFROGsampleTypeLookup[4]  = 'unsigned int (24-bit)';
		$OptimFROGsampleTypeLookup[5]  = 'signed int (24-bit)';
		$OptimFROGsampleTypeLookup[6]  = 'unsigned int (32-bit)';
		$OptimFROGsampleTypeLookup[7]  = 'signed int (32-bit)';
		$OptimFROGsampleTypeLookup[8]  = 'float 0.24 (32-bit)';
		$OptimFROGsampleTypeLookup[9]  = 'float 16.8 (32-bit)';
		$OptimFROGsampleTypeLookup[10] = 'float 24.0 (32-bit)';
	}
	return (isset($OptimFROGsampleTypeLookup[$SampleType]) ? $OptimFROGsampleTypeLookup[$SampleType] : false);
}

function OptimFROGbitsPerSampleTypeLookup($SampleType) {
	static $OptimFROGbitsPerSampleTypeLookup = array();
	if (empty($OptimFROGbitsPerSampleTypeLookup)) {
		$OptimFROGbitsPerSampleTypeLookup[0]  = 8;
		$OptimFROGbitsPerSampleTypeLookup[1]  = 8;
		$OptimFROGbitsPerSampleTypeLookup[2]  = 16;
		$OptimFROGbitsPerSampleTypeLookup[3]  = 16;
		$OptimFROGbitsPerSampleTypeLookup[4]  = 24;
		$OptimFROGbitsPerSampleTypeLookup[5]  = 24;
		$OptimFROGbitsPerSampleTypeLookup[6]  = 32;
		$OptimFROGbitsPerSampleTypeLookup[7]  = 32;
		$OptimFROGbitsPerSampleTypeLookup[8]  = 32;
		$OptimFROGbitsPerSampleTypeLookup[9]  = 32;
		$OptimFROGbitsPerSampleTypeLookup[10] = 32;
	}
	return (isset($OptimFROGbitsPerSampleTypeLookup[$SampleType]) ? $OptimFROGbitsPerSampleTypeLookup[$SampleType] : false);
}

function OptimFROGchannelConfigurationLookup($ChannelConfiguration) {
	static $OptimFROGchannelConfigurationLookup = array();
	if (empty($OptimFROGchannelConfigurationLookup)) {
		$OptimFROGchannelConfigurationLookup[0]  = 'mono';
		$OptimFROGchannelConfigurationLookup[1]  = 'stereo';
	}
	return (isset($OptimFROGchannelConfigurationLookup[$ChannelConfiguration]) ? $OptimFROGchannelConfigurationLookup[$ChannelConfiguration] : false);
}

function OptimFROGchannelConfigNumChannelsLookup($ChannelConfiguration) {
	static $OptimFROGchannelConfigNumChannelsLookup = array();
	if (empty($OptimFROGchannelConfigNumChannelsLookup)) {
		$OptimFROGchannelConfigNumChannelsLookup[0]  = 1;
		$OptimFROGchannelConfigNumChannelsLookup[1]  = 2;
	}
	return (isset($OptimFROGchannelConfigNumChannelsLookup[$ChannelConfiguration]) ? $OptimFROGchannelConfigNumChannelsLookup[$ChannelConfiguration] : false);
}



// function OptimFROGalgorithmNameLookup($AlgorithID) {
// 	static $OptimFROGalgorithmNameLookup = array();
// 	if (empty($OptimFROGalgorithmNameLookup)) {
// 	}
// 	return (isset($OptimFROGalgorithmNameLookup[$AlgorithID]) ? $OptimFROGalgorithmNameLookup[$AlgorithID] : false);
// }


function OptimFROGencoderNameLookup($EncoderID) {
	// version = (encoderID >> 4) + 4500
	// system  =  encoderID & 0xF

	$EncoderVersion  = number_format(((($EncoderID & 0xF0) >> 4) + 4500) / 1000, 3);
	$EncoderSystemID = ($EncoderID & 0x0F);

	static $OptimFROGencoderSystemLookup = array();
	if (empty($OptimFROGencoderSystemLookup)) {
		$OptimFROGencoderSystemLookup[0x00] = 'Windows console';
		$OptimFROGencoderSystemLookup[0x01] = 'Linux console';
		$OptimFROGencoderSystemLookup[0x0F] = 'unknown';
	}
	return $EncoderVersion.' ('.(isset($OptimFROGencoderSystemLookup[$EncoderSystemID]) ? $OptimFROGencoderSystemLookup[$EncoderSystemID] : 'undefined encoder type (0x'.dechex($EncoderSystemID).')').')';
}

function OptimFROGcompressionLookup($CompressionID) {
	// mode    = compression >> 3
	// speedup = compression & 0x07

	$CompressionModeID    = ($CompressionID & 0xF8) >> 3;
	$CompressionSpeedupID = ($CompressionID & 0x07);

	static $OptimFROGencoderModeLookup = array();
	if (empty($OptimFROGencoderModeLookup)) {
		$OptimFROGencoderModeLookup[0x00] = 'fast';
		$OptimFROGencoderModeLookup[0x01] = 'normal';
		$OptimFROGencoderModeLookup[0x02] = 'high';
		$OptimFROGencoderModeLookup[0x03] = 'extra'; // extranew
		$OptimFROGencoderModeLookup[0x04] = 'best';  // bestnew
		$OptimFROGencoderModeLookup[0x05] = 'ultra';
		$OptimFROGencoderModeLookup[0x06] = 'insane';

	}

	static $OptimFROGencoderSpeedupLookup = array();
	if (empty($OptimFROGencoderSpeedupLookup)) {
		$OptimFROGencoderSpeedupLookup[0x00] = '1x';
		$OptimFROGencoderSpeedupLookup[0x01] = '2x';
		$OptimFROGencoderSpeedupLookup[0x02] = '4x';
	}

	return (isset($OptimFROGencoderModeLookup[$CompressionModeID]) ? $OptimFROGencoderModeLookup[$CompressionModeID] : 'undefined mode (0x'.dechex($CompressionModeID)).' '.(isset($OptimFROGencoderSpeedupLookup[$CompressionSpeedupID]) ? $OptimFROGencoderSpeedupLookup[$CompressionSpeedupID] : 'undefined mode (0x'.dechex($CompressionSpeedupID));
}

?>