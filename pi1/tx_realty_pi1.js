/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Saskia Metzler <saskia@merlin.owl.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This file provides a JavaScript functions for the Realty Manager.
 *
 * @package TYPO3
 * @subpackage tx_realty
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */

/**
 * Sets the current full-size image to the current thumbnail and sets the class
 * attribute of the thumbnail accordingly.
 *
 * @param string ID of the link tag that wraps the current image tag, must not
 *               be empty
 * @param string link to the full-size version of the current image, must not
 *               be empty
 */
function showFullsizeImage(id, linkToFullsizeImage) {
	var fullsizeImage = document.getElementById("tx_realty_fullsizeImage");
	var currentImage = document.getElementById(id).getElementsByTagName("img")[0];
	var thumbnails = document.getElementById("tx_realty_thumbnailTable").getElementsByTagName("a");

	fullsizeImage.removeAttribute("width");
	fullsizeImage.removeAttribute("height");
	fullsizeImage.alt = currentImage.title;
	fullsizeImage.title = currentImage.title;
	fullsizeImage.src = linkToFullsizeImage;
	for (i = 0; i < (thumbnails.length); i++) {
		thumbnails[i].className = "tx-realty-pi1-thumbnail";
	};
	document.getElementById(id).className += "-current";
	document.getElementById("tx_realty_fullsizeImageCaption").firstChild.nodeValue = currentImage.title;
}

/**
 * Marks the current attachment as deleted if the confirm becomes submitted.
 *
 * @param string ID of the list item with the attachment to delete, must not be
 *               empty
 * @param string localized confirm message for whether really to mark an
 *               attachment for deletion
 */
function markAttachmentAsDeleted(listItemId, confirmMessage) {
	var listItem = document.getElementById(listItemId);
	var fileNameDiv = listItem.getElementsByTagName("span")[0];
	var deleteButton = listItem.getElementsByTagName("input")[0];

	if (confirm(confirmMessage)) {
		document.getElementById("tx_realty_frontEndImageUpload_imagesToDelete").value
			+= "," + listItemId;
		fileNameDiv.setAttribute("class", "deleted");
		deleteButton.className += " deleted";
		deleteButton.disabled = true;
	}
}

/**
 * In the front-end editor, hides/shows fields depending on whether currently
 * "rent" or "sale" is selected for the edited object.
 */
function updateHideAndShow() {
	if ($("tx_realty_frontEndEditor_object_type_item").checked) {
		$$(".rent").invoke("show");
		$$(".sale").invoke("hide");
	} else {
		$$(".rent").invoke("hide");
		$$(".sale").invoke("show");
	}

	if ($("tx_realty_frontEndEditor_contact_data_source_item").checked) {
		$("contact-data").show();
	} else {
		$("contact-data").hide();
	}

	if ($("tx_realty_frontEndEditor_show_address").checked) {
		$$(".exact-address").invoke("show");
		$$(".rough-address").invoke("hide");
	} else {
		$$(".exact-address").invoke("hide");
		$$(".rough-address").invoke("show");
	}
}
