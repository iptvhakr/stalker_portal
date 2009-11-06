<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.mpeg.php - part of getID3()                          //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

define('MPEG_VIDEO_PICTURE_START',   "\x00\x00\x01\x00");
//define('MPEG_VIDEO_SLICE_START',   "\x00\x00\x01\x01" .. 00\x00\x01\xAF);
//define('MPEG_VIDEO_RESERVED1',     "\x00\x00\x01\xB0");
//define('MPEG_VIDEO_RESERVED2',     "\x00\x00\x01\xB1");
define('MPEG_VIDEO_USER_DATA_START', "\x00\x00\x01\xB2");
define('MPEG_VIDEO_SEQUENCE_HEADER', "\x00\x00\x01\xB3");
define('MPEG_VIDEO_SEQUENCE_ERROR',  "\x00\x00\x01\xB4");
define('MPEG_VIDEO_EXTENSION_START', "\x00\x00\x01\xB5");
//define('MPEG_VIDEO_RESERVED3',     "\x00\x00\x01\xB6");
define('MPEG_VIDEO_SEQUENCE_END',    "\x00\x00\x01\xB7");
define('MPEG_VIDEO_GROUP_START',     "\x00\x00\x01\xB8");
//define('MPEG_VIDEO_SYSTEM_START',  "\x00\x00\x01\xB9" .. \x00\x00\x01\xFF);
define('MPEG_AUDIO_START',           "\x00\x00\x01\xC0");

function getMPEGHeaderFilepointer(&$fd, &$ThisFileInfo) {
	$ThisFileInfo['fileformat'] = 'mpeg';

	fseek($fd, $ThisFileInfo['avdataoffset'], SEEK_SET);
	$MPEGstreamData       = fread($fd, min(100000, $ThisFileInfo['avdataend'] - $ThisFileInfo['avdataoffset']));
	$MPEGstreamDataLength = strlen($MPEGstreamData);

	$foundVideo = true;
	$VideoChunkOffset = 0;
	while (substr($MPEGstreamData, $VideoChunkOffset++, 4) !== MPEG_VIDEO_SEQUENCE_HEADER) {
		if ($VideoChunkOffset >= $MPEGstreamDataLength) {
			$foundVideo = false;
			break 2;
		}
	}
	if ($foundVideo) {

		// Start code                       32 bits
		// horizontal frame size            12 bits
		// vertical frame size              12 bits
		// pixel aspect ratio                4 bits
		// frame rate                        4 bits
		// bitrate                          18 bits
		// marker bit                        1 bit
		// VBV buffer size                  10 bits
		// constrained parameter flag        1 bit
		// intra quant. matrix flag          1 bit
		// intra quant. matrix values      512 bits (present if matrix flag == 1)
		// non-intra quant. matrix flag      1 bit
		// non-intra quant. matrix values  512 bits (present if matrix flag == 1)

		$ThisFileInfo['video']['dataformat'] = 'mpeg';

		// I don't know how to differentiate between MPEG-1 and MPEG-2 video stream
		// Any information appreciated: info@getid3.org
		//$ThisFileInfo['video']['codec']      = 'MPEG-1';
		//$ThisFileInfo['video']['codec']      = 'MPEG-2';
		$ThisFileInfo['video']['codec']      = 'MPEG';

		$VideoChunkOffset += (strlen(MPEG_VIDEO_SEQUENCE_HEADER) - 1);

		$FrameSizeDWORD = BigEndian2Int(substr($MPEGstreamData, $VideoChunkOffset, 3));
		$VideoChunkOffset += 3;

		$AspectRatioFrameRateDWORD = BigEndian2Int(substr($MPEGstreamData, $VideoChunkOffset, 1));
		$VideoChunkOffset += 1;

		$assortedinformation = BigEndian2Bin(substr($MPEGstreamData, $VideoChunkOffset, 4));
		$VideoChunkOffset += 4;

		$ThisFileInfo['mpeg']['video']['raw']['framesize_horizontal'] = ($FrameSizeDWORD & 0xFFF000) >> 12; // 12 bits for horizontal frame size
		$ThisFileInfo['mpeg']['video']['raw']['framesize_vertical']   = ($FrameSizeDWORD & 0x000FFF);       // 12 bits for vertical frame size
		$ThisFileInfo['mpeg']['video']['raw']['pixel_aspect_ratio']   = ($AspectRatioFrameRateDWORD & 0xF0) >> 4;
		$ThisFileInfo['mpeg']['video']['raw']['frame_rate']           = ($AspectRatioFrameRateDWORD & 0x0F);

		$ThisFileInfo['mpeg']['video']['framesize_horizontal'] = $ThisFileInfo['mpeg']['video']['raw']['framesize_horizontal'];
		$ThisFileInfo['mpeg']['video']['framesize_vertical']   = $ThisFileInfo['mpeg']['video']['raw']['framesize_vertical'];

		$ThisFileInfo['mpeg']['video']['pixel_aspect_ratio']      = MPEGvideoAspectRatioLookup($ThisFileInfo['mpeg']['video']['raw']['pixel_aspect_ratio']);
		$ThisFileInfo['mpeg']['video']['pixel_aspect_ratio_text'] = MPEGvideoAspectRatioTextLookup($ThisFileInfo['mpeg']['video']['raw']['pixel_aspect_ratio']);
		$ThisFileInfo['mpeg']['video']['frame_rate']              = MPEGvideoFramerateLookup($ThisFileInfo['mpeg']['video']['raw']['frame_rate']);

		$ThisFileInfo['mpeg']['video']['raw']['bitrate']                = Bin2Dec(substr($assortedinformation,  0, 18));
		$ThisFileInfo['mpeg']['video']['raw']['marker_bit']             = Bin2Dec(substr($assortedinformation, 18,  1));
		$ThisFileInfo['mpeg']['video']['raw']['vbv_buffer_size']        = Bin2Dec(substr($assortedinformation, 19, 10));
		$ThisFileInfo['mpeg']['video']['raw']['constrained_param_flag'] = Bin2Dec(substr($assortedinformation, 29,  1));
		$ThisFileInfo['mpeg']['video']['raw']['intra_quant_flag']       = Bin2Dec(substr($assortedinformation, 30,  1));

		if ($ThisFileInfo['mpeg']['video']['raw']['bitrate'] == 0x3FFFF) { // 18 set bits

			$ThisFileInfo['warning'] .= "\n".'This version of getID3() ['.GETID3VERSION.'] cannot determine average bitrate of VBR MPEG video files';
			$ThisFileInfo['mpeg']['video']['bitrate_mode'] = 'vbr';

		} else {

			$ThisFileInfo['mpeg']['video']['bitrate']      = $ThisFileInfo['mpeg']['video']['raw']['bitrate'] * 400;
			$ThisFileInfo['mpeg']['video']['bitrate_mode'] = 'cbr';
			$ThisFileInfo['video']['bitrate']              = $ThisFileInfo['mpeg']['video']['bitrate'];

		}

		$ThisFileInfo['video']['resolution_x']       = $ThisFileInfo['mpeg']['video']['framesize_horizontal'];
		$ThisFileInfo['video']['resolution_y']       = $ThisFileInfo['mpeg']['video']['framesize_vertical'];
		$ThisFileInfo['video']['frame_rate']         = $ThisFileInfo['mpeg']['video']['frame_rate'];
		$ThisFileInfo['video']['bitrate_mode']       = $ThisFileInfo['mpeg']['video']['bitrate_mode'];
		$ThisFileInfo['video']['pixel_aspect_ratio'] = $ThisFileInfo['mpeg']['video']['pixel_aspect_ratio'];
		$ThisFileInfo['video']['lossless']           = false;
		$ThisFileInfo['video']['bits_per_sample']    = 24;

	} else {

		$ThisFileInfo['error'] .= "\n".'Could not find start of video block in the first 100,000 bytes (or before end of file) - this might not be an MPEG-video file?';

	}



	$AudioChunkOffset = 0;
	while (true) {
		while (substr($MPEGstreamData, $AudioChunkOffset++, 4) !== MPEG_AUDIO_START) {
			if ($AudioChunkOffset >= $MPEGstreamDataLength) {
				break 2;
			}
		}

		require_once(GETID3_INCLUDEPATH.'getid3.mp3.php');
		for ($i = 0; $i <= 2; $i++) {
			// some files have the MPEG-audio header 8 bytes after the end of the $00 $00 $01 $C0 signature, some have it 9 bytes, some 10 bytes after
			// I have no idea why or what the difference is, so this is a stupid hack.
			// If anybody has any better idea of what's going on, please let me know - info@getid3.org

			$dummy = $ThisFileInfo;
			if (decodeMPEGaudioHeader($fd, ($AudioChunkOffset + 3) + 8 + $i, $dummy, false)) {

				$ThisFileInfo = $dummy;
				$ThisFileInfo['audio']['bits_per_sample'] = 16;
				$ThisFileInfo['audio']['bitrate_mode']    = 'cbr';
				$ThisFileInfo['audio']['lossless']        = false;
				break 2;

			}
		}

	}

	return true;
}


function MPEGvideoFramerateLookup($rawframerate) {
	$MPEGvideoFramerateLookup = array(0, 23.976, 24, 25, 29.97, 30, 50, 59.94, 60);
	return (isset($MPEGvideoFramerateLookup[$rawframerate]) ? (float) $MPEGvideoFramerateLookup[$rawframerate] : (float) 0);
}

function MPEGvideoAspectRatioLookup($rawaspectratio) {
	$MPEGvideoAspectRatioLookup = array(0, 1, 0.6735, 0.7031, 0.7615, 0.8055, 0.8437, 0.8935, 0.9157, 0.9815, 1.0255, 1.0695, 1.0950, 1.1575, 1.2015, 0);
	return (isset($MPEGvideoAspectRatioLookup[$rawaspectratio]) ? (float) $MPEGvideoAspectRatioLookup[$rawaspectratio] : (float) 0);
}

function MPEGvideoAspectRatioTextLookup($rawaspectratio) {
	$MPEGvideoAspectRatioTextLookup = array('forbidden', 'square pixels', '0.6735', '16:9, 625 line, PAL', '0.7615', '0.8055', '16:9, 525 line, NTSC', '0.8935', '4:3, 625 line, PAL, CCIR601', '0.9815', '1.0255', '1.0695', '4:3, 525 line, NTSC, CCIR601', '1.1575', '1.2015', 'reserved');
	return (isset($MPEGvideoAspectRatioTextLookup[$rawaspectratio]) ? $MPEGvideoAspectRatioTextLookup[$rawaspectratio] : '');
}

?>