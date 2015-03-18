<?php
class ControllerShippingDdelivery extends Controller {
	private $error = array(); 

	public function index() { 
	    $this->load->model('shipping/ddelivery');
        //echo '<pre>'.print_r($this->data['payment_methods'],1).'</pre>';
		$this->language->load('shipping/ddelivery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ddelivery', $this->request->post);             

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_workmode_test'] = $this->language->get('text_workmode_test');
		$this->data['text_workmode_work'] = $this->language->get('text_workmode_work');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_none'] = $this->language->get('text_none');
        $keys = array('api','work_mode','insur','theme','enabled_type','show_contact_form',
                      'status_confirmed','status_in_progress','status_in_stock','status_in_way',
                      'status_delivered','status_received','status_return','status_customer_returned', 
                      'status_partial_refund','status_returned_mi','status_waiting','status_cancel',  
                      'width','length','height','weight','pvz_companies','cur_companies','cur_payment',
                      'pvz_payment','price_from','price_to','pay_type','summ','zabor','round','round_step');
        foreach ($keys as $key){
		  $this->data["entry_{$key}"] = $this->language->get("entry_{$key}");
		  $this->data["entry_{$key}_help"] = $this->language->get("entry_{$key}_help");
        }
		$this->data['main_settings'] = $this->language->get('main_settings');
		$this->data['statuses_settings'] = $this->language->get('statuses_settings');
		$this->data['statuses_settings_desc'] = $this->language->get('statuses_settings_desc');
		$this->data['spec_settings'] = $this->language->get('spec_settings');
		$this->data['size_settings'] = $this->language->get('size_settings');
		$this->data['size_settings_desc'] = $this->language->get('size_settings_desc');
		$this->data['fields_settings'] = $this->language->get('fields_settings');
		$this->data['shipping_settings'] = $this->language->get('shipping_settings');
		$this->data['shipping_settings_desc'] = $this->language->get('shipping_settings_desc');
		$this->data['courier_companies_settings'] = $this->language->get('courier_companies_settings');
		$this->data['self_companies_settings'] = $this->language->get('self_companies_settings');
		$this->data['price_settings'] = $this->language->get('price_settings');
		$this->data['price_settings_desc'] = $this->language->get('price_settings_desc');
		
		$this->data['entry_display_time'] = $this->language->get('entry_display_time');
		$this->data['entry_length_class'] = $this->language->get('entry_length_class');
		$this->data['entry_weight_class'] = $this->language->get('entry_weight_class');
		$this->data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');         
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
        
        /**
         * 
         * Группа основных настроек
         * 
         */
        
        if (isset($this->error['api'])) {
			$this->data['error_api'] = $this->error['api'];
		} else {
			$this->data['error_api'] = '';
		}
        
        if (isset($this->error['insur'])) {
			$this->data['error_insur'] = $this->error['insur'];
		} else {
			$this->data['error_insur'] = '';
		}
        
        if (isset($this->request->post['ddelivery_api'])) {
			$this->data['ddelivery_api'] = $this->request->post['ddelivery_api'];
		} else {
			$this->data['ddelivery_api'] = $this->config->get('ddelivery_api');
		}
        
        if (isset($this->request->post['ddelivery_work_mode'])) {
			$this->data['ddelivery_work_mode'] = $this->request->post['ddelivery_work_mode'];
		} else {
			$this->data['ddelivery_work_mode'] = $this->config->get('ddelivery_work_mode');
		}
        
        $this->data['payment_methods'] = $this->model_shipping_ddelivery->getPaymentMethods();  
        
        if (isset($this->request->post['ddelivery_cur_payment'])) {
			$this->data['ddelivery_cur_payment'] = $this->request->post['ddelivery_cur_payment'];
		} else {
			$this->data['ddelivery_cur_payment'] = $this->config->get('ddelivery_cur_payment');
		}
        
        if (isset($this->request->post['ddelivery_pvz_payment'])) {
			$this->data['ddelivery_pvz_payment'] = $this->request->post['ddelivery_pvz_payment'];
		} else {
			$this->data['ddelivery_pvz_payment'] = $this->config->get('ddelivery_pvz_payment');
		}
        
        if (isset($this->request->post['ddelivery_insur'])) {
			$this->data['ddelivery_insur'] = (double)$this->request->post['ddelivery_insur'];
		} else {
			$this->data['ddelivery_insur'] = $this->config->get('ddelivery_insur');
		}
        
        $this->data['ddelivery_themes'] = array('default','blue');
        
        if (isset($this->request->post['ddelivery_theme'])) {
			$this->data['ddelivery_theme'] = $this->request->post['ddelivery_theme'];
		} else {
			$this->data['ddelivery_theme'] = $this->config->get('ddelivery_theme');
		}
                                    
        $this->data['ddelivery_enabled_types'] = array(
                                    array('id' => 1,'name' => $this->language->get('text_enabled_type_1')), 
                                    array('id' => 2,'name' => $this->language->get('text_enabled_type_2')),
                                    array('id' => 3,'name' => $this->language->get('text_enabled_type_3')),
                                    //array('id' => 4,'name' => $this->language->get('text_enabled_type_4')),
                                    );
        
        if (isset($this->request->post['ddelivery_enabled_type'])) {
			$this->data['ddelivery_enabled_type'] = $this->request->post['ddelivery_enabled_type'];
		} else {
			$this->data['ddelivery_enabled_type'] = $this->config->get('ddelivery_enabled_type');
		}
        
        if (isset($this->request->post['ddelivery_show_contact_form'])) {
			$this->data['ddelivery_show_contact_form'] = $this->request->post['ddelivery_show_contact_form'];
		} else {
			$this->data['ddelivery_show_contact_form'] = $this->config->get('ddelivery_show_contact_form');
		}
        
        if (isset($this->request->post['ddelivery_display_time'])) {
			$this->data['ddelivery_display_time'] = $this->request->post['ddelivery_display_time'];
		} else {
			$this->data['ddelivery_display_time'] = $this->config->get('ddelivery_display_time');
		}
        
        if (isset($this->request->post['ddelivery_length_class_id'])) {
			$this->data['ddelivery_length_class_id'] = $this->request->post['ddelivery_length_class_id'];
		} else {
			$this->data['ddelivery_length_class_id'] = $this->config->get('ddelivery_length_class_id');
		}
        
        $this->load->model('localisation/length_class');
		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
        
		if (isset($this->request->post['ddelivery_weight_class_id'])) {
			$this->data['ddelivery_weight_class_id'] = $this->request->post['ddelivery_weight_class_id'];
		} else {
			$this->data['ddelivery_weight_class_id'] = $this->config->get('ddelivery_weight_class_id');
		}

		$this->load->model('localisation/weight_class');
		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		if (isset($this->request->post['ddelivery_tax_class_id'])) {
			$this->data['ddelivery_tax_class_id'] = $this->request->post['ddelivery_tax_class_id'];
		} else {
			$this->data['ddelivery_tax_class_id'] = $this->config->get('ddelivery_tax_class_id');
		}

		$this->load->model('localisation/tax_class');
        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['ddelivery_geo_zone_id'])) {
			$this->data['ddelivery_geo_zone_id'] = $this->request->post['ddelivery_geo_zone_id'];
		} else {
			$this->data['ddelivery_geo_zone_id'] = $this->config->get('ddelivery_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['ddelivery_status'])) {
			$this->data['ddelivery_status'] = $this->request->post['ddelivery_status'];
		} else {
			$this->data['ddelivery_status'] = $this->config->get('ddelivery_status');
		}

		if (isset($this->request->post['ddelivery_sort_order'])) {
			$this->data['ddelivery_sort_order'] = (int)$this->request->post['ddelivery_sort_order'];
		} else {
			$this->data['ddelivery_sort_order'] = $this->config->get('ddelivery_sort_order');
		}
        
        /**
         * Группа настроек статусов
         */
         
        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->data['dd_statuses'] = array('confirmed','in_progress','in_stock','in_way','delivered','received','return','customer_returned','partial_refund',
                    'returned_mi','waiting','cancel');
        foreach($this->data['dd_statuses'] as $status){
            if (isset($this->request->post['ddelivery_status_'.$status])) {
    			$this->data['ddelivery_status_'.$status] = $this->request->post['ddelivery_status_'.$status];
    		} else {
    			$this->data['ddelivery_status_'.$status] = $this->config->get('ddelivery_status_'.$status);
    		} 
        }
        
        /**
         * 
         * Группа настроек габаритов
         * 
         */
        
        if (isset($this->error['width'])) {
			$this->data['error_width'] = $this->error['width'];
		} else {
			$this->data['error_width'] = '';
		}
        if (isset($this->error['length'])) {
			$this->data['error_length'] = $this->error['length'];
		} else {
			$this->data['error_length'] = '';
		}
        if (isset($this->error['height'])) {
			$this->data['error_height'] = $this->error['height'];
		} else {
			$this->data['error_height'] = '';
		}
        if (isset($this->error['weight'])) {
			$this->data['error_weight'] = $this->error['weight'];
		} else {
			$this->data['error_weight'] = '';
		}
        
        if (isset($this->request->post['ddelivery_width'])) {
			$this->data['ddelivery_width'] = (double)$this->request->post['ddelivery_width'];
		} else {
			$this->data['ddelivery_width'] = $this->config->get('ddelivery_width');
		}
        if (isset($this->request->post['ddelivery_length'])) {
			$this->data['ddelivery_length'] = (double)$this->request->post['ddelivery_length'];
		} else {
			$this->data['ddelivery_length'] = $this->config->get('ddelivery_length');
		}
        if (isset($this->request->post['ddelivery_height'])) {
			$this->data['ddelivery_height'] = (double)$this->request->post['ddelivery_height'];
		} else {
			$this->data['ddelivery_height'] = $this->config->get('ddelivery_height');
		}
        if (isset($this->request->post['ddelivery_weight'])) {
			$this->data['ddelivery_weight'] = (double)$this->request->post['ddelivery_weight'];
		} else {
			$this->data['ddelivery_weight'] = $this->config->get('ddelivery_weight');
		}
        
        /**
         * 
         * Группа настроек способов доставки
         * 
         */
         
        $this->data['tks'] = $this->model_shipping_ddelivery->getTKS();
        
        if (isset($this->request->post['ddelivery_cur_companies'])) {
			$this->data['ddelivery_cur_companies'] = $this->request->post['ddelivery_cur_companies'];
		} else {
			$this->data['ddelivery_cur_companies'] = $this->config->get('ddelivery_cur_companies');
		}
        
        if (isset($this->request->post['ddelivery_pvz_companies'])) {
			$this->data['ddelivery_pvz_companies'] = $this->request->post['ddelivery_pvz_companies'];
		} else {
			$this->data['ddelivery_pvz_companies'] = $this->config->get('ddelivery_pvz_companies');
		}
        
        /**
         * 
         * Группа настроек цены доставки
         * 
         */
        
        if (isset($this->request->post['ddelivery_price_from'])) {
			$this->data['ddelivery_price_from'] = (double)$this->request->post['ddelivery_price_from'];
		} else {
			$this->data['ddelivery_price_from'] = $this->config->get('ddelivery_price_from');
		}
        
        if (isset($this->request->post['ddelivery_price_to'])) {
			$this->data['ddelivery_price_to'] = (double)$this->request->post['ddelivery_price_to'];
		} else {
			$this->data['ddelivery_price_to'] = $this->config->get('ddelivery_price_to');
		} 
         
        $this->data['ddelivery_pay_types'] = array(
                                    array('id' => 1,'name' => $this->language->get('text_pay_type_1')), 
                                    array('id' => 2,'name' => $this->language->get('text_pay_type_2')),
                                    array('id' => 3,'name' => $this->language->get('text_pay_type_3')),
                                    array('id' => 4,'name' => $this->language->get('text_pay_type_4')),
                                    );
                                    
        if (isset($this->request->post['ddelivery_pay_type'])) {
			$this->data['ddelivery_pay_type'] = (int)$this->request->post['ddelivery_pay_type'];
		} else {
			$this->data['ddelivery_pay_type'] = $this->config->get('ddelivery_pay_type');
		}
        
        if (isset($this->request->post['ddelivery_summ'])) {
			$this->data['ddelivery_summ'] = (double)$this->request->post['ddelivery_summ'];
		} else {
			$this->data['ddelivery_summ'] = $this->config->get('ddelivery_summ');
		}
        
        $this->data['ddelivery_round_types'] = array(
                                    array('id' => 1,'name' => $this->language->get('text_round_type_1')), 
                                    array('id' => 2,'name' => $this->language->get('text_round_type_2')),
                                    array('id' => 3,'name' => $this->language->get('text_round_type_3')),
                                    );
        
        if (isset($this->request->post['ddelivery_round'])) {
			$this->data['ddelivery_round'] = (double)$this->request->post['ddelivery_round'];
		} else {
			$this->data['ddelivery_round'] = $this->config->get('ddelivery_round');
		}
        
        if (isset($this->error['round_step'])) {
			$this->data['error_round_step'] = $this->error['round_step'];
		} else {
			$this->data['error_round_step'] = '';
		}
        if (isset($this->request->post['ddelivery_round_step'])) {
			$this->data['ddelivery_round_step'] = (double)$this->request->post['ddelivery_round_step'];
		} else {
			$this->data['ddelivery_round_step'] = $this->config->get('ddelivery_round_step');
		}
        
        if (isset($this->request->post['ddelivery_zabor'])) {
			$this->data['ddelivery_zabor'] = (double)$this->request->post['ddelivery_zabor'];
		} else {
			$this->data['ddelivery_zabor'] = $this->config->get('ddelivery_zabor');
		}
        
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_shipping'),
			'href'      => $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('shipping/ddelivery', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('shipping/ddelivery', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		$this->template = 'shipping/ddelivery.tpl';
		$this->children = array(
			'common/header',        
			'common/footer' 
		);

		$this->response->setOutput($this->render(true), $this->config->get('config_compression'));
	}
    
    
    public function install(){
        require_once(DIR_APPLICATION . '../ddelivery/application/bootstrap.php');
        require_once(DIR_APPLICATION . '../ddelivery/application/classes/DDelivery/DDeliveryUI.php');
        require_once(DIR_APPLICATION . '../ddelivery/IntegratorShop.php');
        //}
        try{
            $IntegratorShop = new IntegratorShop(true);
    		$ddeliveryUI = new \Ddelivery\DDeliveryUI($IntegratorShop, true);
    		$ddeliveryUI->createTables();
            $base = substr(DIR_APPLICATION,0,-1); 
            $this->rewriteAdminControllerSaleOrders();
            $this->rewriteCatalogControllerConfirm();
            if (file_exists(implode(DIRECTORY_SEPARATOR, array($base, '..', 'catalog','controller', 'checkout','simplecheckout.php'))))
                $this->rewriteCatalogControllerSimplecheckout();    
            //else echo 'файл simplecheckout.php не найден ' . implode(DIRECTORY_SEPARATOR, array($base, '..', 'catalog', 'checkout','simplecheckout.php'));
                
        }
        catch(Exception $e){
            echo $e->getMessage();
        } 
    }
    
    public function uninstall(){
         //$pref = 'oc_dd_';
         //$this->db->query("drop table if exists {$pref}orders");
         //$this->db->query("drop table if exists {$pref}cache");
         $order_path = implode(DIRECTORY_SEPARATOR, array(DIR_APPLICATION, 'controller', 'sale'));
         $order_fname = (file_exists(implode(DIRECTORY_SEPARATOR, array($order_path,'orderg.php'))))?'orderg.php':'order.php';
         $order_file = implode(DIRECTORY_SEPARATOR, array($order_path,$order_fname));
         if (file_exists($order_path.DIRECTORY_SEPARATOR.'backup.order.php')){
            unlink($order_file);
            rename($order_path.DIRECTORY_SEPARATOR.'backup.order.php',$order_file);   
         } 
         
         $base = substr(DIR_APPLICATION,0,-1); 
         $checkout_path = implode(DIRECTORY_SEPARATOR, array($base, '..','catalog', 'controller', 'checkout'));
         $confirm_file = implode(DIRECTORY_SEPARATOR, array($checkout_path,'confirm.php'));
         if (file_exists($checkout_path.DIRECTORY_SEPARATOR.'backup.confirm.php')){
            unlink($confirm_file);
            rename($checkout_path.DIRECTORY_SEPARATOR.'backup.confirm.php',$confirm_file);   
         }
         
         if (file_exists($checkout_path.DIRECTORY_SEPARATOR.'simplecheckout.php')){
             $checkout_path = implode(DIRECTORY_SEPARATOR, array($base, '..','catalog', 'controller', 'checkout'));
             $simplecheckout_file = implode(DIRECTORY_SEPARATOR, array($checkout_path,'simplecheckout.php'));
             if (file_exists($checkout_path.DIRECTORY_SEPARATOR.'backup.simplecheckout.php')){
                unlink($simplecheckout_file);
                rename($checkout_path.DIRECTORY_SEPARATOR.'backup.simplecheckout.php',$simplecheckout_file);   
             } 
         } 
    }
    
    public function backup($path, $file){
        $return = false;  
        header('Content-Type: text/html; charset=utf-8');
        if (!is_writable($path)){
            echo "Директория $path недоступна для записи.";
        }   
        elseif(!is_writable($path.DIRECTORY_SEPARATOR.$file)){
            echo "Файл $path/$file недоступен для записи.";
        }
        elseif(file_exists($path.DIRECTORY_SEPARATOR.'backup.'.$file)){
            echo "Файл $path/backup.$file уже существует";
        }
        elseif(!copy($path.DIRECTORY_SEPARATOR.$file, $path.DIRECTORY_SEPARATOR.'backup.'.$file)){
            echo "Ошибка при копиравании файла " . $path.DIRECTORY_SEPARATOR.$file . ' в '. $path.DIRECTORY_SEPARATOR.'backup.'.$file;  
        }
        else 
            $return = true;
        return $return;
    }
    
    public function rewriteAdminControllerSaleOrders(){
        error_reporting(E_ALL);
        $order_path = implode(DIRECTORY_SEPARATOR, array(DIR_APPLICATION, 'controller', 'sale'));
        $order_fname = (file_exists(implode(DIRECTORY_SEPARATOR, array($order_path,'orderg.php'))))?'orderg.php':'order.php';
        $order_file = implode(DIRECTORY_SEPARATOR, array($order_path,$order_fname));
        $order_text = file_get_contents(implode(DIRECTORY_SEPARATOR, array(DIR_APPLICATION, 'controller', 'sale', $order_fname)));
        $this->backup($order_path, $order_fname);
        //обработка события при смене статуса заказа в админке магазина
        if (strpos($order_text,'cmsChangeStatus')==false)
            $order_text = preg_replace('/public function update\(\)\s*{/',"public function update() {\n\t\t
            if ((\$this->request->server['REQUEST_METHOD'] == 'POST') && \$this->validateForm()) {
                \$this->load->model('shipping/ddelivery');
                \$this->model_shipping_ddelivery->cmsChangeStatus(\$this->request->get['order_id'],\$this->request->post['order_status_id']);\n\t\t\t
            }\n\t\t ",$order_text);
        
        $order_text = preg_replace('/\$this->data\[\'histories\'\]\s*=\s*array\(\);/',
        "\$this->data['histories'] = array();
         if (\$this->request->server['REQUEST_METHOD'] == 'POST') {
                \$this->load->model('shipping/ddelivery');
                \$this->model_shipping_ddelivery->cmsChangeStatus(\$this->request->get['order_id'],\$this->request->post['order_status_id']);
         }",$order_text);
        //добавление информации о заказе DDelivery в админке
        if (strpos($order_text,'getOrderInfoHtml')==false)
            $order_text = preg_replace(
            '/\$this->data\[\'comment\'\]\s*=\s*nl2br\(\$order_info\[\'comment\'\]\);\s*\$this->data\[\'shipping_method\'\]\s*=\s*\$order_info\[\'shipping_method\'\];\s*/',
            "\$this->load->model('shipping/ddelivery');\n
            \$this->data['comment'] = nl2br(\$order_info['comment']);
            \$this->data['shipping_method'] = strip_tags(\$order_info['shipping_method']) . \$this->model_shipping_ddelivery->getOrderInfoHtml(\$this->request->get['order_id']);\n\t\t\t",$order_text);
        
        if (strpos($order_text,"\$this->data['shipping_method'] = \$this->request->post['shipping_method'];") !== false)
            $order_text = str_replace("\$this->data['shipping_method'] = \$this->request->post['shipping_method'];",
                        "\$this->data['shipping_method'] = strip_tags(\$this->request->post['shipping_method']);",$order_text);
        
        if (strpos($order_text,"\$this->data['shipping_method'] = \$order_info['shipping_method'];") !== false)
            $order_text = str_replace("\$this->data['shipping_method'] = \$order_info['shipping_method'];",
                        "\$this->data['shipping_method'] = strip_tags(\$order_info['shipping_method']);",$order_text);
        
        if (strpos($order_text,"array_walk(\$this->data['order_totals'],function(&\$v){\$v['title'] = strip_tags(\$v['title']); });") === false){
		$order_text = str_replace(
        "\$this->template = 'sale/order_form.tpl';",
        "array_walk(\$this->data['order_totals'],function(&\$v){\$v['title'] = strip_tags(\$v['title']); });
        \$this->template = 'sale/order_form.tpl';",$order_text);    
        }                
        file_put_contents($order_file,$order_text);    
    }
    
    public function rewriteCatalogControllerSimplecheckout(){
        $base = substr(DIR_APPLICATION,0,-1);
        $simplecheckout_path = implode(DIRECTORY_SEPARATOR, array($base, '..','catalog', 'controller', 'checkout'));
        $simplecheckout_file = implode(DIRECTORY_SEPARATOR, array($simplecheckout_path,'simplecheckout.php'));
        $simplecheckout_text = file_get_contents(implode(DIRECTORY_SEPARATOR, array($simplecheckout_path, 'simplecheckout.php')));
        $this->backup($simplecheckout_path, 'simplecheckout.php');
        
        if (strpos($simplecheckout_text,'cmsOrderFinish')==false)
            $simplecheckout_text = preg_replace(
                '/return \$order_id;/',
            "if (isset(\$this->session->data['ddelivery_order_id'])){
                \$this->load->model('shipping/ddelivery');
                \$this->model_shipping_ddelivery->cmsOrderFinish(\$this->session->data['order_id'], \$this->config->get('config_order_status_id'),\$this->session->data['payment_method']['code']);			
            }
            return \$order_id;",
            $simplecheckout_text);
        
        if ($simplecheckout_text)
            file_put_contents($simplecheckout_file,$simplecheckout_text);
    }
    
    public function rewriteCatalogControllerConfirm(){
        $base = substr(DIR_APPLICATION,0,-1);
        $confirm_path = implode(DIRECTORY_SEPARATOR, array($base, '..','catalog', 'controller', 'checkout'));
        $confirm_file = implode(DIRECTORY_SEPARATOR, array($confirm_path,'confirm.php'));
        $confirm_text = file_get_contents(implode(DIRECTORY_SEPARATOR, array($confirm_path, 'confirm.php')));
        $this->backup($confirm_path, 'confirm.php');
        
        if (strpos($confirm_text,'cmsOrderFinish')==false){
            $confirm_text = preg_replace(
                '/if \(file_exists\(DIR_TEMPLATE . \$this->config->get\(\'config_template\'\) . \'\/template\/checkout\/confirm.tpl\'\)\) {/',
            "if (isset(\$this->session->data['ddelivery_order_id'])){
                \$this->load->model('shipping/ddelivery');
                \$this->model_shipping_ddelivery->cmsOrderFinish(\$this->session->data['order_id'], \$this->config->get('config_order_status_id'),\$this->session->data['payment_method']['code']);			
            }
            if (file_exists(DIR_TEMPLATE . \$this->config->get('config_template') . '/template/checkout/confirm.tpl')) {",
            $confirm_text);
        }
        if ($confirm_text)
            file_put_contents($confirm_file,$confirm_text);
    }
    
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/ddelivery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!preg_match('/^[0-9a-z]{32}$/', $this->request->post['ddelivery_api'])){
			$this->error['api'] = $this->language->get('error_api');
		}
        
        if ((double)$this->request->post['ddelivery_insur'] <= 0 ){
			$this->error['insur'] = $this->language->get('error_insur');
		}
        if ((double)$this->request->post['ddelivery_width'] <= 0 ){
			$this->error['width'] = $this->language->get('error_width');
		}
        if ((double)$this->request->post['ddelivery_length'] <= 0 ){
			$this->error['length'] = $this->language->get('error_length');
		}
        if ((double)$this->request->post['ddelivery_height'] <= 0 ){
			$this->error['height'] = $this->language->get('error_height');
		}
        if ((double)$this->request->post['ddelivery_weight'] <= 0 ){
			$this->error['weight'] = $this->language->get('error_weight');
		}
        if ((double)$this->request->post['ddelivery_round_step'] <= 0 ){
			$this->error['round_step'] = $this->language->get('error_round_step');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}       
	}
}
?>