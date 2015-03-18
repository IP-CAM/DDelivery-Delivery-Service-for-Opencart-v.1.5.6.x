<?php
class ModelShippingDdelivery extends Model {
	public function getQuote($address) {
        $this->language->load('shipping/ddelivery');
        error_reporting(E_ERROR);
        if (!headers_sent())
                @header('Content-Type: text/html; charset=utf-8');
        //$scripts = '<script type="text/javascript" src="'.implode(DIRECTORY_SEPARATOR,array('ddelivery','assets','js','jquery-1.9.1.min.js')).'"></script>';
        //echo print_r($this->session->data,1).'<br />';
        //echo print_r($this->session->data['simple'],1).'<br />';
        $scripts = '<script type="text/javascript" src="'.implode(DIRECTORY_SEPARATOR,array('ddelivery','assets','js','ddelivery.js')).'"></script>';
        $scripts .= '<script type="text/javascript" src="'.implode(DIRECTORY_SEPARATOR,array('ddelivery','assets','js','ddelivery_include.js')).'"></script>';
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('ddelivery_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		//echo '<pre>'. print_r($this->session->data['ddelivery_order_id'], 1) . '</pre>';
		//echo '<pre>'. print_r($this->session->data['ddelivery'], 1) . '</pre>';
		if (!$this->config->get('ddelivery_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
        
        //echo '<pre>'. print_r($this->request->post, 1) . '</pre>';
        if (isset($this->request->post['order_product']) && isset($this->request->post['order_product'][0]) && isset($this->request->post['order_product'][0]['order_product_id'])){
            
            $query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$this->request->post['order_product'][0]['order_product_id'] . "'");
            if (isset($query->row['order_id']))
                $order_id = (int)$query->row['order_id'];   
            //echo '<pre>'. print_r($query, 1) . '</pre>'; 
        }
        
        
		$error = '';
		
		$quote_data = array();
		
		if ($status) {
			$weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('ddelivery_weight_class_id'));
            $title = '<strong>Сервис доставки DDelivery.</strong> ';
            $cost = 0;
            $this->bootstrap();
            $IntegratorShop = new IntegratorShop(false);
            
            $ddeliveryUI = new \DDelivery\DDeliveryUI($IntegratorShop,1);
            $orders = $ddeliveryUI->getAllOrders();
            $cart_check = true;
            if (count($orders) && isset($this->session->data['ddelivery_order_id'])){
                foreach($orders as $order){
                    if (!isset($this->session->data['ddelivery_order_id'])){
                        //$title .= ' !isset ddelivery_order_id';
                        $cart_check = false; break;
                    }
                    if ((int)$order['id'] == (int)$this->session->data['ddelivery_order_id']){
                        $dd_cart = unserialize($order['cart']);
                        $cms_cart = $this->cart->getProducts();
                        if (!count($dd_cart) || !count($cms_cart) || (count($dd_cart) !== count($cms_cart))){
                            $cart_check = false;
                            //$title .= ' !count';
                        } else {
                            foreach ($dd_cart as $k => $dd_product){
                                $key = $dd_product->getId();
                                if (!isset($cms_cart[$key])){
                                   foreach($cms_cart as $k => $product){
                                    if (strpos($k,$dd_product->getId().':')!==false)
                                        $key = $k;
                                   }
                                }
                                
                                if (!isset($cms_cart[$key])){
                                    $cart_check = false;
                                    //$title .= ' !$cms_cart ' .$dd_product->getId();
                                    break;
                                    
                                }
                                
                                if ( (int)$cms_cart[$key]['quantity'] !== (int)$dd_product->getQuantity()){
                                   $cart_check = false; 
                                   //$title .= ' !$quan '.$cms_cart[$key]['quantity']. '!==' .$dd_product->getQuantity() ;
                                }
                            }
                        }
                        //$title .= '<pre>'. print_r($dd_cart, 1) . '</pre>';
                        //$title .= '<pre>'. print_r($cms_cart, 1) . '</pre>';
                        //$title .= '<pre>'. print_r($cms_cart, 1) . '</pre>';
                        //$title .= '<pre>'. print_r($this->session->data, 1) . '</pre>';
                        
                    }
                }
            }else $cart_check = false;
            
            if (!$cart_check) {
                //echo '<pre>'. print_r($this->session->data['ddelivery_order_id'], 1) . '</pre>';
		        //echo '<pre>'. print_r($this->session->data['ddelivery'], 1) . '</pre>';
		        //echo '<pre>'. print_r($cms_cart, 1) . '</pre>';
                unset($_SESSION['ddelivery_order_id']);
                unset($_SESSION['ddelivery']);
            }
            
            if (isset($this->session->data['ddelivery']['comment'])){
                $title .= '<label id="dd_info">'.$this->session->data['ddelivery']['comment'].'</label>';
                $cost = $this->session->data['ddelivery']['cost'];
                }
            else{
                $title .= '<label id="dd_info"></label>';
            }
            
            if (isset($order_id) && isset($this->session->data['ddelivery_order_id'])){
                $dd_order = $ddeliveryUI->initOrder($this->session->data['ddelivery_order_id']);
                if (is_object($dd_order) && isset($dd_order->localId)){
                  $title = strip_tags($title .$this->session->data['ddelivery_order_id'] . ' ' . ' '. $ddeliveryUI->getPointComment($dd_order));  
                  $cost = (double)$ddeliveryUI->getOrderClientDeliveryPrice($dd_order);
                }
            }
            //if (strpos($_GET['route'],'checkout') !==false)
            //$title .= $scripts;
            //$title .= '<pre>'.print_r($this->session->data,1).'</pre>';
            //$title .= print_r((int)$cart_check,1);
            $quote_data['ddelivery'] = array(
							'code'         => 'ddelivery.ddelivery',
							'title'        => $title,
							'cost'         => $this->currency->convert($cost,'RUB',$this->config->get('config_currency')),//$this->currency->convert($response_info['charge'], 'AUD', $this->config->get('config_currency'))
							'tax_class_id' => $this->config->get('ddelivery_tax_class_id'),
							'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($cost, 'RUB', $this->currency->getCode()), $this->config->get('ddelivery_tax_class_id'), $this->config->get('config_tax')), $this->currency->getCode(), 1.0000000) .$scripts //
						);
		}
		
		$method_data = array();
		
		if ($quote_data) {
			$method_data = array(
				'code'       => 'ddelivery',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('ddelivery_sort_order'),
				'error'      => $error 
			);
		}
		
		return $method_data;
	}
    
    public function bootstrap(){
        require_once(DIR_APPLICATION . '../ddelivery/application/bootstrap.php');
        require_once(DIR_APPLICATION . '../ddelivery/application/classes/DDelivery/DDeliveryUI.php');
        require_once(DIR_APPLICATION . '../ddelivery/IntegratorShop.php');
    }
    
    public function cmsOrderFinish($shopOrderID, $status, $payment){
        try{
        	$this->bootstrap();
            $id = $this->session->data['ddelivery_order_id'];
            //print_r($this->session->data['shipping_methods']['ddelivery']['quote']['ddelivery']);
            $IntegratorShop = new IntegratorShop();
            $ddeliveryUI = new \DDelivery\DdeliveryUI($IntegratorShop, true); 
            if ((int)$id > 0){
                $order = $ddeliveryUI->getOrderByCmsId($shopOrderID);
                if (is_object($order)){
                    $order->shopRefnum = '';
                    $ddeliveryUI->saveFullOrder($order);
                }
                $ddeliveryUI->onCmsOrderFinish($id, $shopOrderID, $status, $payment);
            }
            //unset($this->session->data['ddelivery_order_id']); 
            //unset($this->session->data['ddelivery']['comment']);
        }
        catch(\DDelivery\DDeliveryException $e)
        {
            echo $e->getMessage();
            $ddeliveryUI->logMessage($e);
        }

    }
}
?>