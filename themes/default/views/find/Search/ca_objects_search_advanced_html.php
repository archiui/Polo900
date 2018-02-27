<?php
/* ----------------------------------------------------------------------
 * themes/default/views/find/ca_objects_search_html.php 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2008-2012 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
 	 
	$vo_result = $this->getVar('result');
	$t_display = $this->getVar('t_display');
	if (!is_array($va_display_list = $this->getVar('display_list'))) { $va_display_list = array(); }
	$va_initial_data = array();
	$va_row_headers = array();
	
	$vn_item_count = 0;
		
	if ($vo_result) {
		$vs_pk = $vo_result->primaryKey();
	
		while(($vn_item_count < 100) && $vo_result->nextHit()) {
			$va_result = array('item_id' => $vn_id = $vo_result->get($vs_pk));

			foreach($va_display_list as $vn_placement_id => $va_bundle_info) {
				$va_result[str_replace(".", "-", $va_bundle_info['bundle_name'])] = $t_display->getDisplayValue($vo_result, $vn_placement_id, array('request' => $this->request));
			}
			$va_initial_data[] = $va_result;
			$vn_item_count++;
			$va_row_headers[] = ($vn_item_count)." ".caEditorLink($this->request, caNavIcon($this->request, __CA_NAV_BUTTON_EDIT__), 'caResultsEditorEditLink', $this->ops_tablename, $vn_id);
		}
	}
	
	$this->setVar('initialData', $va_initial_data);
	$this->setVar('rowHeaders', $va_row_headers);
	
 	print $this->render('Search/search_advanced_controls_html.php');
 ?>
	<div id="quickLookOverlay"> 
		<div id="quickLookOverlayContent">
		
		</div>
	</div>
	
 	<div id="resultBox">
<?php
	if($vo_result) {
		$vs_view = $this->getVar('current_view');
		if ($vo_result->numHits() == 0) { $vs_view = 'no_results'; }
		if ($vs_view == 'editable') { $this->setVar('dontShowPages', true); }
		print $this->render('Results/paging_controls_html.php');
		print $this->render('Results/search_options_html.php');
?>

	<div class="sectionBox">
<?php
		switch($vs_view) {
			case 'full':
				print $this->render('Results/ca_objects_results_full_html.php');
				break;
			case 'list':
				print $this->render('Results/ca_objects_results_list_html.php');
				break;
			case 'editable':
				print $this->render('Results/ca_objects_results_editable_html.php');
				break;
			case 'no_results':
				print $this->render('Results/no_results_html.php');
				break;
			default:
				print $this->render('Results/ca_objects_results_thumbnail_html.php');
				break;
		}
?>		
	</div><!-- end sectionbox -->
<?php
		if ($vs_view != 'map') { print $this->render('Results/paging_controls_minimal_html.php'); }
	}
?>
</div><!-- end resultbox -->
