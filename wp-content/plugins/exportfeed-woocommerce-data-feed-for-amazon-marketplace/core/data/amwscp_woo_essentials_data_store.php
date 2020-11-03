<?php

Class Amwscp_Woo_Essentials_Data_Store{

    public $orderessentials;

    public $orderstatus;

    function __construct()
    {

    }

    public function setOrderEssentials(){
        if(function_exists('wc_get_order_statuses')){
           $orderStatus = wc_get_order_statuses();
        }else{
            $orderStatus = array(
                'wc-pending' => 'Pending payment',
                'wc-processing' => 'Processing',
                'wc-on-hold' => 'On hold',
                'wc-completed' => 'Completed',
                'wc-cancelled' => 'Cancelled',
                'wc-refunded' => 'Refunded',
                'wc-failed' => 'Failed'
            );
        }
        $this->orderstatus = $orderStatus;
    }

    public function getOrderEssentials(){
        return $this->orderstatus;
    }
}