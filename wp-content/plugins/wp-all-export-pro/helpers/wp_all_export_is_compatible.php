<?php
function wp_all_export_is_compatible(){
	return ( class_exists('PMXI_Plugin') and PMXI_EDITION == 'paid' and version_compare(PMXI_VERSION, '4.1.4') >= 0 ) ? true : false;
}