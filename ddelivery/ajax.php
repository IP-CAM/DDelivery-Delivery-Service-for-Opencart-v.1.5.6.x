<?php
//ini_set("display_errors", "1");
error_reporting(E_ERROR);
header('Content-Type: text/html; charset=utf-8');
include_once(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'application', 'bootstrap.php')));
include_once("IntegratorShop.php");

use DDelivery\DDeliveryUI;

try{
    // В зависимости от параметров может выводить полноценный html или json
    if (isset($_GET['compareStatuses'])){
        $IntegratorShop = new IntegratorShop(true);
        $ddeliveryUI = new DDeliveryUI($IntegratorShop);
        $orders = $ddeliveryUI->getAllOrders();
        if (is_array($orders) && count($orders)){
            foreach($orders as $ddorder){
                $order = $ddeliveryUI->initOrder($ddorder['id']);
                if ($order->ddeliveryID){
                    $ddStatus = $ddeliveryUI->getDDOrderStatus($order->ddeliveryID);
                    if ((int)$ddStatus >0 ){ 
                        //echo '<pre>'.print_r($order,1).'</pre>';
                        $localStatus = $ddeliveryUI->getLocalStatusByDD($ddStatus);
                        $oldDescr = $ddeliveryUI->getDDStatusDescription($order->ddStatus);
                        $newDescr = $ddeliveryUI->getDDStatusDescription($ddStatus);
                        
                        echo "id: $order->localId ddId: $order->ddeliveryID cmsId: $order->shopRefnum oldDDstatus: $order->ddStatus $oldDescr newDDstatus: $ddStatus $newDescr oldLocalStatus: $order->localStatus newLocalStatus: $localStatus<br />";
                        $order->ddStatus = $ddStatus;
                        $order->localStatus = $localStatus;
                        $cmsID = (int)$order->shopRefnum;
                        
                        $ddeliveryUI->saveFullOrder($order);
                        $IntegratorShop->setCmsOrderStatus($cmsID,$localStatus);
                    }
                    else {
                        $order->ddeliveryID = 0;
                        $ddeliveryUI->saveFullOrder($order);
                    }
                }
            }
        }
        
    }
    else  {
        $IntegratorShop = new IntegratorShop(false);
        $ddeliveryUI = new DDeliveryUI($IntegratorShop);
    
        $ddeliveryUI->render(isset($_REQUEST) ? $_REQUEST : array());
    }
}catch ( \DDelivery\DDeliveryException $e ){
    $IntegratorShop->logMessage($e);
}