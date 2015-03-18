<?php
class ModelShippingDDelivery extends Model {
    
    protected $payment_methods;
	
    public function getPaymentMethods() {
		$this->load->model('setting/extension');
		$extensions = $this->model_setting_extension->getInstalled('payment');
	    $files = glob(DIR_APPLICATION . 'controller/payment/*.php');
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				$this->language->load('payment/' . $extension);
                if (in_array($extension,$extensions))
    				$this->payment_methods[] = array(
    					'id'         => $extension,  
                        'name'       => $this->language->get('heading_title'),
    				);
			}
		}
        return $this->payment_methods;
	}
    
    public function bootstrap(){
        require_once(substr(DIR_APPLICATION,0,strpos(DIR_APPLICATION,'admin')) . 'ddelivery/application/bootstrap.php');
        require_once(substr(DIR_APPLICATION,0,strpos(DIR_APPLICATION,'admin')) . 'ddelivery/application/classes/DDelivery/DDeliveryUI.php');
        require_once(substr(DIR_APPLICATION,0,strpos(DIR_APPLICATION,'admin')) . 'ddelivery/IntegratorShop.php');
    }
    
    public function getTKS(){
        $return = array();
        try{
            $this->bootstrap();
            $items = \DDelivery\DDeliveryUI::getCompanySubInfo();
            if (is_array($items) && count($items)){
                foreach($items as $id => $item)
                    $return[$id] = $item['name'];
            }
            return $return;
        }
        catch(\DDelivery\DDeliveryException $e)
        {
            $ddeliveryUI->logMessage($e);
            return $return;
        }
    }
    
    public function cmsChangeStatus($cmsOrderID, $status){
        try{
            $this->bootstrap();
        	$IntegratorShop = new IntegratorShop(true);
        	$ddeliveryUI = new \DDelivery\DDeliveryUI($IntegratorShop, true);
        	$ddeliveryUI->onCmsChangeStatus($cmsOrderID, $status);
        }
        catch(\DDelivery\DDeliveryException $e)
        {
            $ddeliveryUI->logMessage($e);
        }

    }
    
    public function getOrderInfoHtml($cmsOrderID){
        
        if ((int)$cmsOrderID >0){
            
            try{
                $this->bootstrap();
            	$IntegratorShop = new IntegratorShop(true);
            	$ddeliveryUI = new \DDelivery\DDeliveryUI($IntegratorShop, true);
            	$order = $ddeliveryUI->getOrderByCmsID($cmsOrderID);
        		if(empty($order)) return;
                $point = $order->getPoint();
        
                if($order->toStreet == NULL && $order->toHouse == NULL){
                    $address = 'Адрес доставки: Самовывоз';
                }else{
                    $address = 'Адрес доставки: '.$order->toStreet . ' ' . $order->toHouse . ' ' . $order->toFlat;
                }
                $return = array(
                        'ID заявки на сервере DD:' => $order->ddeliveryID,
                        'Способ доставки:' => ((int)$order->type == 1)?'Самовывоз':'Курьерская доставка',
                        'Клиент:' => "{$order->secondName} {$order->firstName} {$order->toEmail} {$order->toPhone}",
                        'Компания доставки:' => $point['delivery_company_name'],
                        'Стоимость доставки для клиента:' => $ddeliveryUI->getOrderClientDeliveryPrice($order) .' руб.',
                        'Реальная стоимость доставки:' => $ddeliveryUI->getOrderRealDeliveryPrice($order) .' руб.',
                        'Выбранный модуль оплаты в магазине:' => $order->paymentVariant,
                );
                
                if ((int)$order->type == 1){
                    $return['Регион:'] = $point['region'];
                    $return['Город:'] = $point['city_type'] . ' '.  $point['city'];
                    $return['Индекс:'] = $point['postal_code'];
                    $return['Пункт самовывоза:'] = $point['name'];
                    $return['Тип пункта самовывоза:'] = ($point['type'] == 2)?'Живой пункт':'Ячейка';
                    $return['Описание пункта самовывоза:'] = $point['description_out'];
                    $return['Адрес пункта самовывоза:'] = $point['address'];
                    $return['Режим работы:'] = $point['schedule'];
                    if (strlen($point['metro']))
                        $return['Метро:'] = $point['metro'];
                    if ((int)$point['is_cash'] == 1 && (int)$point['is_card'] !== 1)
                        $return['Доступные способы оплаты:'] = 'Оплата наличными';
                    if ((int)$point['is_cash'] !== 1 && (int)$point['is_card'] == 1)
                        $return['Доступные способы оплаты:'] = 'Оплата картой';
                    if ((int)$point['is_cash'] == 1 && (int)$point['is_card'] == 1)
                        $return['Доступные способы оплаты:'] = 'Оплата наличными или картой';
                }
                elseif((int)$order->type == 2){
                    $return['Город:'] = $order->cityName;
                    $return['Улица:'] = $order->toStreet;
                    if ($order->toHouse)
                        $return['Дом:'] = $order->toHouse;
                    if ($order->toHousing)
                        $return['Корпус:'] = $order->toHousing;
                    if ($order->toFlat)
                        $return['Квартира:'] = $order->toFlat;
                        
                    $return['Время доставки (в днях):'] = "от $point[delivery_time_min] до $point[delivery_time_max] (в среднем: $point[delivery_time_avg])";
                }
        		$html = '<table class="adminlist table">' . "\n";
                
                if (is_array($return) && count($return))
                    foreach ($return as $k => $v)
                        $html .= "<tr><td><strong>$k</strong></td><td>$v</td></tr>\n" ;    
                
        		$html .= '</table>' . "\n";
                
                return $html;
            }
            catch(\DDelivery\DDeliveryException $e)
            {
                $ddeliveryUI->logMessage($e);
                echo 'error: '. $e->getMessage();
            }    
        }
        else return '';
    }
    	
}
?>