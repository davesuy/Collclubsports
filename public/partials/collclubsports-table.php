<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CollClubSports
 * @subpackage CollClubSports/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CollClubSports
 * @subpackage CollClubSports/public
 * @author     Bretch Guire Garcinez <bgarcinez@gmail.com>
 */
class CollClubSports_Table {

	private $columns;

	private $items;
	/**
	 * Initialize the class and set its properties.
	 *
		params:
			options = {
				noItemLabel: 'No Stats Available',
				pageSize: 20 // number of rows per page
			},
	 */
	public function __construct($columns = null, $items = null, $total = null, $defaultsort = null, $defaultsorttype = null, $sort = null, $sorttype = null,  $options = null) {
		$this->columns = $columns;
		$this->defaultsort = $defaultsort;
        $this->sort = $sort;
		$this->sorttype = $sorttype;
		$this->defaultsorttype = $defaultsorttype;
		$this->options = $options;
		if (!empty($this->defaultsort) && !empty($this->sort)) {
			$arr = array();
			foreach($items as $k => $v) {
			    $arr[$this->sort][$k] = $v[$this->sort];
			    $arr[$this->defaultsort][$k] = $v[$this->defaultsort];
			}
			$sorttype = $this->sorttype == 'desc' ? SORT_DESC : SORT_ASC;
			$defaultsorttype = $this->defaultsorttype == 'desc' ? SORT_DESC : SORT_ASC;
			if (count($arr) > 0) {
				array_multisort(
					$arr[$this->sort],
					$sorttype,
					$arr[$this->defaultsort],
					$defaultsorttype,
					$items);
			}
		} else if(!empty($this->defaultsort)) {
			usort($items, function($a, $b) {
				if($this->defaultsorttype == 'desc') {
					return $a[$this->defaultsort] > $b[$this->defaultsort] ? -1 : 1;
				} else {
					return $a[$this->defaultsort] > $b[$this->defaultsort] ? 1 : -1;
				}
			});
		} else if (!empty($this->sort)) {
			usort($items, function($a, $b) {
				if($this->sorttype == 'desc') {
					return $a[$this->sort] > $b[$this->sort] ? -1 : 1;
				} else {
					return $a[$this->sort] > $b[$this->sort] ? 1 : -1;
				}
			});
		}
		$this->total = $total;
		$this->items = $items;
		$this->buildTable();
	}

	private function noItemsAvailable() {
		$tpl = "<tr><td class='no-items-wrapper' colspan='{0}'>{1}</tr>";
		$tpl = str_replace('{0}', count($this->columns), $tpl);
		if(!empty($this->options) && 
			!empty($this->options['noItemLabel'])) {
			$tpl = str_replace('{1}', $this->options['noItemLabel'], $tpl);
		} else {
			$tpl = str_replace('{1}', 'No items available', $tpl);
		}
		echo $tpl;
	}

	private function buildTable() { 
		?>
		<table class="collclubsports-component table-reponsive stats-table">
			<thead>
				<tr>
 					<?php for($i = 0; $i < sizeof($this->columns);$i++){
						$column = $this->columns[$i];
						$disablesort = false;
						$disablesort = $this->options['disablesort'];
						if(!empty($column['headerlink']) && !$disablesort) {
							echo $this->generateTh($column['label'], $column['headerlink'], $column['sort'], $column['sorttype'], $column['thclass'], $column['tooltip']);
						} else {
							echo $this->generateTh($column['label'], null, $column['sort'], $column['sorttype'], $column['thclass'], $column['tooltip']);
						}
					}?>
				</tr>
			</thead>
			<?php 
				$currentPage = 1;
				$sizePerPage = ( !empty($this->options) && !empty($this->options['pageSize']) ) ? $this->options['pageSize'] : 20;
				$currentIndex = 0;
				do {
					if($currentPage == 1) {
						echo '<tbody class="collclubsports-component active" name="page-' . $currentPage .'">';
					} else {
						echo '<tbody class="collclubsports-component" name="page-' . $currentPage .'">';
					}
					
					while( $currentIndex < ($sizePerPage * $currentPage) && 
							$currentIndex < sizeof($this->items)) {
						$item = $this->items[$currentIndex];
						echo '<tr>';
						for($j = 0; $j < sizeof($this->columns);$j++){
							$column = $this->columns[$j];
							if(!empty($column['keylink'])) {
								echo $this->generateTd($item[$column['key']], $item[$column['keylink']]);
							} else {
								echo $this->generateTd($item[$column['key']]);
							}
						}
						$currentIndex++;
						echo '</tr>';
					}

					if(count($this->items) == 0) {
						$this->noItemsAvailable();
					}
					else if(!empty($this->total) && $currentIndex >= sizeof($this->items)) {
						echo '<tr>';
						for($i = 0; $i < sizeof($this->columns);$i++){
							$column = $this->columns[$i];
							echo $this->generateTd($this->total[$column['key']]);
						}
						echo '</tr>';
					}
					echo '</tbody>';
					$currentPage++;
				} while($currentIndex < sizeof($this->items));
			?>
		</table>
		<?php if($currentPage > 2) {?>
			<div class="collclubsports-component pagination-wrapper">
				<div class="button accent previous-button btn-disabled">
					<div class="back-icon"></div>
				</div>
				<div class="button accent next-button">
					<div class="next-icon"></div>
				</div>
			</div>
		<?php } ?>
	<?php  
	}

	private function generateTd($label = null, $link = null) {
		if(!empty($link)) {
			$td = '<td><a href="{0}">{1}</a></td>';
			$td = str_replace('{0}', $link, $td);
			$td = str_replace('{1}', $label, $td);
		} else {
			$td = '<td>{0}</td>';
			$td = str_replace('{0}', $label, $td);
		}
		return $td;
	}

	private function generateTh($label = null, $link = null, $sort = null, $sorttype = null, $class = null, $tooltip = null) {
		if($sort) {
			if(!empty($link)) {
				$th = '<th class="sorter-wrapper {3}"><a href="{0}" {4}>{1}</a>{2}';
				$th = str_replace('{0}', $link, $th);
				$th = str_replace('{1}', $label, $th);
			} else {
				$th = '<th class="sorter-wrapper {3}">{1}';
				$th = str_replace('{0}', $link, $th);
				$th = str_replace('{1}', $label, $th);
			}

			if($sorttype == 'desc' ) {
				$th = str_replace('{2}', '<div class="arrow-down"/>', $th);
			} else {
				$th = str_replace('{2}', '<div class="arrow-up"/>', $th);
			}
		}
		else {
			if(!empty($link)) {
				$th = '<th class="{3}"><a href="{0}" {4}>{1}</a>';
				$th = str_replace('{0}', $link, $th);
				$th = str_replace('{1}', $label, $th);

			} else {
				$th = '<th class="{3}"><span {4}>{0}</span>';
				$th = str_replace('{0}', $label, $th);
			}
		}

		if(!empty($class)) {
			$th = str_replace('{3}', $class, $th);
		} else {
			$th = str_replace('{3}', '', $th);
		}

		if(!empty($tooltip)) {
			$tp = 'data-toggle="tooltip" data-placement="top" title="{0}"';
			$tp = str_replace('{0}', $tooltip, $tp);
			$th = str_replace('{4}', $tp, $th);
		} else {
			$th = str_replace('{4}', '', $th);
		}
		
		$th = $th . '</th>';
		return $th;
	}
} ?>