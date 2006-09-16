<?php
/******************************************************************************
*admin/index.php (Xoops-SMF bridge)                                                                    *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 1.1                                         *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2006 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/

include '../../../include/cp_header.php';
// include '../functions.php';
include '../config.php';
xoops_cp_header();
echo"<table width='100%' border='0' cellspacing='1' class='outer'>"
."<tr><td class=\"odd\">";
echo "<a href='./index.php'><h4>"._MD_A_SMFCONF."</h4></a>";
if(isset($mode)) {

}
else {
?>
<table border="0" cellpadding="4" cellspacing="1" width="100%">

<tr class='bg1' align="left">
	<td><span class='fg2'><a href="<?php echo $smfUrl['root'];?>index.php?action=admin"><?php echo _MI_ADMIN;?></a></span></td>
	<td><span class='fg2'><?php echo _MI_ADMIN_DESC;?></span></td>
</tr>

</table>
<?php
}

echo"</td></tr></table>";
xoops_cp_footer();
?>