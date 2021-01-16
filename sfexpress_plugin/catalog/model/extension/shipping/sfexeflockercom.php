<?php
//catalog/model/shipping
class ModelExtensionShippingSfexeflockercom extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/sfexeflockercom');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_sfexeflockercom_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
	
		if (!$this->config->get('shipping_sfexeflockercom_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();
		
		$cart_weight = $this->cart->getWeight();
		
		$shippings_weight_data = $this->config->get('shipping_sfexeflockercom_weight_data');
		
		foreach($shippings_weight_data as $k => $s)
		{
		    if ($s['weight_from'] != 0 && $cart_weight <= $s['weight_from'])
		        unset($shippings_weight_data[$k]);
		    elseif ($s['weight_to'] != 0 && $s['weight_to'] < $cart_weight)
		        unset($shippings_weight_data[$k]);
		}
		
		$checkbox = 'sfexeflockercom.sfexeflockercom_select';
		$checkbox_js = '';
		if (isset($this->session->data['shipping_method']) && strpos($this->session->data['shipping_method']['code'], 'sfexeflockercom') !== false) {
			$checkbox = $this->session->data['shipping_method']['code'];
			$checkbox_js = $this->session->data['shipping_method']['code'];
		}
		
		$dropSelect_js = "";
		/*$dropSelect_js = "
		<script>
		var shp_methods = document.getElementsByName('shipping_method');
		for(var i = 0; i < shp_methods.length; i++){
			if(shp_methods[i].value == '".$checkbox_js."'){
				shp_methods[i].checked = true;
			}
		}
		</script>
		";	*/
		

		if ($status && count($shippings_weight_data)) {
		
			$shipping_weight_data = reset($shippings_weight_data);
			$sfexeflockercom_cost = $shipping_weight_data['cost'] + $shipping_weight_data['each_next']*($this->cart->countProducts()-1);
			
			if ( $this->config->get('shipping_sfexeflockercom_freelimit') > 0 && $this->cart->getSubtotal() > $this->config->get('shipping_sfexeflockercom_freelimit') ) {
				$sfexeflockercom_cost = 0.00;
			}
		
			$quote_data = array();	
		
			$dropSelect = '';  //$this->language->get('text_title')
			$dropSelect .= '<select name="sfexeflockercom_sel" id="sfexeflockercom_sel" style="width: 80%; display: inline;" class="form-control" ';
			$dropSelect .= 'onchange="$(\'#\'+$(this).val()+\'\').click();">'; 
			/*$dropSelect = '</label>'; 
			$dropSelect .= '<select name="sfexeflockercom_sel" id="sfexeflockercom_sel" style="width: 80%; display: inline;" class="form-control" ';
			$dropSelect .= 'onfocus="$(\'#sfexeflockercom_sel\').parent().find(\'input\').eq(0).val($(this).val()); $(\'#sfexeflockercom_sel\').parent().find(\'input\').eq(0).prop(\'checked\',true).trigger(\'change\');" ';
			$dropSelect .= 'onchange="$(\'#sfexeflockercom_sel\').parent().find(\'input\').eq(0).val($(this).val()); $(\'#sfexeflockercom_sel\').parent().find(\'input\').eq(0).prop(\'checked\',true).trigger(\'change\');">';
				*/
				//(place_id,name,city,address,country,postalcode,routingcode,availability,lat,lng,phone,sort)
			$sql = 'SELECT * FROM '.DB_PREFIX.'xx_sfexeflockercom ORDER BY city, name, address, postalcode' ;
			$query = $this->db->query($sql);
			
			$reqion_old = '';
			foreach($query->rows as $result){
				$reqion_new = $result['city'].'/'.$result['postalcode'];
				$terminal_name = $result['name'].' - '.$result['address'];
				if($reqion_new != $reqion_old && $reqion_old != '') { 
					$dropSelect .= "</optgroup>\r\n";
				} 
				if($reqion_new != $reqion_old) { 
					$dropSelect .= "<optgroup label='". $reqion_new ."'>\r\n";
				} 
				if (isset($this->session->data['shipping_method']) && $this->session->data['shipping_method']['code'] == 'sfexeflockercom.sfexeflockercom'.$result['place_id']) {
					$dropSelect .= "<option value='".'sfexeflockercom'.$result['place_id']."' selected='selected'>".$terminal_name."</option>\r\n";
				} else {
					$dropSelect .= "<option value='".'sfexeflockercom'.$result['place_id']."'>".$terminal_name."</option>\r\n";
				}
				$reqion_old = $reqion_new;
			}
			if ($reqion_old != '') { 
				$dropSelect .= "</optgroup>\r\n";
			}


			$dropSelect .= "</select>";
			//$dropSelect .= "<label> &nbsp;&nbsp;&nbsp; ";
			
			/*$quote_data['sfexeflockercom.sfexeflockercom_select'] = array(
				'code'         => $checkbox.'" onclick="$(this).val($(\'#sfexeflockercom_sel\').val());',
				'title'        => $dropSelect.'<!--',
				'cost'         => $sfexeflockercom_cost,
				'tax_class_id' => $this->config->get('shipping_sfexeflockercom_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($sfexeflockercom_cost, $this->config->get('config_currency'), $this->session->data['currency']), $this->config->get('shipping_sfexeflockercom_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
			);
			

			// Start Build Selection 
				$quote_data['sfexeflockercom.sfexeflockercom_begin'] = array(
					'code'         => 'sfexeflockercom.sfexeflockercom_begin',
					'title'        => '',
					'cost'         => $sfexeflockercom_cost,
					'tax_class_id' => $this->config->get('sfexeflockercom_tax_class_id'),
					'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($sfexeflockercom_cost, $this->config->get('config_currency'), $this->session->data['currency']), $this->config->get('shipping_sfexeflockercom_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
				);*/
				
								
				//(place_id,name,city,address,country,postalcode,routingcode,availability,lat,lng,phone,sort)
				$sql = 'SELECT * FROM '.DB_PREFIX.'xx_sfexeflockercom ORDER BY city, name, address, postalcode' ;
				$query = $this->db->query($sql);
				$rec_count = 0;
				foreach($query->rows as $result){
					
					$checkbox_style = 'none';
					$dropSelectblock = '';
					$title_content = $this->language->get('text_title').' - '.$result['name'].' - '.$result['address'].' ('.$result['city'].', '.$result['postalcode'].')';
					if($checkbox == 'sfexeflockercom.sfexeflockercom'.$result['place_id'] || $checkbox == 'sfexeflockercom.sfexeflockercom_select' && $rec_count == 0){ 
						$checkbox_style = 'block';
						$dropSelectblock = $dropSelect;
					}
					
					$quote_data['sfexeflockercom'.$result['place_id']] = array(
						'id'           => 'sfexeflockercom'.$result['place_id'],
						'show'         => $checkbox_style,
						'code'         => 'sfexeflockercom.sfexeflockercom'.$result['place_id'],
						'desc'	   	   => $dropSelectblock,
						'title'        => $title_content,
						'cost'         => $sfexeflockercom_cost,
						'tax_class_id' => $this->config->get('shipping_sfexeflockercom_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($sfexeflockercom_cost, $this->config->get('config_currency'), $this->session->data['currency']), $this->config->get('shipping_sfexeflockercom_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
					);
					$rec_count++;
				}
				
			/*	
				$quote_data['sfexeflockercom.sfexeflockercom_end'] = array(
					'code'         => 'sfexeflockercom.sfexeflockercom_end',
					'title'        => '-->'.$dropSelect_js,
					'cost'         => $sfexeflockercom_cost,
					'tax_class_id' => $this->config->get('shipping_sfexeflockercom_tax_class_id'),
					'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($sfexeflockercom_cost, $this->config->get('config_currency'), $this->session->data['currency']), $this->config->get('shipping_sfexeflockercom_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
				);*/
				// End Build Selection 
			//}

			if (isset($this->session->data['api_id'])) {
				//print_r($this->session);
				$quote_data = array();
				
				$sql = 'SELECT * FROM '.DB_PREFIX.'xx_sfexeflockercom ORDER BY city, name, address, postalcode' ;
				$query = $this->db->query($sql);
				foreach($query->rows as $result){
					$sfexeflockercom[] = $result;
					$quote_data['sfexeflockercom'.$result['place_id']] = array(
						'code'         => 'sfexeflockercom.sfexeflockercom'.$result['place_id'],
						'title'        => $this->language->get('text_title').' - '.$result['name'].' - '.$result['address'].' ('.$result['city'].', '.$result['postalcode'].')',
						'cost'         => $sfexeflockercom_cost,
						'tax_class_id' => $this->config->get('shipping_sfexeflockercom_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($sfexeflockercom_cost, $this->config->get('config_currency'), $this->session->data['currency']), $this->config->get('shipping_sfexeflockercom_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
					);
				}
			}
			
      		$method_data = array(
        		'code'       => 'sfexeflockercom',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_sfexeflockercom_sort_order'),
        		'error'      => false
      		);
		}
	
		return $method_data;
	}
}
?>