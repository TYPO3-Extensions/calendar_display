<?php

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_CalendarDisplay_ViewHelpers_Widget_Controller_PaginateController extends Tx_Fluid_Core_Widget_AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $configuration = array('itemsPerPage' => 10, 'insertAbove' => FALSE, 'insertBelow' => TRUE);

	/**
	 * @var Tx_Extbase_Persistence_QueryResultInterface
	 */
	protected $objects;

	/**
	 * @var integer
	 */
	protected $currentPage = 1;

	/**
	 * @var integer
	 */
	protected $numberOfPages = 1;
	
	/**
	 * @var integer
	 */
	protected $numberOfRecords = 1;
	
	/**
	 * @var integer
	 */
	protected $itemsPerPage = 10;

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->objects = $this->widgetConfiguration['objects'];
		$this->configuration = t3lib_div::array_merge_recursive_overrule($this->configuration, $this->widgetConfiguration['configuration'], TRUE);
		$this->numberOfRecords = count($this->objects);
	}

	/**
	 * @param integer $currentPage
	 * @param integer $itemsPerPage
	 * @return void
	 */
	public function indexAction($currentPage = 1, $itemsPerPage = NULL) {
		$this->itemsPerPage = $itemsPerPage ? (integer)$itemsPerPage : (integer)$this->configuration['itemsPerPage'];
		$this->numberOfPages = ceil(count($this->objects) / $this->itemsPerPage);
		// set current page
		$this->currentPage = (integer)$currentPage;
		if ($this->currentPage < 1) {
			$this->currentPage = 1;
		} elseif ($this->currentPage > $this->numberOfPages) {
			$this->currentPage = $this->numberOfPages;
		}

		// modify query
		$query = $this->objects->getQuery();
		$query->setLimit($this->itemsPerPage);
		if ($this->currentPage > 1) {
			$query->setOffset((integer)($this->itemsPerPage * ($this->currentPage - 1)));
		}
		$modifiedObjects = $query->execute();

		$this->view->assign('contentArguments', array(
			$this->widgetConfiguration['as'] => $modifiedObjects
		));
		$this->view->assign('configuration', $this->configuration);
		$this->view->assign('pagination', $this->buildPagination());
	}

	/**
	 * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
	 *
	 * @return array
	 */
	protected function buildPagination() {
		$startRecord = ($this->currentPage - 1) * $this->itemsPerPage + 1;
		$endtRecord = $this->currentPage * $this->itemsPerPage;
		if ($endtRecord > $this->numberOfRecords) {
			$endtRecord = $this->numberOfRecords;
		}
		$pages = array();
		for ($i = 1; $i <= $this->numberOfPages; $i++) {
			$pages[] = array('number' => $i, 'isCurrent' => ($i === $this->currentPage));
		}
		$pagination = array(
			'pages' => $pages,
			'current' => $this->currentPage,
			'numberOfPages' =>  $this->numberOfPages,
			'numberOfRecords' => $this->numberOfRecords,
			'currentRecords' => $startRecord . '-' . $endtRecord,
			'itemsPerPage' => $this->itemsPerPage,
		);
		if ($this->currentPage < $this->numberOfPages) {
			$pagination['nextPage'] = $this->currentPage + 1;
		}
		if ($this->currentPage > 1) {
			$pagination['previousPage'] = $this->currentPage - 1;
		}
		return $pagination;
	}
}

?>