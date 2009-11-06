<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.ape.php - part of getID3()                           //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getAPEtagFilepointer(&$fd, &$ThisFileInfo) {
	$id3v1tagsize     = 128;
	$apetagheadersize = 32;
	fseek($fd, 0 - $id3v1tagsize - $apetagheadersize, SEEK_END);
	$APEfooterID3v1 = fread($fd, $id3v1tagsize + $apetagheadersize);
	if ((substr($APEfooterID3v1, 0, strlen('APETAGEX')) == 'APETAGEX') && (substr($APEfooterID3v1, $apetagheadersize, strlen('TAG')) == 'TAG')) {

		// APE tag found before ID3v1
		$APEfooterData = substr($APEfooterID3v1, 0, $apetagheadersize);
		$APEfooterOffset = 0 - $apetagheadersize - $id3v1tagsize;

	} elseif (substr($APEfooterID3v1, $id3v1tagsize, strlen('APETAGEX')) == 'APETAGEX') {

		// APE tag found, no ID3v1
		$APEfooterData = substr($APEfooterID3v1, $id3v1tagsize, $apetagheadersize);
		$APEfooterOffset = 0 - $apetagheadersize;

	} else {

		// APE tag not found
		return false;

	}

	$ThisFileInfo['ape']['tag_offset_end'] = $ThisFileInfo['filesize'] - ($APEfooterOffset + $apetagheadersize);
	if (empty($ThisFileInfo['fileformat'])) {
		$ThisFileInfo['fileformat'] = 'ape';
	}
	if (!($ThisFileInfo['ape']['footer'] = parseAPEheaderFooter($APEfooterData))) {
		$ThisFileInfo['error'] .= "\n".'Error parsing APE footer at offset '.$ThisFileInfo['ape']['tag_offset_end'];
		return false;
	}

	if (isset($ThisFileInfo['ape']['footer']['flags']['header']) && $ThisFileInfo['ape']['footer']['flags']['header']) {
		fseek($fd, $APEfooterOffset - $ThisFileInfo['ape']['footer']['raw']['tagsize'] + $apetagheadersize - $apetagheadersize, SEEK_END);
		$ThisFileInfo['ape']['tag_offset_start'] = ftell($fd);
		$APEtagData = fread($fd, $ThisFileInfo['ape']['footer']['raw']['tagsize'] + $apetagheadersize);
	} else {
		fseek($fd, $APEfooterOffset - $ThisFileInfo['ape']['footer']['raw']['tagsize'] + $apetagheadersize, SEEK_END);
		$ThisFileInfo['ape']['tag_offset_start'] = ftell($fd);
		$APEtagData = fread($fd, $ThisFileInfo['ape']['footer']['raw']['tagsize']);
	}
	$offset = 0;
	if (isset($ThisFileInfo['ape']['footer']['flags']['header']) && $ThisFileInfo['ape']['footer']['flags']['header']) {
		if ($ThisFileInfo['ape']['header'] = parseAPEheaderFooter(substr($APEtagData, 0, $apetagheadersize))) {
			$offset += $apetagheadersize;
		} else {
			$ThisFileInfo['error'] .= "\n".'Error parsing APE header at offset '.$ThisFileInfo['ape']['tag_offset_start'];
			return false;
		}
	}

	for ($i = 0; $i < $ThisFileInfo['ape']['footer']['raw']['tag_items']; $i++) {
		$value_size = LittleEndian2Int(substr($APEtagData, $offset, 4));
		$offset += 4;
		$item_flags = LittleEndian2Int(substr($APEtagData, $offset, 4));
		$offset += 4;
		if (strstr(substr($APEtagData, $offset), chr(0)) === false) {
			$ThisFileInfo['error'] .= "\n".'Cannot find null-byte (0x00) seperator between ItemKey #'.$i.' and value. ItemKey starts '.$offset.' bytes into the APE tag, at file offset '.($ThisFileInfo['ape']['tag_offset_start'] + $offset);
			return false;
		}
		$ItemKeyLength = strpos($APEtagData, chr(0), $offset) - $offset;
		$item_key      = strtolower(substr($APEtagData, $offset, $ItemKeyLength));
		$offset += ($ItemKeyLength + 1); // skip 0x00 terminator
		$ThisFileInfo['ape']['items']["$item_key"]['data'] = substr($APEtagData, $offset, $value_size);
		$offset += $value_size;

		$ThisFileInfo['ape']['items']["$item_key"]['flags'] = parseAPEtagFlags($item_flags);
		switch ($ThisFileInfo['ape']['items']["$item_key"]['flags']['item_contents_raw']) {
			case 0: // UTF-8
			case 3: // Locator (URL, filename, etc), UTF-8 encoded
				$ThisFileInfo['ape']['items']["$item_key"]['data'] = explode(chr(0), trim($ThisFileInfo['ape']['items']["$item_key"]['data']));
				foreach ($ThisFileInfo['ape']['items']["$item_key"]['data'] as $key => $value) {
					$ThisFileInfo['ape']['items']["$item_key"]['data_ascii'][$key] = RoughTranslateUnicodeToASCII($value, 3);
				}
				break;

			default: // binary data
				//$ThisFileInfo['ape']['items']["$item_key"]['data_ascii'] = null;
				break;
		}

		switch ($item_key) {
			case 'replaygain_track_gain':
				$ThisFileInfo['replay_gain']['radio']['adjustment']      = (float) $ThisFileInfo['ape']['items']["$item_key"]['data_ascii'][0];
				$ThisFileInfo['replay_gain']['radio']['originator']      = 'unspecified';
				break;

			case 'replaygain_track_peak':
				$ThisFileInfo['replay_gain']['radio']['peak']            = (float) $ThisFileInfo['ape']['items']["$item_key"]['data_ascii'][0];
				$ThisFileInfo['replay_gain']['radio']['originator']      = 'unspecified';
				break;

			case 'replaygain_album_gain':
				$ThisFileInfo['replay_gain']['audiophile']['adjustment'] = (float) $ThisFileInfo['ape']['items']["$item_key"]['data_ascii'][0];
				$ThisFileInfo['replay_gain']['audiophile']['originator'] = 'unspecified';
				break;

			case 'replaygain_album_peak':
				$ThisFileInfo['replay_gain']['audiophile']['peak']       = (float) $ThisFileInfo['ape']['items']["$item_key"]['data_ascii'][0];
				$ThisFileInfo['replay_gain']['audiophile']['originator'] = 'unspecified';
				break;

			default:
				foreach ($ThisFileInfo['ape']['items']["$item_key"]['data_ascii'] as $comment) {
					$ThisFileInfo['ape']['comments'][strtolower($item_key)][] = $comment;
				}
				break;

		}

	}
	if (isset($ThisFileInfo['ape']['comments'])) {
		CopyFormatCommentsToRootComments($ThisFileInfo['ape']['comments'], $ThisFileInfo, true, true, true);
	}

	return true;
}

function parseAPEheaderFooter($APEheaderFooterData) {
	// http://www.uni-jena.de/~pfk/mpp/sv8/apeheader.html
	$headerfooterinfo['raw']['footer_tag']   =                  substr($APEheaderFooterData,  0, 8);
	if ($headerfooterinfo['raw']['footer_tag'] != 'APETAGEX') {
		return false;
	}
	$headerfooterinfo['raw']['version']      = LittleEndian2Int(substr($APEheaderFooterData,  8, 4));
	$headerfooterinfo['raw']['tagsize']      = LittleEndian2Int(substr($APEheaderFooterData, 12, 4));
	$headerfooterinfo['raw']['tag_items']    = LittleEndian2Int(substr($APEheaderFooterData, 16, 4));
	$headerfooterinfo['raw']['global_flags'] = LittleEndian2Int(substr($APEheaderFooterData, 20, 4));
	$headerfooterinfo['raw']['reserved']     =                  substr($APEheaderFooterData, 24, 8);

	$headerfooterinfo['tag_version']         = $headerfooterinfo['raw']['version'] / 1000;
	if ($headerfooterinfo['tag_version'] >= 2) {
		$headerfooterinfo['flags'] = parseAPEtagFlags($headerfooterinfo['raw']['global_flags']);
	}
	return $headerfooterinfo;
}

function parseAPEtagFlags($rawflagint) {
	// "Note: APE Tags 1.0 do not use any of the APE Tag flags.
	// All are set to zero on creation and ignored on reading."
	// http://www.uni-jena.de/~pfk/mpp/sv8/apetagflags.html
	$flags['header']            = (bool) ($rawflagint & 0x80000000);
	$flags['footer']            = (bool) ($rawflagint & 0x40000000);
	$flags['this_is_header']    = (bool) ($rawflagint & 0x20000000);
	$flags['item_contents_raw'] =        ($rawflagint & 0x00000006) >> 1;
	$flags['read_only']         = (bool) ($rawflagint & 0x00000001);

	$flags['item_contents']     = APEcontentTypeFlagLookup($flags['item_contents_raw']);

	return $flags;
}

function APEcontentTypeFlagLookup($contenttypeid) {
	static $APEcontentTypeFlagLookup = array();
	if (empty($APEcontentTypeFlagLookup)) {
		$APEcontentTypeFlagLookup[0]  = 'utf-8';
		$APEcontentTypeFlagLookup[1]  = 'binary';
		$APEcontentTypeFlagLookup[2]  = 'external';
		$APEcontentTypeFlagLookup[3]  = 'reserved';
	}
	return (isset($APEcontentTypeFlagLookup[$contenttypeid]) ? $APEcontentTypeFlagLookup[$contenttypeid] : 'invalid');
}

function APEtagItemIsUTF8Lookup($itemkey) {
	static $APEtagItemIsUTF8Lookup = array();
	if (empty($APEtagItemIsUTF8Lookup)) {
		$APEtagItemIsUTF8Lookup[]  = 'title';
		$APEtagItemIsUTF8Lookup[]  = 'subtitle';
		$APEtagItemIsUTF8Lookup[]  = 'artist';
		$APEtagItemIsUTF8Lookup[]  = 'album';
		$APEtagItemIsUTF8Lookup[]  = 'debut album';
		$APEtagItemIsUTF8Lookup[]  = 'publisher';
		$APEtagItemIsUTF8Lookup[]  = 'conductor';
		$APEtagItemIsUTF8Lookup[]  = 'track';
		$APEtagItemIsUTF8Lookup[]  = 'composer';
		$APEtagItemIsUTF8Lookup[]  = 'comment';
		$APEtagItemIsUTF8Lookup[]  = 'copyright';
		$APEtagItemIsUTF8Lookup[]  = 'publicationright';
		$APEtagItemIsUTF8Lookup[]  = 'file';
		$APEtagItemIsUTF8Lookup[]  = 'year';
		$APEtagItemIsUTF8Lookup[]  = 'record date';
		$APEtagItemIsUTF8Lookup[]  = 'record location';
		$APEtagItemIsUTF8Lookup[]  = 'genre';
		$APEtagItemIsUTF8Lookup[]  = 'media';
		$APEtagItemIsUTF8Lookup[]  = 'related';
		$APEtagItemIsUTF8Lookup[]  = 'isrc';
		$APEtagItemIsUTF8Lookup[]  = 'abstract';
		$APEtagItemIsUTF8Lookup[]  = 'language';
		$APEtagItemIsUTF8Lookup[]  = 'bibliography';
	}
	return in_array(strtolower($itemkey), $APEtagItemIsUTF8Lookup);
}




function WriteAPEtag($filename, $data, $writeid3v1=false, $dataisUTF8=false) {
	// $filename is the filename to write the APE tag to

	// $data is a 2-dimensional array of values:
	//   $data['artist'][0] = 'Artist 1';
	//   $data['artist'][1] = 'Artist 2';
	//   $data['title'][0]  = 'Song Title';

	// $writeid3v1 is a boolean value that specifies if a blank ID3v1 tag
	// is added to the end of the file. Existing ID3v1 tags are not added
	// or removed, regardless of this setting

	// NOTE: All data passed to this function must be either ISO-8859-1 or
	// UTF-8 format, not Unicode.


	if ($APEtag = GenerateAPEtag($data, $dataisUTF8)) {
		if ($fp = @fopen($filename, 'a+b')) {
			$oldignoreuserabort = ignore_user_abort(true);
			flock($fp, LOCK_EX);

			$APEwritingOffset = 0;

			fseek($fp, -128, SEEK_END);
			$ID3v1 = fread($fp, 128);
			if (substr($ID3v1, 0, 3) == 'TAG') {

				// existing ID3v1 tag
				$APEwritingOffset -= 128;

			} else {

				// no ID3v1 tag found
				if ($writeid3v1) {
					$ID3v1 = 'TAG'.str_repeat(chr(0), 124).chr(255);
				} else {
					unset($ID3v1);
				}

			}

			fseek($fp, $APEwritingOffset - 32, SEEK_END);
			$APEfooterTest = fread($fp, 32);
			if (substr($APEfooterTest, 0, 8) == 'APETAGEX') {

				// existing APE tag found
				$OldAPEversion = LittleEndian2Int(substr($APEfooterTest,  8, 4)) / 1000;
				$OldAPEsize    = LittleEndian2Int(substr($APEfooterTest, 12, 4));
				switch ($OldAPEversion) {
					case 1:
						$APEwritingOffset -= $OldAPEsize;
						break;

					case 2:
						fseek($fp, $APEwritingOffset - $OldAPEsize - 32, SEEK_END);
						$APEheaderTest = fread($fp, 32);
						if (substr($APEheaderTest, 0, 8) == 'APETAGEX') {

							// APE header present
							$APEwritingOffset -= ($OldAPEsize + 32);

						} else {

							// no APE header present
							$APEwritingOffset -= $OldAPEsize;

						}
						break;

					default:
						flock($fp, LOCK_UN);
						fclose($fp);
						ignore_user_abort($oldignoreuserabort);
						return false;
						break;
				}

			} else {

				// no existing APE tag found
				// nothing further to do

			}
			fseek($fp, $APEwritingOffset, SEEK_END);
			ftruncate($fp, ftell($fp));
			fwrite($fp, $APEtag, strlen($APEtag));
			if (!empty($ID3v1)) {
				fwrite($fp, $ID3v1, 128);
			}
			flock($fp, LOCK_UN);
			fclose($fp);
			ignore_user_abort($oldignoreuserabort);
			return true;

		}
		return false;
	}
	return false;
}

function DeleteAPEtag($filename) {
	if ($fp = @fopen($filename, 'a+b')) {
		$oldignoreuserabort = ignore_user_abort(true);
		flock($fp, LOCK_EX);

		$APEwritingOffset = 0;

		fseek($fp, -128, SEEK_END);
		$ID3v1 = fread($fp, 128);
		if (substr($ID3v1, 0, 3) == 'TAG') {

			// existing ID3v1 tag
			$APEwritingOffset -= 128;

		} else {

			// no ID3v1 tag found
			if ($writeid3v1) {
				$ID3v1 = 'TAG'.str_repeat(chr(0), 124).chr(255);
			} else {
				unset($ID3v1);
			}

		}

		fseek($fp, $APEwritingOffset - 32, SEEK_END);
		$APEfooterTest = fread($fp, 32);
		if (substr($APEfooterTest, 0, 8) == 'APETAGEX') {

			// existing APE tag found
			$OldAPEversion = LittleEndian2Int(substr($APEfooterTest,  8, 4)) / 1000;
			$OldAPEsize    = LittleEndian2Int(substr($APEfooterTest, 12, 4));
			switch ($OldAPEversion) {
				case 1:
					$APEwritingOffset -= $OldAPEsize;
					break;

				case 2:
					fseek($fp, $APEwritingOffset - $OldAPEsize - 32, SEEK_END);
					$APEheaderTest = fread($fp, 32);
					if (substr($APEheaderTest, 0, 8) == 'APETAGEX') {

						// APE header present
						$APEwritingOffset -= ($OldAPEsize + 32);

					} else {

						// no APE header present
						$APEwritingOffset -= $OldAPEsize;

					}
					break;

				default:
					flock($fp, LOCK_UN);
					fclose($fp);
					ignore_user_abort($oldignoreuserabort);
					return false;
					break;
			}

		} else {

			// no existing APE tag found
			// nothing further to do

		}
		fseek($fp, $APEwritingOffset, SEEK_END);
		ftruncate($fp, ftell($fp));
		if (!empty($ID3v1)) {
			fwrite($fp, $ID3v1, 128);
		}
		flock($fp, LOCK_UN);
		fclose($fp);
		ignore_user_abort($oldignoreuserabort);
		return true;

	}
	return false;
}

function GenerateAPEtag($data, $dataisUTF8=false) {
	// NOTE: All data passed to this function must be either ISO-8859-1 or
	// UTF-8 format, not Unicode.

	$items = array();
	if (!is_array($data)) {
		return false;
	}
	foreach ($data as $key => $arrayofvalues) {
		if (!is_array($arrayofvalues)) {
			return false;
		}

		$valuestring = '';
		foreach ($arrayofvalues as $value) {
			$valuestring .= str_replace(chr(0), '', $value).chr(0);
		}
		$valuestring = rtrim($valuestring, chr(0));
		if (!$dataisUTF8) {
			$valuestring = utf8_encode($valuestring);
		}

		// Length of the assigned value in bytes
		$tagitem  = LittleEndian2String(strlen($valuestring), 4);

		//$tagitem .= GenerateAPEtagFlags(true, true, false, 0, false);
		$tagitem .= "\x00\x00\x00\x00";

		$tagitem .= CleanAPEtagItemKey($key)."\x00";
		$tagitem .= $valuestring;

		$items[] = $tagitem;

	}

	return GenerateAPEtagHeaderFooter($items, true).implode('', $items).GenerateAPEtagHeaderFooter($items, false);
}

function GenerateAPEtagHeaderFooter(&$items, $isheader=false) {
	$tagdatalength = 0;
	foreach ($items as $itemdata) {
		$tagdatalength += strlen($itemdata);
	}

	$APEheader  = 'APETAGEX';
	$APEheader .= LittleEndian2String(2000, 4);
	$APEheader .= LittleEndian2String(32 + $tagdatalength, 4);
	$APEheader .= LittleEndian2String(count($items), 4);
	$APEheader .= GenerateAPEtagFlags(true, true, $isheader, 0, false);
	$APEheader .= str_repeat(chr(0), 8);

	return $APEheader;
}

function GenerateAPEtagFlags($header=true, $footer=true, $isheader=false, $encodingid=0, $readonly=false) {
	$APEtagFlags = array_fill(0, 4, 0);
	if ($header) {
		$APEtagFlags[0] |= 0x80; // Tag contains a header
	}
	if (!$footer) {
		$APEtagFlags[0] |= 0x40; // Tag contains no footer
	}
	if ($isheader) {
		$APEtagFlags[0] |= 0x20; // This is the header, not the footer
	}

	// 0: Item contains text information coded in UTF-8
	// 1: Item contains binary information °)
	// 2: Item is a locator of external stored information °°)
	// 3: reserved
	$APEtagFlags[3] |= ($encodingid << 1);

	if ($readonly) {
		$APEtagFlags[3] |= 0x01; // Tag or Item is Read Only
	}

	return chr($APEtagFlags[3]).chr($APEtagFlags[2]).chr($APEtagFlags[1]).chr($APEtagFlags[0]);
}

function CleanAPEtagItemKey($itemkey) {
	$itemkey = eregi_replace("[^\x20-\x7E]", '', $itemkey);

	// http://www.personal.uni-jena.de/~pfk/mpp/sv8/apekey.html
	switch (strtoupper($itemkey)) {
		case 'EAN/UPC':
		case 'ISBN':
		case 'LC':
		case 'ISRC':
			$itemkey = strtoupper($itemkey);
			break;

		default:
			$itemkey = ucwords($itemkey);
			break;
	}
	return $itemkey;

}


?>