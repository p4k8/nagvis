<?php
/*****************************************************************************
 *
 * WuiShapeManagement.php - Class for managing shapes in WUI
 *
 * Copyright (c) 2004-2008 NagVis Project (Contact: lars@vertical-visions.de)
 *
 * License:
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 *****************************************************************************/
 
/**
 * @author	Lars Michelsen <lars@vertical-visions.de>
 */
class WuiShapeManagement extends WuiPage {
	var $CORE;
	var $ADDFORM;
	var $DELFORM;
	var $propCount;
	
	/**
	* Class Constructor
	*
	* @param  GlobalMainCfg $CORE
	* @author Lars Michelsen <lars@vertical-visions.de>
	*/
	function WuiShapeManagement($CORE) {
		$this->CORE = $CORE;
		
		$this->propCount = 0;
		
		$prop = Array('title' => $this->CORE->getMainCfg()->getValue('internal', 'title'),
								'jsIncludes'=>Array($CORE->getMainCfg()->getValue('paths', 'htmlbase').'/frontend/wui/js/ShapeManagement.js'),
								'extHeader'=> '',
								'allowedUsers' => $this->CORE->getMainCfg()->getValue('wui','allowedforconfig'),
								'languageRoot' => 'nagvis');
		parent::__construct($CORE, $prop);
	}
	
	/**
	* If enabled, the form is added to the page
	*
	* @author Lars Michelsen <lars@vertical-visions.de>
	*/
	function getForm() {
		$code = '';
		$code .= $this->getJsIncludes();
		
		$this->ADDFORM = new WuiForm(Array('name'=>'shape_add',
																						'id'=>'shape_add',
																						'method'=>'POST',
																						'action'=>'./form_handler.php?myaction=mgt_shape_add',
																						'onSubmit'=>'return check_image_add();',
																						'enctype'=>'multipart/form-data',
																						'cols'=>'2'));
		
		$code .= $this->ADDFORM->initForm();
		$code .= $this->ADDFORM->getCatLine($this->CORE->getLang()->getText('uploadShape'));
		$code .= $this->getAddFields();
		$code .= $this->ADDFORM->getSubmitLine($this->CORE->getLang()->getText('upload'));
		$code .= $this->ADDFORM->closeForm();
		
		$this->DELFORM = new WuiForm(Array('name'=>'shape_delete',
																						'id'=>'shape_delete',
																						'method'=>'POST',
																						'action'=>'./form_handler.php?myaction=mgt_shape_delete',
																						'onSubmit'=>'return check_image_delete();',
																						'cols'=>'2'));
		
		$code .= $this->DELFORM->initForm();
		$code .= $this->DELFORM->getCatLine($this->CORE->getLang()->getText('deleteShape'));
		$code .= $this->getDelFields();
		$code .= $this->ADDFORM->getSubmitLine($this->CORE->getLang()->getText('delete'));
		$code .= $this->ADDFORM->closeForm();
		
		return $code;
	}
	
	/**
	* Gets new image fields
	*
	* @return Array HTML Code
	* @author  Lars Michelsen <lars@vertical-visions.de>
	*/
	function getAddFields() {
		return $this->ADDFORM->getHiddenField('MAX_FILE_SIZE','1000000')
					.$this->ADDFORM->getFileLine($this->CORE->getLang()->getText('choosePngImage'),'shape_image','');
	}
	
	/**
	* Gets delete fields
	*
	* @return Array HTML Code
	* @author  Lars Michelsen <lars@vertical-visions.de>
	*/
	function getDelFields() {
		return $this->DELFORM->getSelectLine($this->CORE->getLang()->getText('choosePngImage'),'shape_image',$this->CORE->getAvailableShapes(),'');
	}
	
	/**
	 * Deletes the given shape image
	 *
	 * @param		String	Filename of the shape image
	 * @author	Lars Michelsen <lars@vertical-visions.de>
	 */
	function deleteShape($fileName) {
		if(file_exists($this->CORE->getMainCfg()->getValue('paths', 'shape').$fileName)) {
			if(unlink($this->CORE->getMainCfg()->getValue('paths', 'shape').$fileName)) {
				// Go back to last page
				print("<script>window.history.back();</script>");
			} else {
				// Error handling
				print("ERROR: ".$this->CORE->getLang()->getText('failedToDeleteShape','IMAGE~'.$fileName));
			}
		} else {
			// Error handling
			print("ERROR: ".$this->CORE->getLang()->getText('shapeDoesNotExist','IMAGE~'.$fileName));
		}
	}
	
	/**
	 * Uploads a new shape image
	 *
	 * @param		Array	Informations of the new image
	 * @author	Lars Michelsen <lars@vertical-visions.de>
	 */
	function uploadShape(&$arrFile) {
		// check the file (the map) is properly uploaded
		if(is_uploaded_file($arrFile['tmp_name'])) {
			if(preg_match(MATCH_PNG_GIF_JPG_FILE, $arrFile['name'])) {
				if(@move_uploaded_file($arrFile['tmp_name'], $this->CORE->getMainCfg()->getValue('paths', 'shape').$arrFile['name'])) {
					// Change permissions of the file after the upload
					chmod($this->CORE->getMainCfg()->getValue('paths', 'shape').$arrFile['name'],0666);
					
					// Go back to last page
					print("<script>window.history.back();</script>");
				} else {
					// Error handling
					print("ERROR: ".$this->CORE->getLang()->getText('moveUploadedFileFailed','PATH~'.$this->CORE->getMainCfg()->getValue('paths', 'shape')));
				}
			} else {
				// Error handling
				print("ERROR: ".$this->CORE->getLang()->getText('mustBePngFile'));
			}
		} else {
			// Error handling
			print("ERROR: ".$this->CORE->getLang()->getText('uploadFailed'));
		}
	}
}