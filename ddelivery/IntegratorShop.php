<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 15.05.14
 * Time: 23:14
 */
 error_reporting(E_ALL);
 $system_dir = substr(__DIR__,0,strpos(__DIR__,'ddelivery')).'/system/';
if (!class_exists('Config')) {
	require_once('../config.php');
    
    require_once($system_dir.'startup.php');
    if (!class_exists('Currency'))
        require_once($system_dir.'library/currency.php');
    if (!class_exists('Weight'))
        require_once($system_dir.'library/weight.php');
    if (!class_exists('Length'))
        require_once($system_dir.'library/length.php');
}  

    if (!class_exists('Affiliate'))
        require_once($system_dir.'library/affiliate.php');
    if (!class_exists('Customer'))
        require_once($system_dir.'library/customer.php');
    if (!class_exists('Cart'))
        require_once($system_dir.'library/cart.php');
    if (!class_exists('Tax'))
        require_once($system_dir.'library/tax.php');


use DDelivery\Order\DDeliveryOrder;
use DDelivery\Order\DDeliveryProduct;
use DDelivery\Order\DDStatusProvider;

class IntegratorShop extends \DDelivery\Adapter\PluginFilters
{
    var $user;
    
    protected $registry;
    protected $default_currency_code;
    
    /**
     * Синхронизация локальных статусов и статусов дделивери
     * @var array
     */
    protected  $cmsOrderStatus ;
                                        
    public function __construct($admin = false){
        $this->registry = $this->getRegistry();
        $this->cmsOrderStatus = array( DDStatusProvider::ORDER_IN_PROGRESS => $this->config->get('ddelivery_status_in_progress'),
                                 DDStatusProvider::ORDER_CONFIRMED => $this->config->get('ddelivery_status_confirmed'),
                                 DDStatusProvider::ORDER_IN_STOCK => $this->config->get('ddelivery_status_in_stock'),
                                 DDStatusProvider::ORDER_IN_WAY => $this->config->get('ddelivery_status_in_way'),
                                 DDStatusProvider::ORDER_DELIVERED => $this->config->get('ddelivery_status_delivered'),
                                 DDStatusProvider::ORDER_RECEIVED => $this->config->get('ddelivery_status_received'),
                                 DDStatusProvider::ORDER_RETURN => $this->config->get('ddelivery_status_return'),
                                 DDStatusProvider::ORDER_CUSTOMER_RETURNED => $this->config->get('ddelivery_status_customer_returned'),
                                 DDStatusProvider::ORDER_PARTIAL_REFUND => $this->config->get('ddelivery_status_refund'),
                                 DDStatusProvider::ORDER_RETURNED_MI => $this->config->get('ddelivery_status_returned_mi'),
                                 DDStatusProvider::ORDER_WAITING => $this->config->get('ddelivery_status_waiting'),
                                 DDStatusProvider::ORDER_CANCEL => $this->config->get('ddelivery_status_cancel') );
        //Address
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency where value=1 limit 1");
        $this->default_currency_code = $query->row['code'];

        if ($admin == false){
            $this->user = $this->getUserFields();
        }
    }
    
    public function getRegistry(){
        $registry = new Registry();

        // Loader
        $loader = new Loader($registry);
        $registry->set('load', $loader);
        
        // Config
        $config = new Config();
        $registry->set('config', $config);
        
        // Cart
        $cart = new Cart($registry);
        $registry->set('cart', $cart);
        
        // Database 
        $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        $registry->set('db', $db);
        
        // Store
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
        	$store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`ssl`, 'www.', '') = '" . $db->escape('https://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
        } else {
        	$store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`url`, 'www.', '') = '" . $db->escape('http://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
        }
        
        if ($store_query->num_rows) {
        	$config->set('config_store_id', $store_query->row['store_id']);
        } else {
        	$config->set('config_store_id', 0);
        }
        		
        // Settings
        $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' OR store_id = '" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");
        
        foreach ($query->rows as $setting) {
        	if (!$setting['serialized']) {
        		$config->set($setting['key'], $setting['value']);
        	} else {
        		$config->set($setting['key'], unserialize($setting['value']));
        	}
        }
        
        if (!$store_query->num_rows) {
        	$config->set('config_url', HTTP_SERVER);
        	$config->set('config_ssl', HTTPS_SERVER);	
        }
        
        // Url
        $url = new Url($config->get('config_url'), $config->get('config_secure') ? $config->get('config_ssl') : $config->get('config_url'));	
        $registry->set('url', $url);
        
        // Log 
        $log = new Log($config->get('config_error_filename'));
        $registry->set('log', $log);
        
        // Request
        $request = new Request();
        $registry->set('request', $request);
        
        // Response
        $response = new Response();
        $response->addHeader('Content-Type: text/html; charset=utf-8');
        $response->setCompression($config->get('config_compression'));
        $registry->set('response', $response); 
        
        // Cache
        $cache = new Cache();
        $registry->set('cache', $cache); 
        
        // Session
        $session = new Session();
        $registry->set('session', $session);
        
        // Language Detection
        $languages = array();
        
        $query = $db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE status = '1'"); 
        
        foreach ($query->rows as $result) {
        	$languages[$result['code']] = $result;
        }
        
        $detect = '';
        
        if (isset($request->server['HTTP_ACCEPT_LANGUAGE']) && $request->server['HTTP_ACCEPT_LANGUAGE']) { 
        	$browser_languages = explode(',', $request->server['HTTP_ACCEPT_LANGUAGE']);
        	
        	foreach ($browser_languages as $browser_language) {
        		foreach ($languages as $key => $value) {
        			if ($value['status']) {
        				$locale = explode(',', $value['locale']);
        
        				if (in_array($browser_language, $locale)) {
        					$detect = $key;
        				}
        			}
        		}
        	}
        }
        
        if (isset($session->data['language']) && array_key_exists($session->data['language'], $languages) && $languages[$session->data['language']]['status']) {
        	$code = $session->data['language'];
        } elseif (isset($request->cookie['language']) && array_key_exists($request->cookie['language'], $languages) && $languages[$request->cookie['language']]['status']) {
        	$code = $request->cookie['language'];
        } elseif ($detect) {
        	$code = $detect;
        } else {
        	$code = $config->get('config_language');
        }
        
        if (!isset($session->data['language']) || $session->data['language'] != $code) {
        	$session->data['language'] = $code;
        }
        
        if (!isset($request->cookie['language']) || $request->cookie['language'] != $code) {	  
        	setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $request->server['HTTP_HOST']);
        }			
        
        $config->set('config_language_id', $languages[$code]['language_id']);
        $config->set('config_language', $languages[$code]['code']);
        
        // Language	
        $language = new Language($languages[$code]['directory']);
        $language->load($languages[$code]['filename']);	
        $registry->set('language', $language); 
        
        // Document
        $registry->set('document', new Document()); 		
        
        // Customer
        $registry->set('customer', new Customer($registry));
        
        // Affiliate
        $registry->set('affiliate', new Affiliate($registry));
        
        if (isset($request->get['tracking'])) {
        	setcookie('tracking', $request->get['tracking'], time() + 3600 * 24 * 1000, '/');
        }
        		
        // Currency
        $registry->set('currency', new Currency($registry));
        
        // Tax
        $registry->set('tax', new Tax($registry));
        
        // Weight
        $registry->set('weight', new Weight($registry));
        
        // Length
        $registry->set('length', new Length($registry));
        
        // Cart
        $registry->set('cart', new Cart($registry));
        
        //OpenBay Pro
        if (class_exists('Openbay'))
        $registry->set('openbay', new Openbay($registry));
        
        // Encryption
        $registry->set('encryption', new Encryption($config->get('config_encryption')));
        
        return $registry;
    }
    
    public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}
                                            
    /**
     * Верните true если нужно использовать тестовый(stage) сервер
     * @return bool
     */
    public function isTestMode(){
        return  ($this->config->get('ddelivery_work_mode') == 'test');
    }


    /**
     * Возвращает товары находящиеся в корзине пользователя, будет вызван один раз, затем закеширован
     * @return DDeliveryProduct[]
     */
    protected function _getProductsFromCart(){
        $products = array();
        $prods =  $this->cart->getProducts();
        if (is_array($prods) && count($prods)){
            foreach ($prods as $prod){
                $width      = ((double)$prod['width'] > 0)?$this->length->convert($prod['width'],$prod['length_class_id'],$this->config->get('ddelivery_length_class_id')):$this->config->get('ddelivery_width');
                $height     = ((double)$prod['height'] > 0)?$this->length->convert($prod['height'],$prod['length_class_id'],$this->config->get('ddelivery_length_class_id')):$this->config->get('ddelivery_height');
                $length     = ((double)$prod['length'] > 0)?$this->length->convert($prod['length'],$prod['length_class_id'],$this->config->get('ddelivery_length_class_id')):$this->config->get('ddelivery_length');
                $weight     = ((double)$prod['weight'] > 0)?$this->weight->convert($prod['weight'],$prod['weight_class_id'],$this->config->get('ddelivery_weight_class_id')):$this->config->get('ddelivery_weight');
                if ($weight > 0 && (int)$prod['quantity'] >1) 
                    $weight = (double) ($weight / $prod['quantity']);
                
                $sku        = (isset($prod['sku']) && strlen($prod['sku']))?$prod['sku']:$prod['model'];
                $price = $this->currency->convert($prod['price'],$this->default_currency_code,'RUB');
                $products[] = new DDeliveryProduct(
                        $prod['product_id'],	//	int $id id товара в системе и-нет магазина
                        $width,	 //	float $width длинна
                        $height, //	float $height высота
                        $length, //	float $length ширина
                        $weight,	//	float $weight вес кг
                        $price,	//	float $price стоимостьв рублях
                        $prod['quantity'],
                        $prod['name'],	//	string $name Название вещи
                        $sku
                    );        
            }
        }
        return $products;
    }

    /**
     * Настройки базы данных
     * @return array
     */
    public function getDbConfig(){

        return array( 
            'pdo' => new \PDO('mysql:host='.DB_HOSTNAME.';dbname='.DB_DATABASE, DB_USERNAME, DB_PASSWORD, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")),
            'prefix' => 'oc_dd_',
        );

        return array(
            'type' => self::DB_SQLITE,
            'dbPath' => $this->getPathByDB(),
            'prefix' => '',
        );



        return array(
            'type' => self::DB_MYSQL,
            'dsn' => 'mysql:host=localhost;dbname=ddelivery',
            'user' => 'root',
            'pass' => '0',
            'prefix' => '',
        );
    }

    /**
     * Меняет статус внутреннего заказа cms
     *
     * @param $cmsOrderID - id заказа
     * @param $status - статус заказа для обновления
     *
     * @return bool
     */
    public function setCmsOrderStatus($cmsOrderID, $status){
        $q = "UPDATE ". DB_PREFIX."order set order_status_id='$status' where order_id='$cmsOrderID'";
        $this->db->query($q);
    }

    /**
     * Возвращает API ключ, вы можете получить его для Вашего приложения в личном кабинете
     * @return string
     */
    public function getApiKey(){
           return $this->config->get('ddelivery_api');
    }

    /**
     * Должен вернуть url до каталога с статикой
     * @return string
     */
    public function getStaticPath(){
        return 'assets/';
    }

    /**
     * URL до скрипта где вызывается DDelivery::render
     * @return string
     */
    public function getPhpScriptURL(){
        return 'ajax.php';
    }

    /**
     * Возвращает путь до файла базы данных, положите его в место не доступное по прямой ссылке
     * @return string
     */
    public function getPathByDB(){
        return __DIR__.'db/db.sqlite';
    }

    /**
     * Метод будет вызван когда пользователь закончит выбор способа доставки
     *
     * @param \DDelivery\Order\DDeliveryOrder $order
     * @return void
     */
    public function onFinishChange($order){
        $id = $order->localId;
        $this->session->data['ddelivery_order_id'] = $id;

    }
    
    /**
     * Возможность что - нибудь добавить к информации
     * при окончании оформления заказа
     *
     * @param $order DDeliveryOrder
     * @param $resultArray
     */
    public function onFinishResultReturn( $order, $resultArray ){
        $cost = $this->currency->convert($resultArray['clientPrice'],'RUB',$this->default_currency_code);
        $this->session->data['shipping_methods']['ddelivery']['quote']['ddelivery']['cost'] = $cost;
        $this->session->data['shipping_methods']['ddelivery']['quote']['ddelivery']['text'] = $this->currency->format($cost,$this->currency->getCode());
        $this->session->data['shipping_methods']['ddelivery']['quote']['ddelivery']['title'] = 'Сервис доставки DDelivery. '. $resultArray['comment'];
        //$this->session->data['simple']['customer']['firstname'] = $resultArray['userInfo']['firstName'];
        //$this->session->data['simple']['customer']['secondname'] = $resultArray['userInfo']['secondName'];
        //$this->session->data['simple']['customer']['telephone'] = $resultArray['userInfo']['toPhone'];
        //$this->session->data['simple']['customer']['email'] = $resultArray['userInfo']['toEmail'];
        $addr = $resultArray['userInfo']['toStreet'] .' д. '.$resultArray['userInfo']['toHouse'];
        $addr .= ', кв. '.$resultArray['userInfo']['toFlat'];
        //$this->session->data['simple']['customer']['shipping_address']['address_1'] =  $addr;
        //$this->session->data['simple']['customer']['payment_address']['address_1'] =  $addr;
        $this->session->data['ddelivery']['comment'] = $resultArray['comment'];
        $this->session->data['ddelivery']['cost'] = (double)$resultArray['clientPrice'];
        return $resultArray;
    }

    /**
     * Какой процент от стоимости страхуется
     * @return float
     */
    public function getDeclaredPercent(){
        return (double)$this->config->get('ddelivery_insur');
    }

    /**
     * Должен вернуть те компании которые  показываются в курьерке
     *
     * @return int[]
     */
    public function filterCompanyPointCourier(){
        $return = $this->config->get('ddelivery_cur_companies');
        if (!is_array($return)) $return = array();
        return $return;
        //return array	(4,21,29,23,27,28,20,30,31,11,16,22,17,3,14,1,13,18,6,
        //                 26,25,24,7,35,36,37,39,40,42,43,44,45,46,47,48,49);
    }

    /**
     * Должен вернуть те компании которые  показываются в самовывозе
     *
     * @return int[]
     */
    public function filterCompanyPointSelf(){
        $return = $this->config->get('ddelivery_pvz_companies');
        if (!is_array($return)) $return = array();
        return $return;
    }

    /**
     * Возвращаем способ оплаты  c наложенным платежем для курьера
     *
     * либо константа \DDelivery\Adapter\PluginFilters::PAYMENT_PREPAYMENT - если способ облаты - предоплата,
     * либо константа \DDelivery\Adapter\PluginFilters::PAYMENT_POST_PAYMENT -  если способ оплаты оплата при получении
     *
     * @param $order DDeliveryOrder
     *
     * @return int
     */
    public function filterPointByPaymentTypeCourier( $order ){
        if ($order->paymentVariant == $this->config->get('ddelivery_cur_payment'))
            return \DDelivery\Adapter\PluginFilters::PAYMENT_POST_PAYMENT;
        else return \DDelivery\Adapter\PluginFilters::PAYMENT_PREPAYMENT;
    }

    /**
     * Возвращаем способ оплаты  c наложенным платежем для самовывоза
     *
     * либо константа \DDelivery\Adapter\PluginFilters::PAYMENT_PREPAYMENT - если способ облаты - предоплата,
     * либо константа \DDelivery\Adapter\PluginFilters::PAYMENT_POST_PAYMENT -  если способ оплаты оплата при получении
     *
     * @param $order DDeliveryOrder
     *
     * @return int
     */
    public function filterPointByPaymentTypeSelf( $order ){
        if ($order->paymentVariant == $this->config->get('ddelivery_pvz_payment'))
            return \DDelivery\Adapter\PluginFilters::PAYMENT_POST_PAYMENT;
        else return \DDelivery\Adapter\PluginFilters::PAYMENT_PREPAYMENT;
    }

    /**
     * Если true, то не учитывает цену забора
     * @return bool
     */
    public function isPayPickup(){
        return ((int)$this->config->get('ddelivery_zabor') == 1);
    }

    /**
     * Метод возвращает настройки оплаты фильтра которые должны быть собраны из админки
     *
     * @return array
     */
    public function getIntervalsByPoint(){
        //return array();
        $return = array();
        $min = $this->config->get('ddelivery_price_from');
        $max = $this->config->get('ddelivery_price_to');
        $pay_type = $this->config->get('ddelivery_pay_type');
        $summ = $this->config->get('ddelivery_summ');
        for ($i = 0; $i<3; $i++)
            $return[] = array(
                'min' => $min[$i], 
                'max'=>$max[$i], 
                'type'=>$pay_type[$i], 
                'amount'=>$summ[$i]);
        return $return;
        return array(
            array('min' => 0, 'max'=>100, 'type'=>self::INTERVAL_RULES_MARKET_AMOUNT, 'amount'=>30),
            array('min' => 100, 'max'=>200, 'type'=>self::INTERVAL_RULES_CLIENT_ALL, 'amount'=>60),
            array('min' => 200, 'max'=>5000, 'type'=>self::INTERVAL_RULES_MARKET_PERCENT, 'amount'=>50),
            array('min' => 5000, 'max'=>null, 'type'=>self::INTERVAL_RULES_MARKET_ALL),
        );
    }

    /**
     * Тип округления
     * @return int
     */
    public function aroundPriceType(){
        return (int)$this->config->get('ddelivery_round'); //self::AROUND_ROUND; // self::AROUND_FLOOR, self::AROUND_CEIL
    }

    /**
     * Шаг округления
     * @return float
     */
    public function aroundPriceStep(){
        return $this->config->get('ddelivery_round_step'); // До 50 копеек
    }

    /**
     * описание собственных служб доставки
     * @return string
     */
    public function getCustomPointsString(){
        return '';
    }

    /**
     * Если вы знаете имя покупателя, сделайте чтобы оно вернулось в этом методе
     * @return string|null
     */
    public function getClientFirstName() {
        return $this->user['lastname'] .' ' .$this->user['firstname'];
    }

    /**
     * Если вы знаете фамилию покупателя, сделайте чтобы оно вернулось в этом методе
     * @return string|null
     */
    public function getClientLastName() {
        return '';
    }

    /**
     * Если вы знаете телефон покупателя, сделайте чтобы оно вернулось в этом методе. 11 символов, например 79211234567
     * @return string|null
     */
    public function getClientPhone() {
        $phone = $this->user['telephone'];
        $phone = str_replace(array('+','-','(',')',' '),'',$phone);
        $phone = '+7'.substr($phone,-10);
        //if (strlen($phone) !== 11) $this->address_correct = false;
        return $phone;
    }
    
    
    public function getClientEmail(){
        return $this->user['email'];
    }

    /**
     * Верни массив Адрес, Дом, Корпус, Квартира. Если не можешь можно вернуть все в одном поле и настроить через get*RequiredFields
     * @return string[]
     */
    public function getClientAddress() {
        $addr = $this->user['address'];
        if ($addr) $ar = explode(',',$addr);
        else $ar = array();
        $return = array();
        $street = '';
        $house  = '';
        $corp  = '';
        $flat  = '';
        if (count($ar)){
            foreach($ar as $k => $v){
                $ar[$k] = trim($v);
                if ($k >0 && strpos($v,'корп'))
                    $corp = trim($v);
                if ($k >0 && (strpos($v,'кв') || strpos($v,'оф')))
                    $flat = trim($v);
                }
        }else return $addr;
        $pat_street = "/^(.+)\s+(\d+)$/is";
        if (preg_match($pat_street,$ar[0], $matches)){
            if (isset($matches[1]))
                $street = $matches[1];
            if (isset($matches[2]))
                $house = $matches[2];
        }
        else {  
            if (isset($ar[0]))
                $street = $ar[0];
            if (isset($ar[1]))
                $house = $ar[1];
            }
        if (trim($street))
            $street = trim(str_replace(array('улица','ул.'),'',$street));
        else $street = '--';
        $street = trim($street);
        if ($house)
            $house = trim(str_replace(array('дом','д.','д'),'',$house));
        else $house = '--';
        if ($corp)
            $corp = trim(str_replace(array('корпус','корп.','корп'),'',$corp));
        else $corp = '--';
        if ($flat)
            $flat = trim(str_replace(array('квартира','кв.','кв',),'',$flat));
        else $flat = '--';    
        //if (strlen($street)>0 && strlen($house)>0 && strlen($flat)>0) 
          //  $this->address_correct = true;
            
        $return[] = $street;
        $return[] = $house;
        $return[] = $corp;
        $return[] = $flat;
        
        return $return;
    }

    /**
     * Верните id города в системе DDelivery
     * @return int
     */
    public function getClientCityId(){
        // Если нет информации о городе, оставьте вызов родительского метода.
        return parent::getClientCityId();
    }

    /**
     * Возвращает поддерживаемые магазином способы доставки
     * @return array
     */
    public function getSupportedType(){
        switch ((int)$this->config->get('ddelivery_enabled_type')){
            case 1: return array(
                                   \DDelivery\Sdk\DDeliverySDK::TYPE_COURIER,
                                    \DDelivery\Sdk\DDeliverySDK::TYPE_SELF
                                ); 
            break;
            case 2: return array(
                                    \DDelivery\Sdk\DDeliverySDK::TYPE_SELF
                                ); 
            break;
            case 3: return array(
                                   \DDelivery\Sdk\DDeliverySDK::TYPE_COURIER,
                                ); 
            break;
            default: return array(
                                   \DDelivery\Sdk\DDeliverySDK::TYPE_COURIER,
                                    \DDelivery\Sdk\DDeliverySDK::TYPE_SELF
                                ); 
        }
        
    }

    /**
     *
     * Перед возвратом точек самовывоза фильтровать их по определенным правилам
     *
     * @param $companyArray
     * @param DDeliveryOrder $order
     * @return mixed
     */
    public function finalFilterSelfCompanies( $companyArray, $order ){
        $companyArray = parent::finalFilterSelfCompanies( $companyArray, $order );
        return $companyArray;
    }

    /**
     *
     *  Перед возвратом компаний курьерок фильтровать их по определенным правилам
     *
     * @param $companyArray
     * @param DDeliveryOrder $order
     * @return mixed
     */
    public function finalFilterCourierCompanies( $companyArray, $order ){
        $companyArray = parent::finalFilterCourierCompanies( $companyArray, $order );
        return $companyArray;
    }

    /**
     * Получить доступные способы оплаты для Самовывоза ( можно анализировать содержимое order )
     * @param $order DDeliveryOrder
     * @return array
     */
    public function getSelfPaymentVariants( $order ){
        return array();
    }

    /**
     * Получить доступные способы оплаты для курьера ( можно анализировать содержимое order )
     * @param $order DDeliveryOrder
     * @return array
     */
    public function getCourierPaymentVariants( $order ){
        return array();
    }

    /**
     *
     * Используется при отправке заявки на сервер DD для указания стартового статуса
     *
     * Если true то заявка в сервисе DDelivery будет выставлена в статус "Подтверждена",
     * если false то то заявка в сервисе DDelivery будет выставлена в статус "В обработке"
     *
     * @param mixed $localStatus
     *
     * @return bool
     */
    public function isConfirmedStatus( $localStatus ){
        return ($localStatus == $this->config->get('ddelivery_status_confirmed'));
    }
    
    /**
     * При отправке заказа на сервер дделивери идет
     * проверка  статуса  выставленого в настройках
     *
     * @param mixed $cmsStatus
     * @return bool|void
     */
    public function isStatusToSendOrder( $cmsStatus ){
        return ($cmsStatus == $this->config->get('ddelivery_status_confirmed') || 
                    $cmsStatus == $this->config->get('ddelivery_status_in_progress'));
    }
    
    public function isUserInfoCorrect(){
        $address = $this->getClientAddress();
        if (count($address)==4 && trim($this->getClientFirstName()) && 
            strlen(trim($this->getClientPhone())) == 12 && 
            trim($address[0]) && trim($address[1]) && trim($address[3])
           )
            
            return true;
        
        else 
            return false;
    }


    /**
     * Возвращает бинарную маску обязательных полей для курьера
     * Если редактирование не включено, но есть обязательность то поле появится
     * Если редактируемых полей не будет то пропустим шаг
     * @return int
     */
    public function getCourierRequiredFields(){
        // ВВести все обязательно, кроме корпуса
        //print_r('show_form: '.$this->config->get('ddelivery_show_contact_form').' '.$this->address_correct);
        if ((int)$this->config->get('ddelivery_show_contact_form') == 0 && $this->isUserInfoCorrect()) return false;
        return self::FIELD_EDIT_FIRST_NAME | self::FIELD_REQUIRED_FIRST_NAME
        | self::FIELD_EDIT_PHONE | self::FIELD_REQUIRED_PHONE
        | self::FIELD_EDIT_ADDRESS | self::FIELD_REQUIRED_ADDRESS
        | self::FIELD_EDIT_ADDRESS_HOUSE | self::FIELD_REQUIRED_ADDRESS_HOUSE
        | self::FIELD_EDIT_ADDRESS_HOUSING
        | self::FIELD_EDIT_ADDRESS_FLAT | self::FIELD_REQUIRED_ADDRESS_FLAT | self::FIELD_EDIT_EMAIL;
    }

    /**
     * Возвращает бинарную маску обязательных полей для пунктов самовывоза
     * Если редактирование не включено, но есть обязательность то поле появится
     * Если редактируемых полей не будет то пропустим шаг
     * @return int
     */
    public function getSelfRequiredFields(){
        if ((int)$this->config->get('ddelivery_show_contact_form') == 0 && $this->isUserInfoCorrect()) return false;
        return self::FIELD_EDIT_FIRST_NAME | self::FIELD_REQUIRED_FIRST_NAME
        | self::FIELD_EDIT_PHONE | self::FIELD_REQUIRED_PHONE | self::FIELD_EDIT_EMAIL;
    }

    /**
     * Получить название шаблона для сдк ( разные цветовые схемы )
     *
     * @return string
     */
    public function getTemplate(){
        return $this->config->get('ddelivery_theme');
    }
    
    public function getUserFields(){
        $return = array(
            'lastname' => '',
            'firstname' => '',
            'email' => '',
            'telephone' => '',
            'address' => '',
        );
        
        if (isset($this->session->data['simple'])){
            /**
             * Simple 3
             */
            if (isset($this->session->data['simple']['checkout_customer'])){
                $lastname = '';
                $firstname = '';
                $email = '';
                $telephone = '';
                $address = '';
                foreach($this->session->data['simple']['checkout_customer'] as $k => $v){
                    //echo "$k => $v\n";
                    if (strpos($k,'lastname') !== false)
                        $lastname = $k;
                    if (strpos($k,'firstname') !== false)
                        $firstname = $k;
                    if (strpos($k,'email') !== false)
                        $email = $k;
                    if (strpos($k,'telephone') !== false)
                        $telephone = $k;
                    if (strpos($k,'address_1') !== false)
                        $address = $k;
                }
                if (isset($this->session->data['simple']['checkout_customer'][$lastname]))
                    $return['lastname'] = $this->session->data['simple']['checkout_customer'][$lastname];
                if (isset($this->session->data['simple']['checkout_customer'][$firstname]))
                    $return['firstname'] = $this->session->data['simple']['checkout_customer'][$firstname];
                if (isset($this->session->data['simple']['checkout_customer'][$email]))
                    $return['email'] = $this->session->data['simple']['checkout_customer'][$email];
                if (isset($this->session->data['simple']['checkout_customer'][$telephone]))
                    $return['telephone'] = $this->session->data['simple']['checkout_customer'][$telephone];
                if (isset($this->session->data['simple']['checkout_customer'][$address]))
                    $return['address'] = $this->session->data['simple']['checkout_customer'][$address];
            }
            /**
             * Simple 4
             */
            if (isset($this->session->data['simple']['customer'])){
                
                if (isset($this->session->data['simple']['customer']['register']) && 
                    (int)$this->session->data['simple']['customer']['register'] == 0 && 
                    isset($this->session->data['simple']['customer']['email']))  
                    $return['email'] = $this->session->data['simple']['customer']['email'];
                else 
                    $return['email'] = $this->customer->getEmail();
                if (isset($this->session->data['simple']['customer']['lastname']))    
                    $return['lastname'] = $this->session->data['simple']['customer']['lastname'];
                if (isset($this->session->data['simple']['customer']['firstname'])) 
                    $return['firstname'] = $this->session->data['simple']['customer']['firstname'];
                if (isset($this->session->data['simple']['customer']['telephone'])) 
                    $return['telephone'] = $this->session->data['simple']['customer']['telephone'];
                if (isset($this->session->data['simple']['shipping_address']['address_1']) && $this->session->data['simple']['shipping_address']['address_1'])
                    $return['address'] = $this->session->data['simple']['shipping_address']['address_1'];
                elseif (isset($this->session->data['simple']['payment_address']['address_1']) && $this->session->data['simple']['payment_address']['address_1']) 
                    $return['address'] = $this->session->data['simple']['payment_address']['address_1'];
            }
        }else{
            /**
             * No Simple Guest
             */
            if(isset($this->session->data['guest'])){
                if (isset($this->session->data['guest']['email']))
                    $return['email'] = $this->session->data['guest']['email'];
                if (isset($this->session->data['guest']['lastname']))
                    $return['lastname'] = $this->session->data['guest']['lastname'];
                if (isset($this->session->data['guest']['firstname']))
                    $return['firstname'] = $this->session->data['guest']['firstname'];
                if (isset($this->session->data['guest']['telephone']))
                    $return['telephone'] = $this->session->data['guest']['telephone'];
                if (isset($this->session->data['guest']['shipping']['address_1']) && isset($this->session->data['guest']['shipping']['address_2']))
                    $return['address'] = trim($this->session->data['guest']['shipping']['address_1'] . ' '. $this->session->data['guest']['shipping']['address_2']);
                elseif (isset($this->session->data['guest']['payment']['address_1']) && isset($this->session->data['guest']['payment']['address_2']))
                    $return['address'] = trim($this->session->data['guest']['payment']['address_1'] . ' '. $this->session->data['guest']['payment']['address_2']);
                
            }
            if ($this->customer->isLogged()){
                $return['email']     = $this->customer->getEmail();
                $return['lastname']  = $this->customer->getLastName();
                $return['firstname'] = $this->customer->getFirstName();
                $return['telephone'] = $this->customer->getTelephone();
                $this->load->model('account/address');;
                if (isset($this->session->data['shipping_address_id'])){
                    $address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);
                }elseif(isset($this->session->data['payment_address_id'])){
                    $address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
                }elseif($this->customer->getAddressId()){
                    $address = $this->model_account_address->getAddress($this->customer->getAddressId());
                }
                
                if (isset($address['address_1']) && isset($address['address_2']))
                    $return['address'] = trim($address['address_1']. ' '. $address['address_2']);
                
            }
            
        }
        return $return;
    }
    
    

}