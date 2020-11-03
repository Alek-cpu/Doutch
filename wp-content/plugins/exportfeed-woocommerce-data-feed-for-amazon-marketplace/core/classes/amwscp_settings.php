<?php

require_once dirname(__FILE__) . '/../data/amwscp_woo_essentials_data_store.php';

class Amwscp_settings extends Amwscp_Woo_Essentials_Data_Store
{

    private $table;

    private $data = array();

    function __construct()
    {
        parent::__construct();
        global $wpdb;
        $this->db = $wpdb;
    }

    public function init()
    {
        $this->data['view'] = 'settings';
        parent::setOrderEssentials();
        $this->data['woo_order_status'] = parent::getOrderEssentials();
        $this->data['amazon_order_status'] = $this->get_amazon_order_statuses();
        $this->load()->_view($this->data);
    }

    public function get_amazon_order_statuses()
    {
        $statuses = array(
            'Pending' => 'Pending',
            'Unshipped' => 'Unshipped',
            'PartiallyShipped' => 'PartiallyShipped',
            'Shipped' => 'Shipped',
            'Refund applied' => 'Refund applied',
            'Canceled' => 'Canceled',
            'Completed' => 'Completed',
            'universal_status' => 'Any Other'
        );
        return $statuses;
    }

    public function _view(array $data)
    {
        $viewpath = dirname(__FILE__) . '/../../amazon-views/settings/' . $data['view'] . '.php';
        if (file_exists($viewpath)) {
            extract($data);
            if (realpath($viewpath)) {
                ob_start();
                include_once realpath($viewpath);
                ob_end_flush();
            }
        }
    }


    private function load(){
        return new Amwscp_settings();
    }

}