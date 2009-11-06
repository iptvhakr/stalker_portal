<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.rar.php - part of getID3()                           //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getRARHeaderFilepointer(&$fd, &$ThisFileInfo) {

	$ThisFileInfo['fileformat'] = 'rar';

	$ThisFileInfo['error'] .= "\n".'RAR parsing not enabled in this version of getID3()';
	return false;

}

?>