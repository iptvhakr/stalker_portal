<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.riff.php - part of getID3()                          //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getRIFFHeaderFilepointer(&$fd, &$ThisFileInfo) {
	$Original['avdataoffset'] = $ThisFileInfo['avdataoffset'];
	$Original['avdataend']    = $ThisFileInfo['avdataend'];

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	$RIFFheader = fread($fd, 12);
	switch (substr($RIFFheader, 0, 4)) {
		case 'FORM':
			$ThisFileInfo['fileformat']                      = 'aiff';
			$RIFFheaderSize                                  = EitherEndian2Int($ThisFileInfo, substr($RIFFheader, 4, 4));
			$ThisFileInfo['RIFF'][substr($RIFFheader, 8, 4)] = ParseRIFF($fd, $ThisFileInfo['avdataoffset'] + 12, $ThisFileInfo['avdataoffset'] + $RIFFheaderSize, $ThisFileInfo);
			$ThisFileInfo['RIFF']['header_size']             = $RIFFheaderSize;
			break;

		case 'RIFF':
		case 'SDSS':  // SDSS is identical to RIFF, just renamed. Used by SmartSound QuickTracks (www.smartsound.com)
		case 'RMP3':  // RMP3 is identical to RIFF, just renamed. Used by [unknown program] when creating RIFF-MP3s
			$RIFFtype = substr($RIFFheader, 8, 4);
			if ($RIFFtype == 'RMP3') {
				$RIFFtype == 'WAVE';
			}

			$ThisFileInfo['fileformat']          = 'riff';
			$RIFFheaderSize                      = EitherEndian2Int($ThisFileInfo, substr($RIFFheader, 4, 4));
			$ThisFileInfo['RIFF']["$RIFFtype"]   = ParseRIFF($fd, $ThisFileInfo['avdataoffset'] + 12, $ThisFileInfo['avdataoffset'] + $RIFFheaderSize, $ThisFileInfo);
			$ThisFileInfo['RIFF']['header_size'] = $RIFFheaderSize;
			break;

		default:
			$ThisFileInfo['error'] .= "\n".'Cannot parse RIFF (this is maybe not a RIFF / WAV / AVI file?)';
			unset($ThisFileInfo['fileformat']);
			return false;
			break;
	}

	$streamindex = 0;
	$arraykeys = array_keys($ThisFileInfo['RIFF']);
	switch ($arraykeys[0]) {
		case 'WAVE':
		case 'RMP3':  // RMP3 is identical to WAVE, just renamed. Used by [unknown program] when creating RIFF-MP3s
			if (empty($ThisFileInfo['audio']['bitrate_mode'])) {
				$ThisFileInfo['audio']['bitrate_mode'] = 'cbr';
			}
			if (empty($ThisFileInfo['audio']['dataformat'])) {
				$ThisFileInfo['audio']['dataformat'] = 'wav';
			}

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['data'][0]['offset'])) {
				$ThisFileInfo['avdataoffset'] = $ThisFileInfo['RIFF'][$arraykeys[0]]['data'][0]['offset'] + 8;
				$ThisFileInfo['avdataend']    = $ThisFileInfo['avdataoffset'] + $ThisFileInfo['RIFF'][$arraykeys[0]]['data'][0]['size'];
			}

			if (isset($ThisFileInfo['RIFF']['WAVE']['fmt '][0]['data'])) {

				$ThisFileInfo['RIFF']['audio'][$streamindex] = RIFFparseWAVEFORMATex($ThisFileInfo['RIFF']['WAVE']['fmt '][0]['data']);

				if ($ThisFileInfo['RIFF']['audio'][$streamindex] == 0) {
					$ThisFileInfo['error'] .= 'Corrupt RIFF file: bitrate_audio == zero';
					return false;
				}

				$ThisFileInfo['RIFF']['raw']['fmt '] = $ThisFileInfo['RIFF']['audio'][$streamindex]['raw'];
				unset($ThisFileInfo['RIFF']['audio'][$streamindex]['raw']);
				$ThisFileInfo['audio'] = array_merge_noclobber($ThisFileInfo['audio'], $ThisFileInfo['RIFF']['audio'][$streamindex]);
				if (substr($ThisFileInfo['audio']['codec'], 0, strlen('unknown: 0x')) == 'unknown: 0x') {
					$ThisFileInfo['warning'] .= "\n".'Audio codec = '.$ThisFileInfo['audio']['codec'];
				}
				$ThisFileInfo['audio']['bitrate'] = $ThisFileInfo['RIFF']['audio'][$streamindex]['bitrate'];
				if ($ThisFileInfo['audio']['bits_per_sample'] == 0) {
					unset($ThisFileInfo['audio']['bits_per_sample']);
				}

				$ThisFileInfo['playtime_seconds'] = (float) ((($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / $ThisFileInfo['audio']['bitrate']);

				if (isset($ThisFileInfo['RIFF']['WAVE']['data'][0]['offset']) && isset($ThisFileInfo['RIFF']['raw']['fmt ']['wFormatTag'])) {
					$ThisFileInfo['audio']['lossless'] = false;
					switch ($ThisFileInfo['RIFF']['raw']['fmt ']['wFormatTag']) {

						case 1:  // PCM
							$ThisFileInfo['audio']['lossless'] = true;
							break;

						default:
							// do nothing
							break;

					}
				}
			}

			if (isset($ThisFileInfo['RIFF']['WAVE']['rgad'][0]['data'])) {
				require_once(GETID3_INCLUDEPATH.'getid3.rgad.php');

				$rgadData = $ThisFileInfo['RIFF']['WAVE']['rgad'][0]['data'];
				$ThisFileInfo['RIFF']['raw']['rgad']['fPeakAmplitude']      = LittleEndian2Float(substr($rgadData, 0, 4));
				$ThisFileInfo['RIFF']['raw']['rgad']['nRadioRgAdjust']      = EitherEndian2Int($ThisFileInfo, substr($rgadData, 4, 2));
				$ThisFileInfo['RIFF']['raw']['rgad']['nAudiophileRgAdjust'] = EitherEndian2Int($ThisFileInfo, substr($rgadData, 6, 2));
				$nRadioRgAdjustBitstring      = str_pad(Dec2Bin($ThisFileInfo['RIFF']['raw']['rgad']['nRadioRgAdjust']), 16, '0', STR_PAD_LEFT);
				$nAudiophileRgAdjustBitstring = str_pad(Dec2Bin($ThisFileInfo['RIFF']['raw']['rgad']['nAudiophileRgAdjust']), 16, '0', STR_PAD_LEFT);
				$ThisFileInfo['RIFF']['raw']['rgad']['radio']['name']       = Bin2Dec(substr($nRadioRgAdjustBitstring, 0, 3));
				$ThisFileInfo['RIFF']['raw']['rgad']['radio']['originator'] = Bin2Dec(substr($nRadioRgAdjustBitstring, 3, 3));
				$ThisFileInfo['RIFF']['raw']['rgad']['radio']['signbit']    = Bin2Dec(substr($nRadioRgAdjustBitstring, 6, 1));
				$ThisFileInfo['RIFF']['raw']['rgad']['radio']['adjustment'] = Bin2Dec(substr($nRadioRgAdjustBitstring, 7, 9));
				$ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['name']       = Bin2Dec(substr($nAudiophileRgAdjustBitstring, 0, 3));
				$ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['originator'] = Bin2Dec(substr($nAudiophileRgAdjustBitstring, 3, 3));
				$ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['signbit']    = Bin2Dec(substr($nAudiophileRgAdjustBitstring, 6, 1));
				$ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['adjustment'] = Bin2Dec(substr($nAudiophileRgAdjustBitstring, 7, 9));

				$ThisFileInfo['RIFF']['rgad']['peakamplitude'] = $ThisFileInfo['RIFF']['raw']['rgad']['fPeakAmplitude'];
				if (($ThisFileInfo['RIFF']['raw']['rgad']['radio']['name'] != 0) && ($ThisFileInfo['RIFF']['raw']['rgad']['radio']['originator'] != 0)) {
					$ThisFileInfo['RIFF']['rgad']['radio']['name']            = RGADnameLookup($ThisFileInfo['RIFF']['raw']['rgad']['radio']['name']);
					$ThisFileInfo['RIFF']['rgad']['radio']['originator']      = RGADoriginatorLookup($ThisFileInfo['RIFF']['raw']['rgad']['radio']['originator']);
					$ThisFileInfo['RIFF']['rgad']['radio']['adjustment']      = RGADadjustmentLookup($ThisFileInfo['RIFF']['raw']['rgad']['radio']['adjustment'], $ThisFileInfo['RIFF']['raw']['rgad']['radio']['signbit']);
				}
				if (($ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['name'] != 0) && ($ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['originator'] != 0)) {
					$ThisFileInfo['RIFF']['rgad']['audiophile']['name']       = RGADnameLookup($ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['name']);
					$ThisFileInfo['RIFF']['rgad']['audiophile']['originator'] = RGADoriginatorLookup($ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['originator']);
					$ThisFileInfo['RIFF']['rgad']['audiophile']['adjustment'] = RGADadjustmentLookup($ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['adjustment'], $ThisFileInfo['RIFF']['raw']['rgad']['audiophile']['signbit']);
				}
			}

			if (isset($ThisFileInfo['RIFF']['WAVE']['fact'][0]['data'])) {
				$ThisFileInfo['RIFF']['raw']['fact']['NumberOfSamples'] = EitherEndian2Int($ThisFileInfo, substr($ThisFileInfo['RIFF']['WAVE']['fact'][0]['data'], 0, 4));

				if (isset($ThisFileInfo['RIFF']['raw']['fmt ']['nSamplesPerSec']) && ($ThisFileInfo['RIFF']['raw']['fmt ']['nSamplesPerSec'] > 0)) {
					$ThisFileInfo['playtime_seconds'] = (float) $ThisFileInfo['RIFF']['raw']['fact']['NumberOfSamples'] / $ThisFileInfo['RIFF']['raw']['fmt ']['nSamplesPerSec'];
				}

				if (isset($ThisFileInfo['RIFF']['raw']['fmt ']['nAvgBytesPerSec']) && $ThisFileInfo['RIFF']['raw']['fmt ']['nAvgBytesPerSec']) {
					$ThisFileInfo['audio']['bitrate'] = CastAsInt($ThisFileInfo['RIFF']['raw']['fmt ']['nAvgBytesPerSec'] * 8);
				}
			}

			if (isset($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'])) {
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['title']          =                  trim(substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'],   0, 256));
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['author']         =                  trim(substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 256,  32));
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['reference']      =                  trim(substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 288,  32));
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_date']    =                       substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 320,  10);
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_time']    =                       substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 330,   8);
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['time_reference'] =      LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 338,   8));
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['bwf_version']    =      LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 346,   1));
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['reserved']       =      LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 347, 254));
				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['coding_history'] =  explode("\r\n", trim(substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['data'], 601)));

				$ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_date_unix'] = mktime(
																					substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_time'], 0, 2),
																					substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_time'], 3, 2),
																					substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_time'], 6, 2),
																					substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_date'], 5, 2),
																					substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_date'], 8, 2),
																					substr($ThisFileInfo['RIFF']['WAVE']['bext'][0]['origin_date'], 0, 4)
																				);

				$ThisFileInfo['RIFF']['comments']['author'][] = $ThisFileInfo['RIFF']['WAVE']['bext'][0]['author'];
				$ThisFileInfo['RIFF']['comments']['title'][]  = $ThisFileInfo['RIFF']['WAVE']['bext'][0]['title'];
			}

			if (isset($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['data'])) {
				$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['sound_information']      = LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['data'], 0, 2));
				$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['homogenous']           = (bool) ($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['sound_information'] & 0x0001);
				if ($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['homogenous']) {
					$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['padding']          = InverseBoolean($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['sound_information'] & 0x0002);
					$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['22_or_44']         =        (bool) ($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['sound_information'] & 0x0004);
					$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['free_format']      =        (bool) ($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['sound_information'] & 0x0008);

					$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['nominal_frame_size']        = LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['data'], 2, 2));
				}
				$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['anciliary_data_length']         = LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['data'], 6, 2));
				$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['anciliary_data_def']     = LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['data'], 8, 2));
				$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['anciliary_data_left']  = (bool) ($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['anciliary_data_def'] & 0x0001);
				$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['anciliary_data_free']  = (bool) ($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['anciliary_data_def'] & 0x0002);
				$ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['flags']['anciliary_data_right'] = (bool) ($ThisFileInfo['RIFF']['WAVE']['MEXT'][0]['raw']['anciliary_data_def'] & 0x0004);
			}

			if (isset($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'])) {
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['version']              =                  substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],    0,    4);
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['title']                =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],    4,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['artist']               =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],   68,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['cut_id']               =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  132,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['client_id']            =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  196,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['category']             =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  260,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['classification']       =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  324,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['out_cue']              =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  388,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['start_date']           =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  452,   10));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['start_time']           =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  462,    8));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['end_date']             =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  470,   10));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['end_time']             =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  480,    8));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['producer_app_id']      =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  488,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['producer_app_version'] =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  552,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['user_defined_text']    =             trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  616,   64));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['zero_db_reference']    = LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  680,    4), true);
				for ($i = 0; $i < 8; $i++) {
					$ThisFileInfo['RIFF']['WAVE']['cart'][0]['post_time'][$i]['usage_fourcc'] =                  substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'], 684 + ($i * 8), 4);
					$ThisFileInfo['RIFF']['WAVE']['cart'][0]['post_time'][$i]['timer_value']  = LittleEndian2Int(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'], 684 + ($i * 8) + 4, 4));
				}
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['url']              =                 trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'],  748, 1024));
				$ThisFileInfo['RIFF']['WAVE']['cart'][0]['tag_text']         = explode("\r\n", trim(substr($ThisFileInfo['RIFF']['WAVE']['cart'][0]['data'], 1772)));

				$ThisFileInfo['RIFF']['comments']['artist'][] = $ThisFileInfo['RIFF']['WAVE']['cart'][0]['artist'];
				$ThisFileInfo['RIFF']['comments']['title'][]  = $ThisFileInfo['RIFF']['WAVE']['cart'][0]['title'];
			}

			if (!isset($ThisFileInfo['audio']['bitrate']) && isset($ThisFileInfo['RIFF']['audio'][$streamindex]['bitrate'])) {
				$ThisFileInfo['audio']['bitrate'] = $ThisFileInfo['RIFF']['audio'][$streamindex]['bitrate'];
				$ThisFileInfo['playtime_seconds'] = (float) ((($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / $ThisFileInfo['audio']['bitrate']);
			}

			if (!empty($ThisFileInfo['wavpack'])) {
				$ThisFileInfo['audio']['dataformat']   = 'wavpack';
				$ThisFileInfo['audio']['bitrate_mode'] = 'vbr';
				$ThisFileInfo['audio']['encoder']      = 'WavPack v'.$ThisFileInfo['wavpack']['version'];

				// Reset to the way it was - RIFF parsing will have messed this up
				$ThisFileInfo['avdataend'] = $Original['avdataend'];
				$ThisFileInfo['audio']['bitrate'] = (($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) * 8) / $ThisFileInfo['playtime_seconds'];

				fseek($fd, $ThisFileInfo['avdataoffset'] - 44, SEEK_SET);
				$RIFFdata  = fread($fd, 44);
				$OrignalRIFFheaderSize = LittleEndian2Int(substr($RIFFdata,  4, 4)) +  8;
				$OrignalRIFFdataSize   = LittleEndian2Int(substr($RIFFdata, 40, 4)) + 44;

				if ($OrignalRIFFheaderSize > $OrignalRIFFdataSize) {
					$ThisFileInfo['avdataend'] -= ($OrignalRIFFheaderSize - $OrignalRIFFdataSize);
					fseek($fd, $ThisFileInfo['avdataend'], SEEK_SET);
					$RIFFdata .= fread($fd, $OrignalRIFFheaderSize - $OrignalRIFFdataSize);
				}

				// move the data chunk after all other chunks (if any)
				// so that the RIFF parser doesn't see EOF when trying
				// to skip over the data chunk
				$RIFFdata = substr($RIFFdata, 0, 36).substr($RIFFdata, 44).substr($RIFFdata, 36, 8);
				ParseRIFFdata($RIFFdata, $ThisFileInfo);
			}
			break;

		case 'AVI ':
			$ThisFileInfo['video']['bitrate_mode'] = 'cbr';
			$ThisFileInfo['video']['dataformat']   = 'avi';
			$ThisFileInfo['mime_type']             = 'video/avi';

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['movi']['offset'])) {
				$ThisFileInfo['avdataoffset'] = $ThisFileInfo['RIFF'][$arraykeys[0]]['movi']['offset'] + 8;
				$ThisFileInfo['avdataend']    = $ThisFileInfo['avdataoffset'] + $ThisFileInfo['RIFF'][$arraykeys[0]]['movi']['size'];
			}

			if (isset($ThisFileInfo['RIFF']['AVI ']['hdrl']['avih'][$streamindex]['data'])) {
				$avihData = $ThisFileInfo['RIFF']['AVI ']['hdrl']['avih'][$streamindex]['data'];
				$ThisFileInfo['RIFF']['raw']['avih']['dwMicroSecPerFrame']    = EitherEndian2Int($ThisFileInfo, substr($avihData,  0, 4)); // frame display rate (or 0L)
				if ($ThisFileInfo['RIFF']['raw']['avih']['dwMicroSecPerFrame'] == 0) {
					$ThisFileInfo['error'] .= 'Corrupt RIFF file: avih.dwMicroSecPerFrame == zero';
					return false;
				}
				$ThisFileInfo['RIFF']['raw']['avih']['dwMaxBytesPerSec']      = EitherEndian2Int($ThisFileInfo, substr($avihData,  4, 4)); // max. transfer rate
				$ThisFileInfo['RIFF']['raw']['avih']['dwPaddingGranularity']  = EitherEndian2Int($ThisFileInfo, substr($avihData,  8, 4)); // pad to multiples of this size; normally 2K.
				$ThisFileInfo['RIFF']['raw']['avih']['dwFlags']               = EitherEndian2Int($ThisFileInfo, substr($avihData, 12, 4)); // the ever-present flags
				$ThisFileInfo['RIFF']['raw']['avih']['dwTotalFrames']         = EitherEndian2Int($ThisFileInfo, substr($avihData, 16, 4)); // # frames in file
				$ThisFileInfo['RIFF']['raw']['avih']['dwInitialFrames']       = EitherEndian2Int($ThisFileInfo, substr($avihData, 20, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwStreams']             = EitherEndian2Int($ThisFileInfo, substr($avihData, 24, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwSuggestedBufferSize'] = EitherEndian2Int($ThisFileInfo, substr($avihData, 28, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwWidth']               = EitherEndian2Int($ThisFileInfo, substr($avihData, 32, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwHeight']              = EitherEndian2Int($ThisFileInfo, substr($avihData, 36, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwScale']               = EitherEndian2Int($ThisFileInfo, substr($avihData, 40, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwRate']                = EitherEndian2Int($ThisFileInfo, substr($avihData, 44, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwStart']               = EitherEndian2Int($ThisFileInfo, substr($avihData, 48, 4));
				$ThisFileInfo['RIFF']['raw']['avih']['dwLength']              = EitherEndian2Int($ThisFileInfo, substr($avihData, 52, 4));

				$ThisFileInfo['RIFF']['raw']['avih']['flags']['hasindex']     = (bool) ($ThisFileInfo['RIFF']['raw']['avih']['dwFlags'] & 0x00000010);
				$ThisFileInfo['RIFF']['raw']['avih']['flags']['mustuseindex'] = (bool) ($ThisFileInfo['RIFF']['raw']['avih']['dwFlags'] & 0x00000020);
				$ThisFileInfo['RIFF']['raw']['avih']['flags']['interleaved']  = (bool) ($ThisFileInfo['RIFF']['raw']['avih']['dwFlags'] & 0x00000100);
				$ThisFileInfo['RIFF']['raw']['avih']['flags']['trustcktype']  = (bool) ($ThisFileInfo['RIFF']['raw']['avih']['dwFlags'] & 0x00000800);
				$ThisFileInfo['RIFF']['raw']['avih']['flags']['capturedfile'] = (bool) ($ThisFileInfo['RIFF']['raw']['avih']['dwFlags'] & 0x00010000);
				$ThisFileInfo['RIFF']['raw']['avih']['flags']['copyrighted']  = (bool) ($ThisFileInfo['RIFF']['raw']['avih']['dwFlags'] & 0x00020010);


				if ($ThisFileInfo['RIFF']['raw']['avih']['dwWidth'] > 0) {
					$ThisFileInfo['RIFF']['video'][$streamindex]['frame_width']  = $ThisFileInfo['RIFF']['raw']['avih']['dwWidth'];
					$ThisFileInfo['video']['resolution_x']                       = $ThisFileInfo['RIFF']['video'][$streamindex]['frame_width'];
				}
				if ($ThisFileInfo['RIFF']['raw']['avih']['dwHeight'] > 0) {
					$ThisFileInfo['RIFF']['video'][$streamindex]['frame_height'] = $ThisFileInfo['RIFF']['raw']['avih']['dwHeight'];
					$ThisFileInfo['video']['resolution_y']                       = $ThisFileInfo['RIFF']['video'][$streamindex]['frame_height'];
				}
				$ThisFileInfo['RIFF']['video'][$streamindex]['frame_rate']   = round(1000000 / $ThisFileInfo['RIFF']['raw']['avih']['dwMicroSecPerFrame'], 3);
				$ThisFileInfo['video']['frame_rate'] = $ThisFileInfo['RIFF']['video'][$streamindex]['frame_rate'];
			}
			if (isset($ThisFileInfo['RIFF']['AVI ']['hdrl']['strl']['strh'][0]['data'])) {
				if (is_array($ThisFileInfo['RIFF']['AVI ']['hdrl']['strl']['strh'])) {
					for ($i = 0; $i < count($ThisFileInfo['RIFF']['AVI ']['hdrl']['strl']['strh']); $i++) {
						if (isset($ThisFileInfo['RIFF']['AVI ']['hdrl']['strl']['strh'][$i]['data'])) {
							$strhData = $ThisFileInfo['RIFF']['AVI ']['hdrl']['strl']['strh'][$i]['data'];
							$strhfccType = substr($strhData,  0, 4);

							if (isset($ThisFileInfo['RIFF']['AVI ']['hdrl']['strl']['strf'][$i]['data'])) {
								$strfData = $ThisFileInfo['RIFF']['AVI ']['hdrl']['strl']['strf'][$i]['data'];
								switch ($strhfccType) {
									case 'auds':
										$ThisFileInfo['audio']['bitrate_mode'] = 'cbr';
										$ThisFileInfo['audio']['dataformat']   = 'wav';
										if (isset($ThisFileInfo['RIFF']['audio']) && is_array($ThisFileInfo['RIFF']['audio'])) {
											$streamindex = count($ThisFileInfo['RIFF']['audio']);
										}

										$ThisFileInfo['RIFF']['audio'][$streamindex] = RIFFparseWAVEFORMATex($strfData);
										$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex] = $ThisFileInfo['RIFF']['audio'][$streamindex]['raw'];
										unset($ThisFileInfo['RIFF']['audio'][$streamindex]['raw']);

										$ThisFileInfo['audio'] = array_merge_noclobber($ThisFileInfo['audio'], $ThisFileInfo['RIFF']['audio'][$streamindex]);

										$ThisFileInfo['audio']['lossless'] = false;
										switch ($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['wFormatTag']) {
											case 0x0001:  // PCM
												$ThisFileInfo['audio']['lossless'] = true;
												break;

											case 0x0055: // MPEG Layer 3
												$ThisFileInfo['audio']['dataformat'] = 'mp3';
												break;

											case 0x0161: // Windows Media v7 / v8 / v9
											case 0x0162: // Windows Media Professional v9
											case 0x0163: // Windows Media Lossess v9
												$ThisFileInfo['audio']['dataformat'] = 'wma';
												break;

											case 0x2000: // AC-3
												$ThisFileInfo['audio']['dataformat'] = 'ac3';
												break;

											default:
												$ThisFileInfo['audio']['dataformat'] = 'wav';
												break;
										}

										break;


									case 'iavs':
									case 'vids':
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['fccType']               =                  substr($strhData,  0, 4);  // same as $strhfccType;
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['fccHandler']            =                  substr($strhData,  4, 4);
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwFlags']               = EitherEndian2Int($ThisFileInfo, substr($strhData,  8, 4)); // Contains AVITF_* flags
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['wPriority']             = EitherEndian2Int($ThisFileInfo, substr($strhData, 12, 2));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['wLanguage']             = EitherEndian2Int($ThisFileInfo, substr($strhData, 14, 2));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwInitialFrames']       = EitherEndian2Int($ThisFileInfo, substr($strhData, 16, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwScale']               = EitherEndian2Int($ThisFileInfo, substr($strhData, 20, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwRate']                = EitherEndian2Int($ThisFileInfo, substr($strhData, 24, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwStart']               = EitherEndian2Int($ThisFileInfo, substr($strhData, 28, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwLength']              = EitherEndian2Int($ThisFileInfo, substr($strhData, 32, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwSuggestedBufferSize'] = EitherEndian2Int($ThisFileInfo, substr($strhData, 36, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwQuality']             = EitherEndian2Int($ThisFileInfo, substr($strhData, 40, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['dwSampleSize']          = EitherEndian2Int($ThisFileInfo, substr($strhData, 44, 4));
										$ThisFileInfo['RIFF']['raw']['strh'][$i]['rcFrame']               = EitherEndian2Int($ThisFileInfo, substr($strhData, 48, 4));

										$ThisFileInfo['RIFF']['video'][$streamindex]['codec'] = RIFFfourccLookup($ThisFileInfo['RIFF']['raw']['strh'][$i]['fccHandler']);
										if (!$ThisFileInfo['RIFF']['video'][$streamindex]['codec'] && isset($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc']) && RIFFfourccLookup($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc'])) {
											$ThisFileInfo['RIFF']['video'][$streamindex]['codec'] = RIFFfourccLookup($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc']);
										}
										$ThisFileInfo['video']['codec']              = $ThisFileInfo['RIFF']['video'][$streamindex]['codec'];
										$ThisFileInfo['video']['pixel_aspect_ratio'] = (float) 1;
										switch ($ThisFileInfo['RIFF']['raw']['strh'][$i]['fccHandler']) {
											case 'HFYU': // Huffman Lossless Codec
											case 'IRAW': // Intel YUV Uncompressed
											case 'YUY2': // Uncompressed YUV 4:2:2
												$ThisFileInfo['video']['lossless'] = true;
												break;

											default:
												$ThisFileInfo['video']['lossless'] = false;
												break;
										}

										switch ($strhfccType) {
											case 'vids':
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biSize']          = EitherEndian2Int($ThisFileInfo, substr($strfData,  0, 4)); // number of bytes required by the BITMAPINFOHEADER structure
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biWidth']         = EitherEndian2Int($ThisFileInfo, substr($strfData,  4, 4)); // width of the bitmap in pixels
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biHeight']        = EitherEndian2Int($ThisFileInfo, substr($strfData,  8, 4)); // height of the bitmap in pixels. If biHeight is positive, the bitmap is a 'bottom-up' DIB and its origin is the lower left corner. If biHeight is negative, the bitmap is a 'top-down' DIB and its origin is the upper left corner
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biPlanes']        = EitherEndian2Int($ThisFileInfo, substr($strfData, 12, 2)); // number of color planes on the target device. In most cases this value must be set to 1
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biBitCount']      = EitherEndian2Int($ThisFileInfo, substr($strfData, 14, 2)); // Specifies the number of bits per pixels
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc']          =                                 substr($strfData, 16, 4);  //
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biSizeImage']     = EitherEndian2Int($ThisFileInfo, substr($strfData, 20, 4)); // size of the bitmap data section of the image (the actual pixel data, excluding BITMAPINFOHEADER and RGBQUAD structures)
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biXPelsPerMeter'] = EitherEndian2Int($ThisFileInfo, substr($strfData, 24, 4)); // horizontal resolution, in pixels per metre, of the target device
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biYPelsPerMeter'] = EitherEndian2Int($ThisFileInfo, substr($strfData, 28, 4)); // vertical resolution, in pixels per metre, of the target device
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biClrUsed']       = EitherEndian2Int($ThisFileInfo, substr($strfData, 32, 4)); // actual number of color indices in the color table used by the bitmap. If this value is zero, the bitmap uses the maximum number of colors corresponding to the value of the biBitCount member for the compression mode specified by biCompression
												$ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biClrImportant']  = EitherEndian2Int($ThisFileInfo, substr($strfData, 36, 4)); // number of color indices that are considered important for displaying the bitmap. If this value is zero, all colors are important

												$ThisFileInfo['video']['bits_per_sample'] = $ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['biBitCount'];

												if ($ThisFileInfo['RIFF']['video'][$streamindex]['codec'] == 'DV') {
													$ThisFileInfo['RIFF']['video'][$streamindex]['dv_type'] = 2;
												}
												break;

											case 'iavs':
												$ThisFileInfo['RIFF']['video'][$streamindex]['dv_type'] = 1;
												break;
										}
										break;

									default:
										$ThisFileInfo['warning'] .= "\n".'Unhandled fccType for stream ('.$i.'): "'.$strhfccType.'"';
										break;

								}
							}
						}

						if (isset($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc']) && RIFFfourccLookup($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc'])) {

							$ThisFileInfo['RIFF']['video'][$streamindex]['codec'] = RIFFfourccLookup($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc']);
							$ThisFileInfo['video']['codec'] = $ThisFileInfo['RIFF']['video'][$streamindex]['codec'];

							switch ($ThisFileInfo['RIFF']['raw']['strf']["$strhfccType"][$streamindex]['fourcc']) {
								case 'HFYU': // Huffman Lossless Codec
								case 'IRAW': // Intel YUV Uncompressed
								case 'YUY2': // Uncompressed YUV 4:2:2
									$ThisFileInfo['video']['lossless']        = true;
									$ThisFileInfo['video']['bits_per_sample'] = 24;
									break;

								default:
									$ThisFileInfo['video']['lossless']        = false;
									$ThisFileInfo['video']['bits_per_sample'] = 24;
									break;
							}

						}
					}
				}
			}
			break;

		case 'CDDA':
			$ThisFileInfo['audio']['bitrate_mode'] = 'cbr';
			$ThisFileInfo['audio']['dataformat']   = 'cda';
			$ThisFileInfo['audio']['lossless']     = true;
			unset($ThisFileInfo['mime_type']);

			$ThisFileInfo['avdataoffset'] = 44;

			if (isset($ThisFileInfo['RIFF']['CDDA']['fmt '][0]['data'])) {
				$fmtData = $ThisFileInfo['RIFF']['CDDA']['fmt '][0]['data'];
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['unknown1']           = EitherEndian2Int($ThisFileInfo, substr($fmtData,  0, 2));
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['track_num']          = EitherEndian2Int($ThisFileInfo, substr($fmtData,  2, 2));
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['disc_id']            = EitherEndian2Int($ThisFileInfo, substr($fmtData,  4, 4));
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['start_offset_frame'] = EitherEndian2Int($ThisFileInfo, substr($fmtData,  8, 4));
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['playtime_frames']    = EitherEndian2Int($ThisFileInfo, substr($fmtData, 12, 4));
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['unknown6']           = EitherEndian2Int($ThisFileInfo, substr($fmtData, 16, 4));
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['unknown7']           = EitherEndian2Int($ThisFileInfo, substr($fmtData, 20, 4));

				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['start_offset_seconds'] = (float) $ThisFileInfo['RIFF']['CDDA']['fmt '][0]['start_offset_frame'] / 75;
				$ThisFileInfo['RIFF']['CDDA']['fmt '][0]['playtime_seconds']     = (float) $ThisFileInfo['RIFF']['CDDA']['fmt '][0]['playtime_frames'] / 75;
				$ThisFileInfo['comments']['track']                               = $ThisFileInfo['RIFF']['CDDA']['fmt '][0]['track_num'];
				$ThisFileInfo['playtime_seconds']                                = $ThisFileInfo['RIFF']['CDDA']['fmt '][0]['playtime_seconds'];

				// hardcoded data for CD-audio
				$ThisFileInfo['audio']['sample_rate']     = 44100;
				$ThisFileInfo['audio']['channels']        = 2;
				$ThisFileInfo['audio']['bits_per_sample'] = 16;
				$ThisFileInfo['audio']['bitrate']         = $ThisFileInfo['audio']['sample_rate'] * $ThisFileInfo['audio']['channels'] * $ThisFileInfo['audio']['bits_per_sample'];
				$ThisFileInfo['audio']['bitrate_mode']    = 'cbr';
			}
			break;


		case 'AIFF':
		case 'AIFC':
			$ThisFileInfo['audio']['bitrate_mode'] = 'cbr';
			$ThisFileInfo['audio']['dataformat']   = 'aiff';
			$ThisFileInfo['audio']['lossless']     = true;
			$ThisFileInfo['mime_type']             = 'audio/x-aiff';

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['SSND'][0]['offset'])) {
				$ThisFileInfo['avdataoffset'] = $ThisFileInfo['RIFF'][$arraykeys[0]]['SSND'][0]['offset'] + 8;
				$ThisFileInfo['avdataend']    = $ThisFileInfo['avdataoffset'] + $ThisFileInfo['RIFF'][$arraykeys[0]]['SSND'][0]['size'];
				if ($ThisFileInfo['avdataend'] > $ThisFileInfo['filesize']) {
					if (($ThisFileInfo['avdataend'] == ($ThisFileInfo['filesize'] + 1)) && (($ThisFileInfo['filesize'] % 2) == 1)) {
						// structures rounded to 2-byte boundary, but dumb encoders
						// forget to pad end of file to make this actually work
					} else {
						$ThisFileInfo['warning'] .= "\n".'Probable truncated AIFF file: expecting '.$ThisFileInfo['RIFF'][$arraykeys[0]]['SSND'][0]['size'].' bytes of audio data, only '.($ThisFileInfo['filesize'] - $ThisFileInfo['avdataoffset']).' bytes found';
					}
					$ThisFileInfo['avdataend'] = $ThisFileInfo['filesize'];
				}
			}

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'])) {
				$ThisFileInfo['RIFF']['audio']['channels']         =         BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'],  0,  2), true);
				$ThisFileInfo['RIFF']['audio']['total_samples']    =         BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'],  2,  4), false);
				$ThisFileInfo['RIFF']['audio']['bits_per_sample']  =         BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'],  6,  2), true);
				$ThisFileInfo['RIFF']['audio']['sample_rate']      = (int) BigEndian2Float(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'],  8, 10));

				if ($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['size'] > 18) {
					$ThisFileInfo['RIFF']['audio']['codec_fourcc'] =                       substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'], 18,  4);
					$CodecNameSize                                 =         BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'], 22,  1), false);
					$ThisFileInfo['RIFF']['audio']['codec_name']   =                       substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMM'][0]['data'], 23,  $CodecNameSize);
					if ($ThisFileInfo['RIFF']['audio']['codec_name'] == 'NONE') {
						$ThisFileInfo['audio']['codec']    = 'Pulse Code Modulation (PCM)';
						$ThisFileInfo['audio']['lossless'] = true;
					} else {
						$ThisFileInfo['audio']['codec']    = $ThisFileInfo['RIFF']['audio']['codec_name'];
						$ThisFileInfo['audio']['lossless'] = false;
					}
				}

				$ThisFileInfo['audio']['channels']        = $ThisFileInfo['RIFF']['audio']['channels'];
				if ($ThisFileInfo['RIFF']['audio']['bits_per_sample'] > 0) {
					$ThisFileInfo['audio']['bits_per_sample'] = $ThisFileInfo['RIFF']['audio']['bits_per_sample'];
				}
				$ThisFileInfo['audio']['sample_rate']     = $ThisFileInfo['RIFF']['audio']['sample_rate'];
				if ($ThisFileInfo['audio']['sample_rate'] == 0) {
					$ThisFileInfo['error'] .= "\n".'Corrupted AIFF file: sample_rate == zero';
					return false;
				}
				$ThisFileInfo['playtime_seconds']         = $ThisFileInfo['RIFF']['audio']['total_samples'] / $ThisFileInfo['audio']['sample_rate'];
			}

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['COMT'])) {
				$offset = 0;
				$CommentCount                                           = BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMT'][0]['data'], $offset, 2), false);
				$offset += 2;
				for ($i = 0; $i < $CommentCount; $i++) {
					$ThisFileInfo['comments_raw'][$i]['timestamp']      = BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMT'][0]['data'], $offset, 4), false);
					$offset += 4;
					$ThisFileInfo['comments_raw'][$i]['marker_id']      = BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMT'][0]['data'], $offset, 2), true);
					$offset += 2;
					$CommentLength                                      = BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMT'][0]['data'], $offset, 2), false);
					$offset += 2;
					$ThisFileInfo['comments_raw'][$i]['comment']        =               substr($ThisFileInfo['RIFF'][$arraykeys[0]]['COMT'][0]['data'], $offset, $CommentLength);
					$offset += $CommentLength;

					$ThisFileInfo['comments_raw'][$i]['timestamp_unix'] = DateMac2Unix($ThisFileInfo['comments_raw'][$i]['timestamp']);
					$ThisFileInfo['RIFF']['comments']['comment'][]      = $ThisFileInfo['comments_raw'][$i]['comment'];
				}
			}

			$CommentsChunkNames = array('NAME'=>'title', 'author'=>'artist', '(c) '=>'copyright', 'ANNO'=>'comment');
			foreach ($CommentsChunkNames as $key => $value) {
				if (isset($ThisFileInfo['RIFF'][$arraykeys[0]][$key][0]['data'])) {
					$ThisFileInfo['RIFF']['comments'][$value][] = $ThisFileInfo['RIFF'][$arraykeys[0]][$key][0]['data'];
				}
			}

			if (isset($ThisFileInfo['RIFF']['comments'])) {
				CopyFormatCommentsToRootComments($ThisFileInfo['RIFF']['comments'], $ThisFileInfo, true, true, true);
			}

			break;

		case '8SVX':
			$ThisFileInfo['audio']['bitrate_mode']    = 'cbr';
			$ThisFileInfo['audio']['dataformat']      = '8svx';
			$ThisFileInfo['audio']['bits_per_sample'] = 8;
			$ThisFileInfo['audio']['channels']        = 1; // overridden below, if need be
			$ThisFileInfo['mime_type']                = 'audio/x-aiff';

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['BODY'][0]['offset'])) {
				$ThisFileInfo['avdataoffset'] = $ThisFileInfo['RIFF'][$arraykeys[0]]['BODY'][0]['offset'] + 8;
				$ThisFileInfo['avdataend']    = $ThisFileInfo['avdataoffset'] + $ThisFileInfo['RIFF'][$arraykeys[0]]['BODY'][0]['size'];
				if ($ThisFileInfo['avdataend'] > $ThisFileInfo['filesize']) {
					$ThisFileInfo['warning'] .= "\n".'Probable truncated AIFF file: expecting '.$ThisFileInfo['RIFF'][$arraykeys[0]]['BODY'][0]['size'].' bytes of audio data, only '.($ThisFileInfo['filesize'] - $ThisFileInfo['avdataoffset']).' bytes found';
				}
			}

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['offset'])) {
				$ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['oneShotHiSamples']  =   BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['data'],  0, 4));
				$ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['repeatHiSamples']   =   BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['data'],  4, 4));
				$ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['samplesPerHiCycle'] =   BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['data'],  8, 4));
				$ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['samplesPerSec']     =   BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['data'], 12, 2));
				$ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['ctOctave']          =   BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['data'], 14, 1));
				$ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['sCompression']      =   BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['data'], 15, 1));
				$ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['Volume']            = FixedPoint16_16(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['data'], 16, 4));

				$ThisFileInfo['audio']['sample_rate'] = $ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['samplesPerSec'];

				switch ($ThisFileInfo['RIFF'][$arraykeys[0]]['VHDR'][0]['sCompression']) {
					case 0:
						$ThisFileInfo['audio']['codec']    = 'Pulse Code Modulation (PCM)';
						$ThisFileInfo['audio']['lossless'] = true;
						$ActualBitsPerSample               = 8;
						break;

					case 1:
						$ThisFileInfo['audio']['codec']    = 'Fibonacci-delta encoding';
						$ThisFileInfo['audio']['lossless'] = false;
						$ActualBitsPerSample               = 4;
						break;

					default:
						$ThisFileInfo['warning'] .= "\n".'Unexpected sCompression value in 8SVX.VHDR chunk - expecting 0 or 1, found "'.sCompression.'"';
						break;
				}
			}

			if (isset($ThisFileInfo['RIFF'][$arraykeys[0]]['CHAN'][0]['data'])) {
				$ChannelsIndex = BigEndian2Int(substr($ThisFileInfo['RIFF'][$arraykeys[0]]['CHAN'][0]['data'], 0, 4));
				switch ($ChannelsIndex) {
					case 6: // Stereo
						$ThisFileInfo['audio']['channels'] = 2;
						break;

					case 2: // Left channel only
					case 4: // Right channel only
						$ThisFileInfo['audio']['channels'] = 1;
						break;

					default:
						$ThisFileInfo['warning'] .= "\n".'Unexpected value in 8SVX.CHAN chunk - expecting 2 or 4 or 6, found "'.$ChannelsIndex.'"';
						break;
				}

			}

			$CommentsChunkNames = array('NAME'=>'title', 'author'=>'artist', '(c) '=>'copyright', 'ANNO'=>'comment');
			foreach ($CommentsChunkNames as $key => $value) {
				if (isset($ThisFileInfo['RIFF'][$arraykeys[0]][$key][0]['data'])) {
					$ThisFileInfo['RIFF']['comments'][$value][] = $ThisFileInfo['RIFF'][$arraykeys[0]][$key][0]['data'];
				}
			}

			if (isset($ThisFileInfo['RIFF']['comments'])) {
				CopyFormatCommentsToRootComments($ThisFileInfo['RIFF']['comments'], $ThisFileInfo, true, true, true);
			}

			$ThisFileInfo['audio']['bitrate'] = $ThisFileInfo['audio']['sample_rate'] * $ActualBitsPerSample * $ThisFileInfo['audio']['channels'];
			if (!empty($ThisFileInfo['audio']['bitrate'])) {
				$ThisFileInfo['playtime_seconds'] = ($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) / ($ThisFileInfo['audio']['bitrate'] / 8);
			}
			break;

		default:
			$ThisFileInfo['error'] .= "\n".'Unknown RIFF type: expecting one of (WAVE|RMP3|AVI |CDDA|AIFF|AIFC|8SVX|), found "'.$arraykeys[0].'" instead';
			unset($ThisFileInfo['fileformat']);
			break;
	}

	if (isset($ThisFileInfo['RIFF']['WAVE']['DISP']) && is_array($ThisFileInfo['RIFF']['WAVE']['DISP'])) {
		$ThisFileInfo['tags'][] = 'riff';
		$ThisFileInfo['RIFF']['comments']['title'][] = trim(substr($ThisFileInfo['RIFF']['WAVE']['DISP'][count($ThisFileInfo['RIFF']['WAVE']['DISP']) - 1]['data'], 4));
	}
	if (isset($ThisFileInfo['RIFF']['WAVE']['INFO']) && is_array($ThisFileInfo['RIFF']['WAVE']['INFO'])) {
		$ThisFileInfo['tags'][] = 'riff';
		$RIFFinfoKeyLookup = array('IART'=>'artist', 'IGNR'=>'genre', 'ICMT'=>'comment', 'ICOP'=>'copyright', 'IENG'=>'engineers', 'IKEY'=>'keywords', 'IMED'=>'orignalmedium', 'INAM'=>'name', 'ISRC'=>'sourcesupplier', 'ITCH'=>'digitizer', 'ISBJ'=>'subject', 'ISRF'=>'digitizationsource', 'ISFT'=>'encoded_by');
		foreach ($RIFFinfoKeyLookup as $key => $value) {
			if (isset($ThisFileInfo['RIFF']['WAVE']['INFO']["$key"])) {
				foreach ($ThisFileInfo['RIFF']['WAVE']['INFO']["$key"] as $commentid => $commentdata) {
					if (trim($commentdata['data']) != '') {
						$ThisFileInfo['RIFF']['comments']["$value"][] = trim($commentdata['data']);
					}
				}
			}
		}
	}
	if (!empty($ThisFileInfo['RIFF']['comments'])) {
		CopyFormatCommentsToRootComments($ThisFileInfo['RIFF']['comments'], $ThisFileInfo, true, false, true);
	}

	if (empty($ThisFileInfo['audio']['encoder']) && !empty($ThisFileInfo['mpeg']['audio']['LAME']['short_version'])) {
		$ThisFileInfo['audio']['encoder'] = $ThisFileInfo['mpeg']['audio']['LAME']['short_version'];
	}
	if (!isset($ThisFileInfo['playtime_seconds'])) {
		$ThisFileInfo['playtime_seconds'] = 0;
	}
	if (isset($ThisFileInfo['RIFF']['raw']['avih']['dwTotalFrames']) && isset($ThisFileInfo['RIFF']['raw']['avih']['dwMicroSecPerFrame'])) {
		$ThisFileInfo['playtime_seconds'] = $ThisFileInfo['RIFF']['raw']['avih']['dwTotalFrames'] * ($ThisFileInfo['RIFF']['raw']['avih']['dwMicroSecPerFrame'] / 1000000);
	}

	if ($ThisFileInfo['playtime_seconds'] > 0) {

		if (isset($ThisFileInfo['RIFF']['audio']) && isset($ThisFileInfo['RIFF']['video'])) {

			if (!isset($ThisFileInfo['bitrate'])) {
				$ThisFileInfo['bitrate'] = ((($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) / $ThisFileInfo['playtime_seconds']) * 8);
			}

		} elseif (isset($ThisFileInfo['RIFF']['audio']) && !isset($ThisFileInfo['RIFF']['video'])) {

			if (!isset($ThisFileInfo['audio']['bitrate'])) {
				$ThisFileInfo['audio']['bitrate'] = ((($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) / $ThisFileInfo['playtime_seconds']) * 8);
			}

		} elseif (!isset($ThisFileInfo['RIFF']['audio']) && isset($ThisFileInfo['RIFF']['video'])) {

			if (!isset($ThisFileInfo['video']['bitrate'])) {
				$ThisFileInfo['video']['bitrate'] = ((($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) / $ThisFileInfo['playtime_seconds']) * 8);
			}

		}

	}


	if (isset($ThisFileInfo['RIFF']['video']) && isset($ThisFileInfo['audio']['bitrate']) && ($ThisFileInfo['audio']['bitrate'] > 0) && ($ThisFileInfo['playtime_seconds'] > 0)) {
		$ThisFileInfo['audio']['bitrate'] = 0;
		$ThisFileInfo['video']['bitrate'] = ((($ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']) / $ThisFileInfo['playtime_seconds']) * 8);
		foreach ($ThisFileInfo['RIFF']['audio'] as $channelnumber => $audioinfoarray) {
			$ThisFileInfo['video']['bitrate'] -= $audioinfoarray['bitrate'];
			$ThisFileInfo['audio']['bitrate'] += $audioinfoarray['bitrate'];
		}
		if ($ThisFileInfo['video']['bitrate'] <= 0) {
			unset($ThisFileInfo['video']['bitrate']);
		}
		if ($ThisFileInfo['audio']['bitrate'] <= 0) {
			unset($ThisFileInfo['audio']['bitrate']);
		}
	}

	if (!empty($ThisFileInfo['RIFF']['raw']['fmt ']['nBitsPerSample']) && ($ThisFileInfo['RIFF']['raw']['fmt ']['nBitsPerSample'] > 0)) {
		$ThisFileInfo['audio']['bits_per_sample'] = $ThisFileInfo['RIFF']['raw']['fmt ']['nBitsPerSample'];
	}

	return true;
}


function ParseRIFF(&$fd, $startoffset, $maxoffset, &$ThisFileInfo) {

	$maxoffset = min($maxoffset, $ThisFileInfo['avdataend']);

	$RIFFchunk = false;

	fseek($fd, $startoffset, SEEK_SET);

	while (ftell($fd) < $maxoffset) {
		$chunkname = fread($fd, 4);
		$chunksize = EitherEndian2Int($ThisFileInfo, fread($fd, 4));
		if (($chunksize % 2) != 0) {
			// all structures are packed on word boundaries
			$chunksize++;
		}

		switch ($chunkname) {

			case 'LIST':
				$listname = fread($fd, 4);
				switch ($listname) {
					case 'movi':
					case 'rec ':
						// skip over
						$RIFFchunk["$listname"]['offset'] = ftell($fd) - 4;
						$RIFFchunk["$listname"]['size']   = $chunksize;
						fseek($fd, $chunksize - 4, SEEK_CUR);
						break;

					default:
						if (!isset($RIFFchunk["$listname"])) {
							$RIFFchunk["$listname"] = array();
						}
						$RIFFchunk["$listname"] = array_merge_recursive($RIFFchunk["$listname"], ParseRIFF($fd, ftell($fd), ftell($fd) + $chunksize - 4, $ThisFileInfo));
						break;
				}
				break;

			default:
				$thisindex = 0;
				if (isset($RIFFchunk["$chunkname"]) && is_array($RIFFchunk["$chunkname"])) {
					$thisindex = count($RIFFchunk["$chunkname"]);
				}
				$RIFFchunk["$chunkname"][$thisindex]['offset'] = ftell($fd) - 8;
				$RIFFchunk["$chunkname"][$thisindex]['size']   = $chunksize;
				switch ($chunkname) {
					case 'data':
						$RIFFdataChunkContentsTest = fread($fd, 8);

						if ((strlen($RIFFdataChunkContentsTest) > 0) && ($RIFFdataChunkContentsTest{0} == chr(0xFF))) {

							// Probably (but not guaranteed) that this is MP3 data
							require_once(GETID3_INCLUDEPATH.'getid3.mp3.php');
							if (MPEGaudioHeaderBytesValid(substr($RIFFdataChunkContentsTest, 0, 4))) {
								$WhereWeWere = ftell($fd);
								getOnlyMPEGaudioInfo($fd, $ThisFileInfo, $RIFFchunk["$chunkname"][$thisindex]['offset'], false);
								fseek($fd, $WhereWeWere, SEEK_SET);
								if (isset($ThisFileInfo['mpeg']['audio'])) {
									$ThisFileInfo['audio']['dataformat']   = 'mp'.strlen($ThisFileInfo['mpeg']['audio']['layer']);
									$ThisFileInfo['audio']['sample_rate']  = $ThisFileInfo['mpeg']['audio']['sample_rate'];
									$ThisFileInfo['audio']['channels']     = $ThisFileInfo['mpeg']['audio']['channels'];
									$ThisFileInfo['audio']['bitrate']      = $ThisFileInfo['mpeg']['audio']['bitrate'] * 1000;
									$ThisFileInfo['bitrate']               = $ThisFileInfo['audio']['bitrate'];
									$ThisFileInfo['audio']['bitrate_mode'] = strtolower($ThisFileInfo['mpeg']['audio']['bitrate_mode']);
								}
								fseek($fd, $chunksize - 8, SEEK_CUR);
							}

						} else if (substr($RIFFdataChunkContentsTest, 0, 4) == 'wvpk') {

							// This is WavPack data
							$ThisFileInfo['wavpack']['offset'] = ftell($fd) - 8;
							$ThisFileInfo['wavpack']['size']   = LittleEndian2Int(substr($RIFFdataChunkContentsTest, 4, 4));
							$WavPackData                       = fread($fd, $ThisFileInfo['wavpack']['size']);
							RIFFparseWavPackHeader($WavPackData, $ThisFileInfo);
							fseek($fd, $chunksize - 8 - $ThisFileInfo['wavpack']['size'], SEEK_CUR);

						} else {

							// This is some other kind of data (quite possibly just PCM)
							// do nothing special, just skip it
							fseek($fd, $chunksize - 8, SEEK_CUR);

						}
						break;

					case 'bext':
					case 'cart':
					case 'fmt ':
					case 'MEXT':
					case 'DISP':
						// always read data in
						$RIFFchunk["$chunkname"][$thisindex]['data'] = fread($fd, $chunksize);
						break;

					default:
						// read data in if smaller than 2kB
						if ($chunksize < 2048) {
							$RIFFchunk["$chunkname"][$thisindex]['data'] = fread($fd, $chunksize);
						} else {
							fseek($fd, $chunksize, SEEK_CUR);
						}
						break;
				}
				break;

		}

	}

	return $RIFFchunk;
}


function ParseRIFFdata(&$RIFFdata, &$ThisFileInfo) {
	if ($RIFFdata) {
		if ($fp_temp = tmpfile()) {

			$RIFFdataLength = strlen($RIFFdata);
			$NewLengthString = LittleEndian2String($RIFFdataLength, 4);
			for ($i = 0; $i < 4; $i++) {
				$RIFFdata{$i + 4} = $NewLengthString{$i};
			}
			fwrite($fp_temp, $RIFFdata);
			$dummy = array('filesize'=>$RIFFdataLength, 'filenamepath'=>$ThisFileInfo['filenamepath'], 'tags'=>$ThisFileInfo['tags'], 'avdataoffset'=>0, 'avdataend'=>$RIFFdataLength, 'warning'=>$ThisFileInfo['warning'], 'error'=>$ThisFileInfo['error'], 'comments'=>$ThisFileInfo['comments']);
			if (getRIFFHeaderFilepointer($fp_temp, $dummy)) {
				$ThisFileInfo['RIFF']     = $dummy['RIFF'];
				$ThisFileInfo['warning']  = $dummy['warning'];
				$ThisFileInfo['error']    = $dummy['error'];
				$ThisFileInfo['tags']     = $dummy['tags'];
				$ThisFileInfo['comments'] = $dummy['comments'];
			}
			fclose($fp_temp);
			return true;

		} else {

			$ThisFileInfo['error'] .= "\n".'Error calling tmpfile() to parse OptimFROG RIFF header';

		}
	}
	return false;
}


function RIFFparseWAVEFORMATex($WaveFormatExData) {
	$WaveFormatEx['raw']['wFormatTag']      = LittleEndian2Int(substr($WaveFormatExData,  0, 2));
	$WaveFormatEx['raw']['nChannels']       = LittleEndian2Int(substr($WaveFormatExData,  2, 2));
	$WaveFormatEx['raw']['nSamplesPerSec']  = LittleEndian2Int(substr($WaveFormatExData,  4, 4));
	$WaveFormatEx['raw']['nAvgBytesPerSec'] = LittleEndian2Int(substr($WaveFormatExData,  8, 4));
	$WaveFormatEx['raw']['nBlockAlign']     = LittleEndian2Int(substr($WaveFormatExData, 12, 2));
	$WaveFormatEx['raw']['nBitsPerSample']  = LittleEndian2Int(substr($WaveFormatExData, 14, 2));

	$WaveFormatEx['codec']           = RIFFwFormatTagLookup($WaveFormatEx['raw']['wFormatTag']);
	$WaveFormatEx['channels']        = $WaveFormatEx['raw']['nChannels'];
	$WaveFormatEx['sample_rate']     = $WaveFormatEx['raw']['nSamplesPerSec'];
	$WaveFormatEx['bitrate']         = $WaveFormatEx['raw']['nAvgBytesPerSec'] * 8;
	$WaveFormatEx['bits_per_sample'] = $WaveFormatEx['raw']['nBitsPerSample'];

	return $WaveFormatEx;
}


function RIFFparseWavPackHeader(&$WavPackChunkData, &$ThisFileInfo) {
	// typedef struct {
	//     char ckID [4];
	//     long ckSize;
	//     short version;
	//     short bits;			    // added for version 2.00
	//     short flags, shift;		// added for version 3.00
	//     long total_samples, crc, crc2;
	//     char extension [4], extra_bc, extras [3];
	// } WavpackHeader;

	$ThisFileInfo['wavpack']['version'] = LittleEndian2Int(substr($WavPackChunkData, 0, 2));
	if ($ThisFileInfo['wavpack']['version'] >= 2) {
		$ThisFileInfo['wavpack']['bits']      = LittleEndian2Int(substr($WavPackChunkData, 2, 2));
	}
	if ($ThisFileInfo['wavpack']['version'] >= 3) {
		$ThisFileInfo['wavpack']['flags_raw']     = LittleEndian2Int(substr($WavPackChunkData,  4, 2));
		$ThisFileInfo['wavpack']['shift']         = LittleEndian2Int(substr($WavPackChunkData,  6, 2));
		$ThisFileInfo['wavpack']['total_samples'] = LittleEndian2Int(substr($WavPackChunkData,  8, 4));
		$ThisFileInfo['wavpack']['crc1']          = LittleEndian2Int(substr($WavPackChunkData, 12, 4));
		$ThisFileInfo['wavpack']['crc2']          = LittleEndian2Int(substr($WavPackChunkData, 16, 4));
		$ThisFileInfo['wavpack']['extension']     =                  substr($WavPackChunkData, 20, 4);
		$ThisFileInfo['wavpack']['extra_bc']      =                  substr($WavPackChunkData, 24, 1);
		$ThisFileInfo['wavpack']['extras']        =                  substr($WavPackChunkData, 25, 3);
	}

	return true;
}

function RIFFwFormatTagLookup($wFormatTag) {
	static $RIFFwFormatTagLookup = array();
	if (empty($RIFFwFormatTagLookup)) {
		$RIFFwFormatTagLookup[0x0000] = 'Microsoft Unknown Wave Format';
		$RIFFwFormatTagLookup[0x0001] = 'Pulse Code Modulation (PCM)';
		$RIFFwFormatTagLookup[0x0002] = 'Microsoft ADPCM';
		$RIFFwFormatTagLookup[0x0003] = 'IEEE Float';
		$RIFFwFormatTagLookup[0x0004] = 'Compaq Computer VSELP';
		$RIFFwFormatTagLookup[0x0005] = 'IBM CVSD';
		$RIFFwFormatTagLookup[0x0006] = 'Microsoft A-Law';
		$RIFFwFormatTagLookup[0x0007] = 'Microsoft mu-Law';
		$RIFFwFormatTagLookup[0x0008] = 'Microsoft DTS';
		$RIFFwFormatTagLookup[0x0010] = 'OKI ADPCM';
		$RIFFwFormatTagLookup[0x0011] = 'Intel DVI/IMA ADPCM';
		$RIFFwFormatTagLookup[0x0012] = 'Videologic MediaSpace ADPCM';
		$RIFFwFormatTagLookup[0x0013] = 'Sierra Semiconductor ADPCM';
		$RIFFwFormatTagLookup[0x0014] = 'Antex Electronics G.723 ADPCM';
		$RIFFwFormatTagLookup[0x0015] = 'DSP Solutions DigiSTD';
		$RIFFwFormatTagLookup[0x0016] = 'DSP Solutions DigiFIX';
		$RIFFwFormatTagLookup[0x0017] = 'Dialogic OKI ADPCM';
		$RIFFwFormatTagLookup[0x0018] = 'MediaVision ADPCM';
		$RIFFwFormatTagLookup[0x0019] = 'Hewlett-Packard CU';
		$RIFFwFormatTagLookup[0x0020] = 'Yamaha ADPCM';
		$RIFFwFormatTagLookup[0x0021] = 'Speech Compression Sonarc';
		$RIFFwFormatTagLookup[0x0022] = 'DSP Group TrueSpeech';
		$RIFFwFormatTagLookup[0x0023] = 'Echo Speech EchoSC1';
		$RIFFwFormatTagLookup[0x0024] = 'Audiofile AF36';
		$RIFFwFormatTagLookup[0x0025] = 'Audio Processing Technology APTX';
		$RIFFwFormatTagLookup[0x0026] = 'AudioFile AF10';
		$RIFFwFormatTagLookup[0x0027] = 'Prosody 1612';
		$RIFFwFormatTagLookup[0x0028] = 'LRC';
		$RIFFwFormatTagLookup[0x0030] = 'Dolby AC2';
		$RIFFwFormatTagLookup[0x0031] = 'Microsoft GSM 6.10';
		$RIFFwFormatTagLookup[0x0032] = 'MSNAudio';
		$RIFFwFormatTagLookup[0x0033] = 'Antex Electronics ADPCME';
		$RIFFwFormatTagLookup[0x0034] = 'Control Resources VQLPC';
		$RIFFwFormatTagLookup[0x0035] = 'DSP Solutions DigiREAL';
		$RIFFwFormatTagLookup[0x0036] = 'DSP Solutions DigiADPCM';
		$RIFFwFormatTagLookup[0x0037] = 'Control Resources CR10';
		$RIFFwFormatTagLookup[0x0038] = 'Natural MicroSystems VBXADPCM';
		$RIFFwFormatTagLookup[0x0039] = 'Crystal Semiconductor IMA ADPCM';
		$RIFFwFormatTagLookup[0x003A] = 'EchoSC3';
		$RIFFwFormatTagLookup[0x003B] = 'Rockwell ADPCM';
		$RIFFwFormatTagLookup[0x003C] = 'Rockwell Digit LK';
		$RIFFwFormatTagLookup[0x003D] = 'Xebec';
		$RIFFwFormatTagLookup[0x0040] = 'Antex Electronics G.721 ADPCM';
		$RIFFwFormatTagLookup[0x0041] = 'G.728 CELP';
		$RIFFwFormatTagLookup[0x0042] = 'MSG723';
		$RIFFwFormatTagLookup[0x0050] = 'Microsoft MPEG';
		$RIFFwFormatTagLookup[0x0052] = 'RT24';
		$RIFFwFormatTagLookup[0x0053] = 'PAC';
		$RIFFwFormatTagLookup[0x0055] = 'MPEG Layer 3';
		$RIFFwFormatTagLookup[0x0059] = 'Lucent G.723';
		$RIFFwFormatTagLookup[0x0060] = 'Cirrus';
		$RIFFwFormatTagLookup[0x0061] = 'ESPCM';
		$RIFFwFormatTagLookup[0x0062] = 'Voxware';
		$RIFFwFormatTagLookup[0x0063] = 'Canopus Atrac';
		$RIFFwFormatTagLookup[0x0064] = 'G.726 ADPCM';
		$RIFFwFormatTagLookup[0x0065] = 'G.722 ADPCM';
		$RIFFwFormatTagLookup[0x0066] = 'DSAT';
		$RIFFwFormatTagLookup[0x0067] = 'DSAT Display';
		$RIFFwFormatTagLookup[0x0069] = 'Voxware Byte Aligned';
		$RIFFwFormatTagLookup[0x0070] = 'Voxware AC8';
		$RIFFwFormatTagLookup[0x0071] = 'Voxware AC10';
		$RIFFwFormatTagLookup[0x0072] = 'Voxware AC16';
		$RIFFwFormatTagLookup[0x0073] = 'Voxware AC20';
		$RIFFwFormatTagLookup[0x0074] = 'Voxware MetaVoice';
		$RIFFwFormatTagLookup[0x0075] = 'Voxware MetaSound';
		$RIFFwFormatTagLookup[0x0076] = 'Voxware RT29HW';
		$RIFFwFormatTagLookup[0x0077] = 'Voxware VR12';
		$RIFFwFormatTagLookup[0x0078] = 'Voxware VR18';
		$RIFFwFormatTagLookup[0x0079] = 'Voxware TQ40';
		$RIFFwFormatTagLookup[0x0080] = 'Softsound';
		$RIFFwFormatTagLookup[0x0081] = 'Voxware TQ60';
		$RIFFwFormatTagLookup[0x0082] = 'MSRT24';
		$RIFFwFormatTagLookup[0x0083] = 'G.729A';
		$RIFFwFormatTagLookup[0x0084] = 'MVI MV12';
		$RIFFwFormatTagLookup[0x0085] = 'DF G.726';
		$RIFFwFormatTagLookup[0x0086] = 'DF GSM610';
		$RIFFwFormatTagLookup[0x0088] = 'ISIAudio';
		$RIFFwFormatTagLookup[0x0089] = 'Onlive';
		$RIFFwFormatTagLookup[0x0091] = 'SBC24';
		$RIFFwFormatTagLookup[0x0092] = 'Dolby AC3 SPDIF';
		$RIFFwFormatTagLookup[0x0093] = 'MediaSonic G.723';
		$RIFFwFormatTagLookup[0x0094] = 'Aculab PLC	Prosody 8kbps';
		$RIFFwFormatTagLookup[0x0097] = 'ZyXEL ADPCM';
		$RIFFwFormatTagLookup[0x0098] = 'Philips LPCBB';
		$RIFFwFormatTagLookup[0x0099] = 'Packed';
		$RIFFwFormatTagLookup[0x0100] = 'Rhetorex ADPCM';
		$RIFFwFormatTagLookup[0x0101] = 'IBM mu-law';
		$RIFFwFormatTagLookup[0x0102] = 'IBM A-law';
		$RIFFwFormatTagLookup[0x0103] = 'IBM AVC Adaptive Differential Pulse Code Modulation (ADPCM)';
		$RIFFwFormatTagLookup[0x0111] = 'Vivo G.723';
		$RIFFwFormatTagLookup[0x0112] = 'Vivo Siren';
		$RIFFwFormatTagLookup[0x0123] = 'Digital G.723';
		$RIFFwFormatTagLookup[0x0125] = 'Sanyo LD ADPCM';
		$RIFFwFormatTagLookup[0x0130] = 'Sipro Lab Telecom ACELP NET';
		$RIFFwFormatTagLookup[0x0131] = 'Sipro Lab Telecom ACELP 4800';
		$RIFFwFormatTagLookup[0x0132] = 'Sipro Lab Telecom ACELP 8V3';
		$RIFFwFormatTagLookup[0x0133] = 'Sipro Lab Telecom G.729';
		$RIFFwFormatTagLookup[0x0134] = 'Sipro Lab Telecom G.729A';
		$RIFFwFormatTagLookup[0x0135] = 'Sipro Lab Telecom Kelvin';
		$RIFFwFormatTagLookup[0x0140] = 'Windows Media Video V8';
		$RIFFwFormatTagLookup[0x0150] = 'Qualcomm PureVoice';
		$RIFFwFormatTagLookup[0x0151] = 'Qualcomm HalfRate';
		$RIFFwFormatTagLookup[0x0155] = 'Ring Zero Systems TUB GSM';
		$RIFFwFormatTagLookup[0x0160] = 'Microsoft Audio 1';
		$RIFFwFormatTagLookup[0x0161] = 'Windows Media Audio V7 / V8 / V9';
		$RIFFwFormatTagLookup[0x0162] = 'Windows Media Audio Professional V9';
		$RIFFwFormatTagLookup[0x0163] = 'Windows Media Audio Lossless V9';
		$RIFFwFormatTagLookup[0x0200] = 'Creative Labs ADPCM';
		$RIFFwFormatTagLookup[0x0202] = 'Creative Labs Fastspeech8';
		$RIFFwFormatTagLookup[0x0203] = 'Creative Labs Fastspeech10';
		$RIFFwFormatTagLookup[0x0210] = 'UHER Informatic GmbH ADPCM';
		$RIFFwFormatTagLookup[0x0220] = 'Quarterdeck';
		$RIFFwFormatTagLookup[0x0230] = 'I-link Worldwide VC';
		$RIFFwFormatTagLookup[0x0240] = 'Aureal RAW Sport';
		$RIFFwFormatTagLookup[0x0250] = 'Interactive Products HSX';
		$RIFFwFormatTagLookup[0x0251] = 'Interactive Products RPELP';
		$RIFFwFormatTagLookup[0x0260] = 'Consistent Software CS2';
		$RIFFwFormatTagLookup[0x0270] = 'Sony SCX';
		$RIFFwFormatTagLookup[0x0300] = 'Fujitsu FM Towns Snd';
		$RIFFwFormatTagLookup[0x0400] = 'BTV Digital';
		$RIFFwFormatTagLookup[0x0401] = 'Intel Music Coder';
		$RIFFwFormatTagLookup[0x0450] = 'QDesign Music';
		$RIFFwFormatTagLookup[0x0680] = 'VME VMPCM';
		$RIFFwFormatTagLookup[0x0681] = 'AT&T Labs TPC';
		$RIFFwFormatTagLookup[0x1000] = 'Olivetti GSM';
		$RIFFwFormatTagLookup[0x1001] = 'Olivetti ADPCM';
		$RIFFwFormatTagLookup[0x1002] = 'Olivetti CELP';
		$RIFFwFormatTagLookup[0x1003] = 'Olivetti SBC';
		$RIFFwFormatTagLookup[0x1004] = 'Olivetti OPR';
		$RIFFwFormatTagLookup[0x1100] = 'Lernout & Hauspie Codec (0x1100)';
		$RIFFwFormatTagLookup[0x1101] = 'Lernout & Hauspie CELP Codec (0x1101)';
		$RIFFwFormatTagLookup[0x1102] = 'Lernout & Hauspie SBC Codec (0x1102)';
		$RIFFwFormatTagLookup[0x1103] = 'Lernout & Hauspie SBC Codec (0x1103)';
		$RIFFwFormatTagLookup[0x1104] = 'Lernout & Hauspie SBC Codec (0x1104)';
		$RIFFwFormatTagLookup[0x1400] = 'Norris';
		$RIFFwFormatTagLookup[0x1401] = 'AT&T ISIAudio';
		$RIFFwFormatTagLookup[0x1500] = 'Soundspace Music Compression';
		$RIFFwFormatTagLookup[0x181C] = 'VoxWare RT24 Speech';
		$RIFFwFormatTagLookup[0x1FC4] = 'NCT Soft ALF2CD (www.nctsoft.com)';
		$RIFFwFormatTagLookup[0x2000] = 'Dolby AC3';
		$RIFFwFormatTagLookup[0x674F] = 'Ogg Vorbis 1';
		$RIFFwFormatTagLookup[0x6750] = 'Ogg Vorbis 2';
		$RIFFwFormatTagLookup[0x6751] = 'Ogg Vorbis 3';
		$RIFFwFormatTagLookup[0x676F] = 'Ogg Vorbis 1+';
		$RIFFwFormatTagLookup[0x6770] = 'Ogg Vorbis 2+';
		$RIFFwFormatTagLookup[0x6771] = 'Ogg Vorbis 3+';
		$RIFFwFormatTagLookup[0x7A21] = 'GSM-AMR (CBR, no SID)';
		$RIFFwFormatTagLookup[0x7A22] = 'GSM-AMR (VBR, including SID)';
		$RIFFwFormatTagLookup[0xFFFF] = 'development';
	}

	return (isset($RIFFwFormatTagLookup[$wFormatTag]) ? $RIFFwFormatTagLookup[$wFormatTag] : 'unknown: 0x'.dechex($wFormatTag));
}

function RIFFfourccLookup($fourcc) {
	static $RIFFfourccLookup = array();
	if (empty($RIFFfourccLookup)) {
		$RIFFfourccLookup['____'] = 'No Codec (____)';
		$RIFFfourccLookup['_BIT'] = 'BI_BITFIELDS (Raw RGB)';
		$RIFFfourccLookup['_JPG'] = 'JPEG compressed';
		$RIFFfourccLookup['_PNG'] = 'PNG compressed W3C/ISO/IEC (RFC-2083)';
		$RIFFfourccLookup['_RAW'] = 'Full Frames (Uncompressed)';
		$RIFFfourccLookup['_RGB'] = 'Raw RGB Bitmap';
		$RIFFfourccLookup['_RL4'] = '(RLE 4bpp RGB)';
		$RIFFfourccLookup['_RL8'] = '(RLE 8bpp RGB)';
		$RIFFfourccLookup['3IV1'] = '3ivx v1';
		$RIFFfourccLookup['3IV2'] = '3ivx v2';
		$RIFFfourccLookup['3IVX'] = '3IVX MPEG4-based codec (www.3ivx.com)';
		$RIFFfourccLookup['AASC'] = 'Autodesk Animator';
		$RIFFfourccLookup['ABYR'] = 'Kensington ?ABYR?';
		$RIFFfourccLookup['AEMI'] = 'Array Microsystems VideoONE MPEG1-I Capture';
		$RIFFfourccLookup['AFLC'] = 'Autodesk Animator FLC';
		$RIFFfourccLookup['AFLI'] = 'Autodesk Animator FLI';
		$RIFFfourccLookup['AMPG'] = 'Array Microsystems VideoONE MPEG';
		$RIFFfourccLookup['ANIM'] = 'Intel RDX (ANIM)';
		$RIFFfourccLookup['AP41'] = 'AngelPotion Definitive';
		$RIFFfourccLookup['ASV1'] = 'Asus Video v1';
		$RIFFfourccLookup['ASV2'] = 'Asus Video v2';
		$RIFFfourccLookup['ASVX'] = 'Asus Video 2.0 (audio)';
		$RIFFfourccLookup['AUR2'] = 'AuraVision Aura 2 Codec - YUV 4:2:2';
		$RIFFfourccLookup['AURA'] = 'AuraVision Aura 1 Codec - YUV 4:1:1';
		$RIFFfourccLookup['AVDJ'] = 'Independent JPEG Group\'s codec (AVDJ)';
		$RIFFfourccLookup['AVRN'] = 'Independent JPEG Group\'s codec (AVRN)';
		$RIFFfourccLookup['AYUV'] = '4:4:4 YUV (AYUV)';
		$RIFFfourccLookup['AZPR'] = 'Quicktime Apple Video (AZPR)';
		$RIFFfourccLookup['BGR '] = 'Raw RGB32';
		$RIFFfourccLookup['BLZ0'] = 'FFmpeg MPEG-4';
		$RIFFfourccLookup['BTVC'] = 'Conexant Composite Video';
		$RIFFfourccLookup['BINK'] = 'RAD Game Tools Bink Video';
		$RIFFfourccLookup['BT20'] = 'Conexant Prosumer Video';
		$RIFFfourccLookup['BTCV'] = 'Conexant Composite Video Codec';
		$RIFFfourccLookup['BW10'] = 'Data Translation Broadway MPEG Capture';
		$RIFFfourccLookup['CC12'] = 'Intel YUV12';
		$RIFFfourccLookup['CDVC'] = 'Canopus DV';
		$RIFFfourccLookup['CFCC'] = 'Digital Processing Systems DPS Perception';
		$RIFFfourccLookup['CGDI'] = 'Microsoft Office 97 Camcorder Video';
		$RIFFfourccLookup['CHAM'] = 'Winnov Caviara Champagne';
		$RIFFfourccLookup['CJPG'] = 'Creative WebCam JPEG';
		$RIFFfourccLookup['CLJR'] = 'Cirrus Logic YUV 4:1:1';
		$RIFFfourccLookup['CMYK'] = 'Common Data Format in Printing (Colorgraph)';
		$RIFFfourccLookup['CPLA'] = 'Weitek 4:2:0 YUV Planar';
		$RIFFfourccLookup['CRAM'] = 'Microsoft Video 1 (CRAM)';
		$RIFFfourccLookup['cvid'] = 'Radius Cinepak';
		$RIFFfourccLookup['CVID'] = 'Radius Cinepak';
		$RIFFfourccLookup['CWLT'] = 'Microsoft Color WLT DIB';
		$RIFFfourccLookup['CYUV'] = 'Creative Labs YUV';
		$RIFFfourccLookup['CYUY'] = 'ATI YUV';
		$RIFFfourccLookup['D261'] = 'H.261';
		$RIFFfourccLookup['D263'] = 'H.263';
		$RIFFfourccLookup['DIB '] = 'Device Independent Bitmap';
		$RIFFfourccLookup['DIV1'] = 'FFmpeg OpenDivX';
		$RIFFfourccLookup['DIV2'] = 'Microsoft MPEG-4 v1/v2';
		$RIFFfourccLookup['DIV3'] = 'DivX ;-) v3 MPEG-4 Low-Motion';
		$RIFFfourccLookup['DIV4'] = 'DivX ;-) v3 MPEG-4 Fast-Motion';
		$RIFFfourccLookup['DIV5'] = 'DivX 5.0';
		$RIFFfourccLookup['DIV6'] = 'DivX ;-) (MS MPEG-4 v3)';
		$RIFFfourccLookup['DIVX'] = 'DivX v4 (OpenDivX / Project Mayo)';
		$RIFFfourccLookup['divx'] = 'DivX';
		$RIFFfourccLookup['DMB1'] = 'Matrox Rainbow Runner hardware MJPEG';
		$RIFFfourccLookup['DMB2'] = 'Paradigm MJPEG';
		$RIFFfourccLookup['DSVD'] = '?DSVD?';
		$RIFFfourccLookup['DUCK'] = 'Duck TrueMotion 1.0';
		$RIFFfourccLookup['DPS0'] = 'DPS/Leitch Reality Motion JPEG';
		$RIFFfourccLookup['DPSC'] = 'DPS/Leitch PAR Motion JPEG';
		$RIFFfourccLookup['DV25'] = 'Matrox DVCPRO codec';
		$RIFFfourccLookup['DV50'] = 'Matrox DVCPRO50 codec';
		$RIFFfourccLookup['DVC '] = 'IEC 61834 and SMPTE 314M DVC/DV Video';
		$RIFFfourccLookup['DVCP'] = 'IEC 61834 and SMPTE 314M DVC/DV Video';
		$RIFFfourccLookup['DVHD'] = 'IEC Standard DV 1125 lines @ 30fps / 1250 lines @ 25fps';
		$RIFFfourccLookup['DVMA'] = 'Darim Vision DVMPEG (dummy for MPEG compressor) (www.darvision.com)';
		$RIFFfourccLookup['DVSL'] = 'IEC Standard DV compressed in SD (SDL)';
		$RIFFfourccLookup['DVAN'] = '?DVAN?';
		$RIFFfourccLookup['DVE2'] = 'InSoft DVE-2 Videoconferencing';
		$RIFFfourccLookup['dvsd'] = 'IEC 61834 and SMPTE 314M DVC/DV Video';
		$RIFFfourccLookup['DVSD'] = 'IEC 61834 and SMPTE 314M DVC/DV Video';
		$RIFFfourccLookup['DVX1'] = 'Lucent DVX1000SP Video Decoder';
		$RIFFfourccLookup['DVX2'] = 'Lucent DVX2000S Video Decoder';
		$RIFFfourccLookup['DVX3'] = 'Lucent DVX3000S Video Decoder';
		$RIFFfourccLookup['DX50'] = 'DivX v5';
		$RIFFfourccLookup['DXT1'] = 'Microsoft DirectX Compressed Texture (DXT1)';
		$RIFFfourccLookup['DXT2'] = 'Microsoft DirectX Compressed Texture (DXT2)';
		$RIFFfourccLookup['DXT3'] = 'Microsoft DirectX Compressed Texture (DXT3)';
		$RIFFfourccLookup['DXT4'] = 'Microsoft DirectX Compressed Texture (DXT4)';
		$RIFFfourccLookup['DXT5'] = 'Microsoft DirectX Compressed Texture (DXT5)';
		$RIFFfourccLookup['DXTC'] = 'Microsoft DirectX Compressed Texture (DXTC)';
		$RIFFfourccLookup['DXTn'] = 'Microsoft DirectX Compressed Texture (DXTn)';
		$RIFFfourccLookup['EM2V'] = 'Etymonix MPEG-2 I-frame (www.etymonix.com)';
		$RIFFfourccLookup['EKQ0'] = 'Elsa ?EKQ0?';
		$RIFFfourccLookup['ELK0'] = 'Elsa ?ELK0?';
		$RIFFfourccLookup['ESCP'] = 'Eidos Escape';
		$RIFFfourccLookup['ETV1'] = 'eTreppid Video ETV1';
		$RIFFfourccLookup['ETV2'] = 'eTreppid Video ETV2';
		$RIFFfourccLookup['ETVC'] = 'eTreppid Video ETVC';
		$RIFFfourccLookup['FLIC'] = 'Autodesk FLI/FLC Animation';
		$RIFFfourccLookup['FRWT'] = 'Darim Vision Forward Motion JPEG (www.darvision.com)';
		$RIFFfourccLookup['FRWU'] = 'Darim Vision Forward Uncompressed (www.darvision.com)';
		$RIFFfourccLookup['FLJP'] = 'D-Vision Field Encoded Motion JPEG';
		$RIFFfourccLookup['FRWA'] = 'SoftLab-Nsk Forward Motion JPEG w/ alpha channel';
		$RIFFfourccLookup['FRWD'] = 'SoftLab-Nsk Forward Motion JPEG';
		$RIFFfourccLookup['FVF1'] = 'Iterated Systems Fractal Video Frame';
		$RIFFfourccLookup['GLZW'] = 'Motion LZW (gabest@freemail.hu)';
		$RIFFfourccLookup['GPEG'] = 'Motion JPEG (gabest@freemail.hu)';
		$RIFFfourccLookup['GWLT'] = 'Microsoft Greyscale WLT DIB';
		$RIFFfourccLookup['H260'] = 'Intel ITU H.260 Videoconferencing';
		$RIFFfourccLookup['H261'] = 'Intel ITU H.261 Videoconferencing';
		$RIFFfourccLookup['H262'] = 'Intel ITU H.262 Videoconferencing';
		$RIFFfourccLookup['H263'] = 'Intel ITU H.263 Videoconferencing';
		$RIFFfourccLookup['H264'] = 'Intel ITU H.264 Videoconferencing';
		$RIFFfourccLookup['H265'] = 'Intel ITU H.265 Videoconferencing';
		$RIFFfourccLookup['H266'] = 'Intel ITU H.266 Videoconferencing';
		$RIFFfourccLookup['H267'] = 'Intel ITU H.267 Videoconferencing';
		$RIFFfourccLookup['H268'] = 'Intel ITU H.268 Videoconferencing';
		$RIFFfourccLookup['H269'] = 'Intel ITU H.269 Videoconferencing';
		$RIFFfourccLookup['HFYU'] = 'Huffman Lossless Codec';
		$RIFFfourccLookup['HMCR'] = 'Rendition Motion Compensation Format (HMCR)';
		$RIFFfourccLookup['HMRR'] = 'Rendition Motion Compensation Format (HMRR)';
		$RIFFfourccLookup['I263'] = 'FFmpeg I263 decoder';
		$RIFFfourccLookup['IF09'] = 'Indeo YVU9 ("YVU9 with additional delta-frame info after the U plane")';
		$RIFFfourccLookup['IUYV'] = 'Interlaced version of UYVY (www.leadtools.com)';
		$RIFFfourccLookup['IY41'] = 'Interlaced version of Y41P (www.leadtools.com)';
		$RIFFfourccLookup['IYU1'] = '12 bit format used in mode 2 of the IEEE 1394 Digital Camera 1.04 spec	IEEE standard';
		$RIFFfourccLookup['IYU2'] = '24 bit format used in mode 2 of the IEEE 1394 Digital Camera 1.04 spec	IEEE standard';
		$RIFFfourccLookup['IYUV'] = 'Planar YUV format (8-bpp Y plane, followed by 8-bpp 2×2 U and V planes)';
		$RIFFfourccLookup['i263'] = 'Intel ITU H.263 Videoconferencing (i263)';
		$RIFFfourccLookup['I420'] = 'Intel Indeo 4';
		$RIFFfourccLookup['IAN '] = 'Intel Indeo 4 (RDX)';
		$RIFFfourccLookup['ICLB'] = 'InSoft CellB Videoconferencing';
		$RIFFfourccLookup['IGOR'] = 'Power DVD';
		$RIFFfourccLookup['IJPG'] = 'Intergraph JPEG';
		$RIFFfourccLookup['ILVC'] = 'Intel Layered Video';
		$RIFFfourccLookup['ILVR'] = 'ITU-T H.263+';
		$RIFFfourccLookup['IPDV'] = 'I-O Data Device Giga AVI DV Codec';
		$RIFFfourccLookup['IR21'] = 'Intel Indeo 2.1';
		$RIFFfourccLookup['IRAW'] = 'Intel YUV Uncompressed';
		$RIFFfourccLookup['IV30'] = 'Intel Indeo 3.0';
		$RIFFfourccLookup['IV31'] = 'Intel Indeo 3.1';
		$RIFFfourccLookup['IV32'] = 'Ligos Indeo 3.2';
		$RIFFfourccLookup['IV33'] = 'Ligos Indeo 3.3';
		$RIFFfourccLookup['IV34'] = 'Ligos Indeo 3.4';
		$RIFFfourccLookup['IV35'] = 'Ligos Indeo 3.5';
		$RIFFfourccLookup['IV36'] = 'Ligos Indeo 3.6';
		$RIFFfourccLookup['IV37'] = 'Ligos Indeo 3.7';
		$RIFFfourccLookup['IV38'] = 'Ligos Indeo 3.8';
		$RIFFfourccLookup['IV39'] = 'Ligos Indeo 3.9';
		$RIFFfourccLookup['IV40'] = 'Ligos Indeo Interactive 4.0';
		$RIFFfourccLookup['IV41'] = 'Ligos Indeo Interactive 4.1';
		$RIFFfourccLookup['IV42'] = 'Ligos Indeo Interactive 4.2';
		$RIFFfourccLookup['IV43'] = 'Ligos Indeo Interactive 4.3';
		$RIFFfourccLookup['IV44'] = 'Ligos Indeo Interactive 4.4';
		$RIFFfourccLookup['IV45'] = 'Ligos Indeo Interactive 4.5';
		$RIFFfourccLookup['IV46'] = 'Ligos Indeo Interactive 4.6';
		$RIFFfourccLookup['IV47'] = 'Ligos Indeo Interactive 4.7';
		$RIFFfourccLookup['IV48'] = 'Ligos Indeo Interactive 4.8';
		$RIFFfourccLookup['IV49'] = 'Ligos Indeo Interactive 4.9';
		$RIFFfourccLookup['IV50'] = 'Ligos Indeo Interactive 5.0';
		$RIFFfourccLookup['JBYR'] = 'Kensington ?JBYR?';
		$RIFFfourccLookup['JPEG'] = 'Still Image JPEG DIB';
		$RIFFfourccLookup['JPGL'] = 'Pegasus Lossless Motion JPEG';
		$RIFFfourccLookup['KMVC'] = 'Team17 Software Karl Morton\'s Video Codec';
		$RIFFfourccLookup['LSVM'] = 'Vianet Lighting Strike Vmail (Streaming) (www.vianet.com)';
		$RIFFfourccLookup['LEAD'] = 'LEAD Video Codec';
		$RIFFfourccLookup['Ljpg'] = 'LEAD MJPEG Codec';
		$RIFFfourccLookup['MDVD'] = 'Alex MicroDVD Video (hacked MS MPEG-4) (www.tiasoft.de)';
		$RIFFfourccLookup['MJPA'] = 'Morgan Motion JPEG (MJPA) (www.morgan-multimedia.com)';
		$RIFFfourccLookup['MJPB'] = 'Morgan Motion JPEG (MJPB) (www.morgan-multimedia.com)';
		$RIFFfourccLookup['MMES'] = 'Matrox MPEG-2 I-frame';
		$RIFFfourccLookup['MP2v'] = 'Microsoft S-Mpeg 4 version 1 (MP2v)';
		$RIFFfourccLookup['MP42'] = 'Microsoft S-Mpeg 4 version 2 (MP42)';
		$RIFFfourccLookup['MP43'] = 'Microsoft S-Mpeg 4 version 3 (MP43)';
		$RIFFfourccLookup['MP4S'] = 'Microsoft S-Mpeg 4 version 3 (MP4S)';
		$RIFFfourccLookup['MP4V'] = 'FFmpeg MPEG-4';
		$RIFFfourccLookup['MPG1'] = 'FFmpeg MPEG 1/2';
		$RIFFfourccLookup['MPG2'] = 'FFmpeg MPEG 1/2';
		$RIFFfourccLookup['MPG3'] = 'FFmpeg DivX ;-) (MS MPEG-4 v3)';
		$RIFFfourccLookup['MPG4'] = 'Microsoft MPEG-4';
		$RIFFfourccLookup['MPGI'] = 'Sigma Designs MPEG';
		$RIFFfourccLookup['MPNG'] = 'PNG images decoder';
		$RIFFfourccLookup['MSS1'] = 'Microsoft Windows Screen Video';
		$RIFFfourccLookup['MSZH'] = 'LCL (Lossless Codec Library) (www.geocities.co.jp/Playtown-Denei/2837/LRC.htm)';
		$RIFFfourccLookup['M261'] = 'Microsoft H.261';
		$RIFFfourccLookup['M263'] = 'Microsoft H.263';
		$RIFFfourccLookup['M4S2'] = 'Microsoft Fully Compliant MPEG-4 v2 simple profile (M4S2)';
		$RIFFfourccLookup['m4s2'] = 'Microsoft Fully Compliant MPEG-4 v2 simple profile (m4s2)';
		$RIFFfourccLookup['MC12'] = 'ATI Motion Compensation Format (MC12)';
		$RIFFfourccLookup['MCAM'] = 'ATI Motion Compensation Format (MCAM)';
		$RIFFfourccLookup['MJ2C'] = 'Morgan Multimedia Motion JPEG2000';
		$RIFFfourccLookup['mJPG'] = 'IBM Motion JPEG w/ Huffman Tables';
		$RIFFfourccLookup['MJPG'] = 'Microsoft Motion JPEG DIB';
		$RIFFfourccLookup['MP42'] = 'Microsoft MPEG-4 (low-motion)';
		$RIFFfourccLookup['MP43'] = 'Microsoft MPEG-4 (fast-motion)';
		$RIFFfourccLookup['MP4S'] = 'Microsoft MPEG-4 (MP4S)';
		$RIFFfourccLookup['mp4s'] = 'Microsoft MPEG-4 (mp4s)';
		$RIFFfourccLookup['MPEG'] = 'Chromatic Research MPEG-1 Video I-Frame';
		$RIFFfourccLookup['MPG4'] = 'Microsoft MPEG-4 Video High Speed Compressor';
		$RIFFfourccLookup['MPGI'] = 'Sigma Designs MPEG';
		$RIFFfourccLookup['MRCA'] = 'FAST Multimedia Martin Regen Codec';
		$RIFFfourccLookup['MRLE'] = 'Microsoft Run Length Encoding';
		$RIFFfourccLookup['MSVC'] = 'Microsoft Video 1';
		$RIFFfourccLookup['MTX1'] = 'Matrox ?MTX1?';
		$RIFFfourccLookup['MTX2'] = 'Matrox ?MTX2?';
		$RIFFfourccLookup['MTX3'] = 'Matrox ?MTX3?';
		$RIFFfourccLookup['MTX4'] = 'Matrox ?MTX4?';
		$RIFFfourccLookup['MTX5'] = 'Matrox ?MTX5?';
		$RIFFfourccLookup['MTX6'] = 'Matrox ?MTX6?';
		$RIFFfourccLookup['MTX7'] = 'Matrox ?MTX7?';
		$RIFFfourccLookup['MTX8'] = 'Matrox ?MTX8?';
		$RIFFfourccLookup['MTX9'] = 'Matrox ?MTX9?';
		$RIFFfourccLookup['MV12'] = 'Motion Pixels Codec (old)';
		$RIFFfourccLookup['MWV1'] = 'Aware Motion Wavelets';
		$RIFFfourccLookup['nAVI'] = 'SMR Codec (hack of Microsoft MPEG-4) (IRC #shadowrealm)';
		$RIFFfourccLookup['NT00'] = 'NewTek LightWave HDTV YUV w/ Alpha (www.newtek.com)';
		$RIFFfourccLookup['NUV1'] = 'NuppelVideo';
		$RIFFfourccLookup['NTN1'] = 'Nogatech Video Compression 1';
		$RIFFfourccLookup['NVS0'] = 'nVidia GeForce Texture (NVS0)';
		$RIFFfourccLookup['NVS1'] = 'nVidia GeForce Texture (NVS1)';
		$RIFFfourccLookup['NVS2'] = 'nVidia GeForce Texture (NVS2)';
		$RIFFfourccLookup['NVS3'] = 'nVidia GeForce Texture (NVS3)';
		$RIFFfourccLookup['NVS4'] = 'nVidia GeForce Texture (NVS4)';
		$RIFFfourccLookup['NVS5'] = 'nVidia GeForce Texture (NVS5)';
		$RIFFfourccLookup['NVT0'] = 'nVidia GeForce Texture (NVT0)';
		$RIFFfourccLookup['NVT1'] = 'nVidia GeForce Texture (NVT1)';
		$RIFFfourccLookup['NVT2'] = 'nVidia GeForce Texture (NVT2)';
		$RIFFfourccLookup['NVT3'] = 'nVidia GeForce Texture (NVT3)';
		$RIFFfourccLookup['NVT4'] = 'nVidia GeForce Texture (NVT4)';
		$RIFFfourccLookup['NVT5'] = 'nVidia GeForce Texture (NVT5)';
		$RIFFfourccLookup['PIXL'] = 'MiroXL, Pinnacle PCTV';
		$RIFFfourccLookup['PDVC'] = 'I-O Data Device Digital Video Capture DV codec';
		$RIFFfourccLookup['PGVV'] = 'Radius Video Vision';
		$RIFFfourccLookup['PHMO'] = 'IBM Photomotion';
		$RIFFfourccLookup['PIM1'] = 'MPEG Realtime (Pinnacle Cards)';
		$RIFFfourccLookup['PIM2'] = 'Pegasus Imaging ?PIM2?';
		$RIFFfourccLookup['PIMJ'] = 'Pegasus Imaging Lossless JPEG';
		$RIFFfourccLookup['PVEZ'] = 'Horizons Technology PowerEZ';
		$RIFFfourccLookup['PVMM'] = 'PacketVideo Corporation MPEG-4';
		$RIFFfourccLookup['PVW2'] = 'Pegasus Imaging Wavelet Compression';
		$RIFFfourccLookup['Q1.0'] = 'Q-Team\'s QPEG 1.0 (www.q-team.de)';
		$RIFFfourccLookup['Q1.1'] = 'Q-Team\'s QPEG 1.1 (www.q-team.de)';
		$RIFFfourccLookup['QPEG'] = 'Q-Team QPEG 1.0';
		$RIFFfourccLookup['qpeq'] = 'Q-Team QPEG 1.1';
		$RIFFfourccLookup['RGB '] = 'Raw BGR32';
		$RIFFfourccLookup['RGBA'] = 'Raw RGB w/ Alpha';
		$RIFFfourccLookup['RMP4'] = 'REALmagic MPEG-4 (unauthorized XVID copy) (www.sigmadesigns.com)';
		$RIFFfourccLookup['ROQV'] = 'Id RoQ File Video Decoder';
		$RIFFfourccLookup['RPZA'] = 'Quicktime Apple Video (RPZA)';
		$RIFFfourccLookup['RUD0'] = 'Rududu video codec (http://rududu.ifrance.com/rududu/)';
		$RIFFfourccLookup['RV10'] = 'RealVideo 1.0 (RV10)';
		$RIFFfourccLookup['RV13'] = 'RealVideo 1.0 (RV13)';
		$RIFFfourccLookup['RV20'] = 'RealVideo 2.0';
		$RIFFfourccLookup['RV30'] = 'RealVideo 3.0';
		$RIFFfourccLookup['RV40'] = 'RealVideo 4.0';
		$RIFFfourccLookup['RGBT'] = 'Raw RGB w/ Transparency';
		$RIFFfourccLookup['RLE '] = 'Microsoft Run Length Encoder';
		$RIFFfourccLookup['RLE4'] = 'Run Length Encoded (4bpp, 16-color)';
		$RIFFfourccLookup['RLE8'] = 'Run Length Encoded (8bpp, 256-color)';
		$RIFFfourccLookup['RT21'] = 'Intel Indeo RealTime Video 2.1';
		$RIFFfourccLookup['rv20'] = 'RealVideo G2';
		$RIFFfourccLookup['rv30'] = 'RealVideo 8';
		$RIFFfourccLookup['RVX '] = 'Intel RDX (RVX )';
		$RIFFfourccLookup['SMC '] = 'Apple Graphics (SMC )';
		$RIFFfourccLookup['SP54'] = 'Logitech Sunplus Sp54 Codec for Mustek GSmart Mini 2';
		$RIFFfourccLookup['SPIG'] = 'Radius Spigot';
		$RIFFfourccLookup['SVQ3'] = 'Sorenson Video 3 (Apple Quicktime 5)';
		$RIFFfourccLookup['s422'] = 'Tekram VideoCap C210 YUV 4:2:2';
		$RIFFfourccLookup['SDCC'] = 'Sun Communication Digital Camera Codec';
		$RIFFfourccLookup['SFMC'] = 'CrystalNet Surface Fitting Method';
		$RIFFfourccLookup['SMSC'] = 'Radius SMSC';
		$RIFFfourccLookup['SMSD'] = 'Radius SMSD';
		$RIFFfourccLookup['smsv'] = 'WorldConnect Wavelet Video';
		$RIFFfourccLookup['SPIG'] = 'Radius Spigot';
		$RIFFfourccLookup['SPLC'] = 'Splash Studios ACM Audio Codec (www.splashstudios.net)';
		$RIFFfourccLookup['SQZ2'] = 'Microsoft VXTreme Video Codec V2';
		$RIFFfourccLookup['STVA'] = 'ST Microelectronics CMOS Imager Data (Bayer)';
		$RIFFfourccLookup['STVB'] = 'ST Microelectronics CMOS Imager Data (Nudged Bayer)';
		$RIFFfourccLookup['STVC'] = 'ST Microelectronics CMOS Imager Data (Bunched)';
		$RIFFfourccLookup['STVX'] = 'ST Microelectronics CMOS Imager Data (Extended CODEC Data Format)';
		$RIFFfourccLookup['STVY'] = 'ST Microelectronics CMOS Imager Data (Extended CODEC Data Format with Correction Data)';
		$RIFFfourccLookup['SV10'] = 'Sorenson Video R1';
		$RIFFfourccLookup['SVQ1'] = 'Sorenson Video';
		$RIFFfourccLookup['T420'] = 'Toshiba YUV 4:2:0';
		$RIFFfourccLookup['TM2A'] = 'Duck TrueMotion Archiver 2.0 (www.duck.com)';
		$RIFFfourccLookup['TVJP'] = 'Pinnacle/Truevision Targa 2000 board (TVJP)';
		$RIFFfourccLookup['TVMJ'] = 'Pinnacle/Truevision Targa 2000 board (TVMJ)';
		$RIFFfourccLookup['TY0N'] = 'Tecomac Low-Bit Rate Codec (www.tecomac.com)';
		$RIFFfourccLookup['TY2C'] = 'Trident Decompression Driver';
		$RIFFfourccLookup['TLMS'] = 'TeraLogic Motion Intraframe Codec (TLMS)';
		$RIFFfourccLookup['TLST'] = 'TeraLogic Motion Intraframe Codec (TLST)';
		$RIFFfourccLookup['TM20'] = 'Duck TrueMotion 2.0';
		$RIFFfourccLookup['TM2X'] = 'Duck TrueMotion 2X';
		$RIFFfourccLookup['TMIC'] = 'TeraLogic Motion Intraframe Codec (TMIC)';
		$RIFFfourccLookup['TMOT'] = 'Horizons Technology TrueMotion S';
		$RIFFfourccLookup['tmot'] = 'Horizons TrueMotion Video Compression';
		$RIFFfourccLookup['TR20'] = 'Duck TrueMotion RealTime 2.0';
		$RIFFfourccLookup['TSCC'] = 'TechSmith Screen Capture Codec';
		$RIFFfourccLookup['TV10'] = 'Tecomac Low-Bit Rate Codec';
		$RIFFfourccLookup['TY2N'] = 'Trident ?TY2N?';
		$RIFFfourccLookup['U263'] = 'UB Video H.263/H.263+/H.263++ Decoder';
		$RIFFfourccLookup['UMP4'] = 'UB Video MPEG 4 (www.ubvideo.com)';
		$RIFFfourccLookup['UYNV'] = 'Nvidia UYVY packed 4:2:2';
		$RIFFfourccLookup['UYVP'] = 'Evans & Sutherland YCbCr 4:2:2 extended precision';
		$RIFFfourccLookup['UCOD'] = 'eMajix.com ClearVideo';
		$RIFFfourccLookup['ULTI'] = 'IBM Ultimotion';
		$RIFFfourccLookup['UYVY'] = 'UYVY packed 4:2:2';
		$RIFFfourccLookup['V261'] = 'Lucent VX2000S';
		$RIFFfourccLookup['VIFP'] = 'VFAPI Reader Codec (www.yks.ne.jp/~hori/)';
		$RIFFfourccLookup['VIV1'] = 'FFmpeg H263+ decoder';
		$RIFFfourccLookup['VIV2'] = 'Vivo H.263';
		$RIFFfourccLookup['VQC2'] = 'Vector-quantised codec 2 (research) http://eprints.ecs.soton.ac.uk/archive/00001310/01/VTC97-js.pdf)';
		$RIFFfourccLookup['VTLP'] = 'Alaris VideoGramPiX';
		$RIFFfourccLookup['VYU9'] = 'ATI YUV (VYU9)';
		$RIFFfourccLookup['VYUY'] = 'ATI YUV (VYUY)';
		$RIFFfourccLookup['V261'] = 'Lucent VX2000S';
		$RIFFfourccLookup['V422'] = 'Vitec Multimedia 24-bit YUV 4:2:2 Format';
		$RIFFfourccLookup['V655'] = 'Vitec Multimedia 16-bit YUV 4:2:2 Format';
		$RIFFfourccLookup['VCR1'] = 'ATI Video Codec 1';
		$RIFFfourccLookup['VCR2'] = 'ATI Video Codec 2';
		$RIFFfourccLookup['VCR3'] = 'ATI VCR 3.0';
		$RIFFfourccLookup['VCR4'] = 'ATI VCR 4.0';
		$RIFFfourccLookup['VCR5'] = 'ATI VCR 5.0';
		$RIFFfourccLookup['VCR6'] = 'ATI VCR 6.0';
		$RIFFfourccLookup['VCR7'] = 'ATI VCR 7.0';
		$RIFFfourccLookup['VCR8'] = 'ATI VCR 8.0';
		$RIFFfourccLookup['VCR9'] = 'ATI VCR 9.0';
		$RIFFfourccLookup['VDCT'] = 'Vitec Multimedia Video Maker Pro DIB';
		$RIFFfourccLookup['VDOM'] = 'VDOnet VDOWave';
		$RIFFfourccLookup['VDOW'] = 'VDOnet VDOLive (H.263)';
		$RIFFfourccLookup['VDTZ'] = 'Darim Vison VideoTizer YUV';
		$RIFFfourccLookup['VGPX'] = 'Alaris VideoGramPiX';
		$RIFFfourccLookup['VIDS'] = 'Vitec Multimedia YUV 4:2:2 CCIR 601 for V422';
		$RIFFfourccLookup['VIVO'] = 'Vivo H.263 v2.00';
		$RIFFfourccLookup['vivo'] = 'Vivo H.263';
		$RIFFfourccLookup['VIXL'] = 'Miro/Pinnacle Video XL';
		$RIFFfourccLookup['VLV1'] = 'VideoLogic/PURE Digital Videologic Capture';
		$RIFFfourccLookup['VP30'] = 'On2 VP3.0';
		$RIFFfourccLookup['VP31'] = 'On2 VP3.1';
		$RIFFfourccLookup['VX1K'] = 'Lucent VX1000S Video Codec';
		$RIFFfourccLookup['VX2K'] = 'Lucent VX2000S Video Codec';
		$RIFFfourccLookup['VXSP'] = 'Lucent VX1000SP Video Codec';
		$RIFFfourccLookup['WBVC'] = 'Winbond W9960';
		$RIFFfourccLookup['WHAM'] = 'Microsoft Video 1 (WHAM)';
		$RIFFfourccLookup['WINX'] = 'Winnov Software Compression';
		$RIFFfourccLookup['WJPG'] = 'AverMedia Winbond JPEG';
		$RIFFfourccLookup['WMV1'] = 'Windows Media Video V7';
		$RIFFfourccLookup['WMV2'] = 'Windows Media Video V8';
		$RIFFfourccLookup['WMV3'] = 'Windows Media Video V9';
		$RIFFfourccLookup['WNV1'] = 'Winnov Hardware Compression';
		$RIFFfourccLookup['XYZP'] = 'Extended PAL format XYZ palette (www.riff.org)';
		$RIFFfourccLookup['x263'] = 'Xirlink H.263';
		$RIFFfourccLookup['XLV0'] = 'NetXL Video Decoder';
		$RIFFfourccLookup['XMPG'] = 'Xing MPEG (I-Frame only)';
		$RIFFfourccLookup['XVID'] = 'XviD MPEG-4 (www.xvid.org)';
		$RIFFfourccLookup['XXAN'] = '?XXAN?';
		$RIFFfourccLookup['Y422'] = 'ADS Technologies Copy of UYVY used in Pyro WebCam firewire camera';
		$RIFFfourccLookup['Y800'] = 'Simple, single Y plane for monochrome images';
		$RIFFfourccLookup['YU92'] = 'Intel YUV (YU92)';
		$RIFFfourccLookup['YUNV'] = 'Nvidia Uncompressed YUV 4:2:2';
		$RIFFfourccLookup['YUVP'] = 'Extended PAL format YUV palette (www.riff.org)';
		$RIFFfourccLookup['Y211'] = 'YUV 2:1:1 Packed';
		$RIFFfourccLookup['Y411'] = 'YUV 4:1:1 Packed';
		$RIFFfourccLookup['Y41B'] = 'Weitek YUV 4:1:1 Planar';
		$RIFFfourccLookup['Y41P'] = 'Brooktree PC1 YUV 4:1:1 Packed';
		$RIFFfourccLookup['Y41T'] = 'Brooktree PC1 YUV 4:1:1 with transparency';
		$RIFFfourccLookup['Y42B'] = 'Weitek YUV 4:2:2 Planar';
		$RIFFfourccLookup['Y42T'] = 'Brooktree UYUV 4:2:2 with transparency';
		$RIFFfourccLookup['Y8  '] = 'Grayscale video';
		$RIFFfourccLookup['YC12'] = 'Intel YUV 12 codec';
		$RIFFfourccLookup['YUV8'] = 'Winnov Caviar YUV8';
		$RIFFfourccLookup['YUV9'] = 'Intel YUV9';
		$RIFFfourccLookup['YUY2'] = 'Uncompressed YUV 4:2:2';
		$RIFFfourccLookup['YUYV'] = 'Canopus YUV';
		$RIFFfourccLookup['YV12'] = 'YVU12 Planar';
		$RIFFfourccLookup['YVU9'] = 'Intel YVU9 Planar (8-bpp Y plane, followed by 8-bpp 4x4 U and V planes)';
		$RIFFfourccLookup['YVYU'] = 'YVYU 4:2:2 Packed';
		$RIFFfourccLookup['ZLIB'] = 'Lossless Codec Library zlib compression (www.geocities.co.jp/Playtown-Denei/2837/LRC.htm)';
		$RIFFfourccLookup['ZPEG'] = 'Metheus Video Zipper';
	}

	return (isset($RIFFfourccLookup["$fourcc"]) ? $RIFFfourccLookup["$fourcc"] : '');
}

function EitherEndian2Int(&$ThisFileInfo, $byteword, $signed=false) {
	if ($ThisFileInfo['fileformat'] == 'riff') {
		return LittleEndian2Int($byteword, $signed);
	}
	return BigEndian2Int($byteword, false, $signed);
}

?>