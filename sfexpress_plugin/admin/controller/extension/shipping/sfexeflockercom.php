<?php
//admin/controller/shipping 
class ControllerExtensionShippingSfexeflockercom extends Controller {
	private $error = array(); 
	
	public function install() {
		$this->db->query("
		
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xx_sfexeflockercom` (
			  `place_id` varchar(25) NOT NULL,
			  `name` varchar(50) NOT NULL,
			  `city` varchar(25) NOT NULL,
			  `address` varchar(50) NOT NULL,
			  `province` varchar(50) NOT NULL,
			  `postalcode` varchar(15) NOT NULL,
			  `routingcode` varchar(15) NOT NULL,
			  `availability` varchar(50) NULL,
			  `lat` float NOT NULL,
			  `lng` float NOT NULL,
			  `phone` varchar(20) NULL,
			  `sort` varchar(3) NOT NULL,
			  KEY `city` (`city`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
		");

		$this->db->query("
			ALTER TABLE `" . DB_PREFIX . "session` MODIFY `data` LONGTEXT
		");
		
		$this->refresh_data();
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xx_sfexeflockercom`");
	}
	
	public function XmlLoader($strXml)
	{
		set_error_handler(function($number, $error){
			if (preg_match('/^DOMDocument::loadXML\(\): (.+)$/', $error, $m) === 1) {
				throw new Exception($m[1]);
			}
		});
		$dom = new DOMDocument();
		$dom->loadXml($strXml);    
		restore_error_handler();
		return $dom;
	}
	
	public function refresh_data() {
		
		function remove_utf8_bom($text){
			$bom = pack('H*','EFBBBF');
			$text = preg_replace("/^$bom/", '', $text);
			return $text;
		}
		$pr_count = 0;
		//$ch = curl_init('https://ipl-hibox.eflocker.com/hibox/boxInfo/selectMapEdmBoxList');
		$ch = curl_init('https://www.zdexpress.com/api/eflocker/selectMapEdmBoxList/');
		//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$string = curl_exec($ch);
		$json = json_decode(remove_utf8_bom($string));
		

		$values = $json;
		

		foreach ($values as $value) {
			
			if($pr_count == 0) 
			{
				$this->db->query("DELETE FROM `" . DB_PREFIX . "xx_sfexeflockercom`");
			}
			
			$sqlString = "INSERT INTO `" . DB_PREFIX . "xx_sfexeflockercom`(place_id,name,city,address,province,postalcode,routingcode,availability,lat,lng,phone,sort) VALUES (";
			
			$sqlString .= "'".$this->db->escape($value->edCode)."',";
			$sqlString .= "'".$this->db->escape($value->edCode)."',";
			$sqlString .= "'".$this->db->escape($value->city)."',";
			$sqlString .= "'".$this->db->escape($value->twThrowaddress)."',";
			$sqlString .= "'".$this->db->escape($value->province)."',";
			$sqlString .= "'".$this->db->escape($value->district)."',";
			$sqlString .= "'".$this->db->escape($value->district)."',";
			$sqlString .= "'週一至週五: ".$this->db->escape($value->workday);
			if(strlen($value->saturday) == 0 && strlen($value->holiday) == 0 && strlen($value->sunday) == 0 ) {
				$sqlString .= "; 週六、日/公眾假期: ".$this->db->escape($value->workday);
			} else {
				if(strlen($value->saturday) > 0) {
					$sqlString .= "; 週六: ".$this->db->escape($value->saturday);
				}
				if(strlen($value->sunday) > 0) {
					$sqlString .= "; 日/公: ".$this->db->escape($value->sunday);
				}
				if(strlen($value->holiday) > 0) {
					$sqlString .= "; 眾假期: ".$this->db->escape($value->holiday);
				}
				
			
		}
			$sqlString .= "',";
			$sqlString .= "'".$this->db->escape($value->isBlackList)."',";
			$sqlString .= "'".$this->db->escape($value->isBlackList)."',";
			$sqlString .= "'".$this->db->escape($value->isHot)."',";
			
		/*	$sqlString .= "'".$this->db->escape($value->edcode)."',";
			$sqlString .= "'".$this->db->escape($value->edcode)."',";
			$sqlString .= "'".$this->db->escape($value->cityname)."',";
			$sqlString .= "'".$this->db->escape($value->twThrowaddress)."',";
			$sqlString .= "'".$this->db->escape($value->province)."',";
			$sqlString .= "'".$this->db->escape($value->dictname)."',";
			$sqlString .= "'".$this->db->escape($value->dictname)."',";
			$sqlString .= "'週一至週五: ".$this->db->escape($value->workday);
			if(strlen($value->saturday) == 0 && strlen($value->holiday) == 0 && strlen($value->sunday) == 0 ) {
				$sqlString .= "; 週六、日/公眾假期: ".$this->db->escape($value->workday);
			} else {
				if(strlen($value->saturday) > 0) {
					$sqlString .= "; 週六: ".$this->db->escape($value->saturday);
				}
				if(strlen($value->sunday) > 0) {
					$sqlString .= "; 日/公: ".$this->db->escape($value->sunday);
				}
				if(strlen($value->holiday) > 0) {
					$sqlString .= "; 眾假期: ".$this->db->escape($value->holiday);
				}
				
			}
			$sqlString .= "',";
			$sqlString .= "'".$this->db->escape($value->latitude)."',";
			$sqlString .= "'".$this->db->escape($value->longtitude)."',";
			$sqlString .= "'".$this->db->escape($value->hkTel)."',";*/

			$pr_count++;
			$sqlString .= "'1'";
			
			$sqlString .= ")";
			$this->db->query($sqlString);
			
		}			
		
	}
	
	public function populate_data() {
		$this->refresh_data();
        $this->index();
	}

	
	public function index() {   
		$this->language->load('extension/shipping/sfexeflockercom');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_sfexeflockercom', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}
				
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_none'] = $this->language->get('text_none');
		
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['entry_freelimit'] = $this->language->get('entry_freelimit');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['button_refresh'] = $this->language->get('button_refresh');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['entry_weight_from'] = $this->language->get('entry_weight_from');
		$data['entry_weight_from_tooltip'] = $this->language->get('entry_weight_from_tooltip');
		$data['entry_weight_to'] = $this->language->get('entry_weight_to');
		$data['entry_weight_to_tooltip'] = $this->language->get('entry_weight_to_tooltip');
		$data['entry_first'] = $this->language->get('entry_first');
		$data['entry_next'] = $this->language->get('entry_next');
		
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_add'] = $this->language->get('button_add');
		

		$data['refresh_data_href'] = $this->url->link('extension/shipping/sfexeflockercom/populate_data', 'user_token=' . $this->session->data['user_token'], true);
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_shipping'),
			'href'      => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token']. '&type=shipping', true)
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/shipping/sfexeflockercom', 'user_token=' . $this->session->data['user_token'], true)
   		);
		
		$data['action'] = $this->url->link('extension/shipping/sfexeflockercom', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token']. '&type=shipping', true);
		
		if (isset($this->request->post['shipping_sfexeflockercom_freelimit'])) {
			$data['shipping_sfexeflockercom_freelimit'] = $this->request->post['shipping_sfexeflockercom_freelimit'];
		} else {
			$data['shipping_sfexeflockercom_freelimit'] = $this->config->get('shipping_sfexeflockercom_freelimit');
		}
		
		if (isset($this->request->post['shipping_sfexeflockercom_weight_data'])) {
			$data['shipping_sfexeflockercom_weight_data'] = $this->request->post['shipping_sfexeflockercom_weight_data'];
		} else {
			$data['shipping_sfexeflockercom_weight_data'] = $this->config->get('shipping_sfexeflockercom_weight_data');
		}

		if (isset($this->request->post['shipping_sfexeflockercom_tax_class_id'])) {
			$data['shipping_sfexeflockercom_tax_class_id'] = $this->request->post['shipping_sfexeflockercom_tax_class_id'];
		} else {
			$data['shipping_sfexeflockercom_tax_class_id'] = $this->config->get('shipping_sfexeflockercom_tax_class_id');
		}
		
		$this->load->model('localisation/tax_class');
		
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['shipping_sfexeflockercom_geo_zone_id'])) {
			$data['shipping_sfexeflockercom_geo_zone_id'] = $this->request->post['shipping_sfexeflockercom_geo_zone_id'];
		} else {
			$data['shipping_sfexeflockercom_geo_zone_id'] = $this->config->get('shipping_sfexeflockercom_geo_zone_id');
		}
		
		$this->load->model('localisation/geo_zone');
		
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['shipping_sfexeflockercom_status'])) {
			$data['shipping_sfexeflockercom_status'] = $this->request->post['shipping_sfexeflockercom_status'];
		} else {
			$data['shipping_sfexeflockercom_status'] = $this->config->get('shipping_sfexeflockercom_status');
		}
		
		if (isset($this->request->post['shipping_sfexeflockercom_sort_order'])) {
			$data['shipping_sfexeflockercom_sort_order'] = $this->request->post['shipping_sfexeflockercom_sort_order'];
		} else {
			$data['shipping_sfexeflockercom_sort_order'] = $this->config->get('shipping_sfexeflockercom_sort_order');
		}	
		
		$sql = 'SELECT * FROM '.DB_PREFIX.'xx_sfexeflockercom ORDER BY city, name, address, postalcode' ;
		$query = $this->db->query($sql);
		foreach($query->rows as $result){
			$data['shipping_sfexeflockercom_places'][$result['place_id']] = array(
				'dickup_place'  => $result['name'].' - '.$result['address'],
				'dickup_region'   => $result['city'].'/'.$result['postalcode']
			); //(place_id,name,city,address,country,postalcode,routingcode,availability,lat,lng,phone,sort)
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('extension/shipping/sfexeflockercom', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/sfexeflockercom')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
	

}
?>