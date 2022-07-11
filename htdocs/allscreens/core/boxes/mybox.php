<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		core/boxes/mybox.php
 * 	\ingroup	allscreens
 * 	\brief		This file is a sample box definition file
 * 				Put some comments here
 */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class mybox extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "mybox";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "thumb@allscreens";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('allscreens');

	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param='')
	{
		global $langs;
		$langs->load("boxes");

		$this->boxlabel = $langs->transnoentitiesnoconv("Box module AllScreens");

		$this->db = $db;
		$this->param = $param;
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $langs;

		// Use configuration value for max lines count
		$this->max = $max;

		//include_once DOL_DOCUMENT_ROOT . "/allscreens/class/allscreens.class.php";

		// Populate the head at runtime
		$text = $langs->trans("Module AllScreens", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => 'http://msmobile.fr',
			// Sublink icon placed after the text
			'subpicto' => 'object_thumb@allscreens',
			// Sublink icon HTML alt text
			'subtext' => '',
			// Sublink HTML target
			'target' => '',
			// HTML class attached to the picto and link
			'subclass' => 'center',
			// Limit and truncate with "…" the displayed text lenght, 0 = disabled
			'limit' => 0,
			// Adds translated " (Graph)" to a hidden form value's input (?)
			'graph' => false
		);

		// Populate the contents at runtime
		$this->info_box_contents = array(
			0 => array( // First line
				0 => array( // First Column
					//  HTML properties of the TR element. Only available on the first column.
					'tr'           => 'align="left" class="box_impair"',
					// HTML properties of the TD element
					'td'           => '',
					// Fist line logo
					'logo'         => 'logo@allscreens',
					// Main text
					'text'         => 'MS Mobile',
					// Secondary text
					//'text2'        => '<p><strong>Another text</strong></p>',
					// Unformatted text, usefull to load javascript elements
					'textnoformat' => '',
					// Link on 'text' and 'logo' elements
					'url'          => 'http://msmobile.fr',
					// Link's target HTML property
					'target'       => '_blank',
					// Truncates 'text' element to the specified character length, 0 = disabled
					'maxlength'    => 0,
					// Prevents HTML cleaning (and truncation)
					'asis'         => false,
					// Same for 'text2'
					'asis2'        => true
				),
				//1 => array( // Another column
					// No TR for n≠0
					//'td'   => '',
					//'text' => '',
				//)
			),
			1 => array( // Another line
				0 => array( // TR
					'tr'   => 'align="left" class="box_impair"',
					'text' => 'Ce module permet d\'afficher Dolibarr en mode responsive.'
				)
			),
			2 => array( // Another line
				0 => array( // TR
					'tr'   => 'align="left" class="box_impair"',
					'text' => 'Fonctionne sur toutes tailles d\'écran.'
				)
			),
		);
	}

	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head Array with properties of box title
	 * @param array $contents Array with properties of box lines
	 * @return void
	 */
	public function showBox($head = null, $contents = null)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}
