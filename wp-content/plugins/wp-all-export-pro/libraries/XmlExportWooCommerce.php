<?php

if ( ! class_exists('XmlExportWooCommerce') ){

	final class XmlExportWooCommerce
	{

		/**
		 * Singletone instance
		 * @var XmlExportWooCommerce
		 */
		protected static $instance;

		/**
		 * Return singletone instance
		 * @return XmlExportWooCommerce
		 */
		static public function getInstance() {
			if (self::$instance == NULL) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		private $_woo_data = array();

		private $_existing_attributes = array();					

		public static $is_active = true;

		public function __construct(){			

			if ( ! class_exists('WooCommerce') 
				or ( XmlExportEngine::$exportOptions['export_type'] == 'specific' and ! in_array('product', XmlExportEngine::$post_types) ) 
					or ( XmlExportEngine::$exportOptions['export_type'] == 'advanced' and strpos(XmlExportEngine::$exportOptions['wp_query'], 'product') === false ) ) {
				self::$is_active = false;
				return;			
			}

			$this->_woo_data = array(
				'_visibility', '_stock_status', '_downloadable', '_virtual', '_regular_price', '_sale_price', '_purchase_note', '_featured', '_weight', '_length',
				'_width', '_height', '_sku', '_sale_price_dates_from', '_sale_price_dates_to', '_price', '_sold_individually', '_manage_stock', '_stock', '_upsell_ids', '_crosssell_ids',
				'_downloadable_files', '_download_limit', '_download_expiry', '_download_type', '_product_url', '_button_text', '_backorders', '_tax_status', '_tax_class', '_product_image_gallery', '_default_attributes',
				'total_sales', '_product_attributes'
			);						

		}

		public function init( & $existing_meta_keys = array() ){

			if ( ! self::$is_active ) return;

			$hide_fields = array('_edit_lock', '_edit_last');		

			if ( ! empty($existing_meta_keys) ){
				foreach ($existing_meta_keys as $key => $record_meta_key) {
					
					if ( in_array($record_meta_key, $this->_woo_data) ) unset($existing_meta_keys[$key]);

					if ( strpos($record_meta_key, 'attribute_pa_') === 0 || strpos($record_meta_key, '_min_') === 0 || strpos($record_meta_key, '_max_') === 0){
						if ( ! in_array($record_meta_key, $this->_woo_data)){
							$this->_woo_data[] = $record_meta_key;
							unset($existing_meta_keys[$key]);
						}
											
					}
				}
			}			

			global $wp_taxonomies;	

			foreach ($wp_taxonomies as $key => $obj) {	if (in_array($obj->name, array('nav_menu'))) continue;

				if (strpos($obj->name, "pa_") === 0 and strlen($obj->name) > 3)
					$this->_existing_attributes[] = $obj->name;															
			}

		}

		public function render( & $i ){			
			
			if ( self::$is_active and ! empty($this->_woo_data) ){
				?>										
				<p class="wpae-available-fields-group"><?php _e("WooCommerce", "wp_all_export_plugin"); ?><span class="wpae-expander">+</span></p>
				<div class="wpae-custom-field">
					<ul>
						<li>
							<div class="default_column" rel="">
								<a href="javascript:void(0);" class="pmxe_remove_column">X</a>
								<label class="wpallexport-element-label"><?php _e("All WooCommerce Data", "wp_all_export_plugin"); ?></label>															
								<input type="hidden" name="rules[]" value="pmxe_woo"/>															
							</div>
						</li>
						<?php
						foreach ($this->_woo_data as $cur_meta_key) {		
							if ( strpos($cur_meta_key, 'attribute_pa_') === 0 ) continue;																											
							?>
							<li class="pmxe_woo">
								<div class="custom_column" rel="<?php echo ($i + 1);?>">
									<label class="wpallexport-xml-element">&lt;<?php echo $cur_meta_key; ?>&gt;</label>
									<input type="hidden" name="ids[]" value="1"/>
									<input type="hidden" name="cc_label[]" value="<?php echo $cur_meta_key; ?>"/>										
									<input type="hidden" name="cc_php[]" value=""/>										
									<input type="hidden" name="cc_code[]" value=""/>
									<input type="hidden" name="cc_sql[]" value=""/>
									<input type="hidden" name="cc_options[]" value=""/>										
									<input type="hidden" name="cc_type[]" value="woo"/>										
									<input type="hidden" name="cc_value[]" value="<?php echo $cur_meta_key; ?>"/>
									<input type="hidden" name="cc_name[]" value="<?php echo str_replace(" ", "_", $cur_meta_key);?>"/>
								</div>
							</li>
							<?php
							$i++;												
						}		
						if ( ! empty($this->_existing_attributes) ){									
							foreach ($this->_existing_attributes as $key => $tx_name) {
								?>
								<li class="pmxe_woo">
									<div class="custom_column" rel="<?php echo ($i + 1);?>">														
										<label class="wpallexport-xml-element">&lt;<?php echo $tx_name; ?>&gt;</label>
										<input type="hidden" name="ids[]" value="1"/>
										<input type="hidden" name="cc_label[]" value="<?php echo $tx_name; ?>"/>			
										<input type="hidden" name="cc_php[]" value=""/>
										<input type="hidden" name="cc_code[]" value=""/>	
										<input type="hidden" name="cc_sql[]" value=""/>		
										<input type="hidden" name="cc_options[]" value=""/>
										<input type="hidden" name="cc_type[]" value="attr"/>
										<input type="hidden" name="cc_value[]" value="<?php echo $tx_name; ?>"/>
										<input type="hidden" name="cc_name[]" value="<?php echo str_replace(" ", "_", $tx_name);?>"/>
									</div>
								</li>
								<?php
								$i++;
							}
						}
						?>
					</ul>
				</div>									
				<?php
			}
		}

		public function render_filters(){
			if ( self::$is_active and ! empty($this->_woo_data) ){
				?>										
				<optgroup label="WooCommerce Data">
					<?php
						foreach ($this->_woo_data as $cur_meta_key) {		
							if ( strpos($cur_meta_key, 'attribute_pa_') === 0 ) continue;																											
							?>
							<option value="<?php echo 'cf_' . $cur_meta_key?>"><?php echo $cur_meta_key?></option>
							<?php
						}
					?>
				</optgroup>	
				<?php
				if ( ! empty($this->_existing_attributes) ){	
					?>								
					<optgroup label="WooCommerce Taxonomies">
					<?php
					foreach ($this->_existing_attributes as $key => $tx_name) {
						?>
						<option value="<?php echo 'tx_' . $tx_name; ?>"><?php echo $tx_name; ?></option>
						<?php
					}
					?>
					</optgroup>	
					<?php
				}
			}
		}

		/**
	     * __get function.
	     *
	     * @access public
	     * @param mixed $key
	     * @return mixed
	     */
	    public function __get( $key ) {
	        return $this->get( $key );
	    }	

	    /**
	     * Get a session variable
	     *
	     * @param string $key
	     * @param  mixed $default used if the session variable isn't set
	     * @return mixed value of session variable
	     */
	    public function get( $key, $default = null ) {        
	        return isset( $this->{$key} ) ? $this->{$key} : $default;
	    }
	}
}
