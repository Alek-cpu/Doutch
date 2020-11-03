<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class AMWSCP_PFeedActivityLog
{

    function __construct($feedIdentifier = '')
    {
        //When instantiated (as opposed to static calls) it means we need to log the phases
        //therefore, save the feedIdentifier
        $this->feedIdentifier = $feedIdentifier;
    }

    function __destruct()
    {
        global $amwcore;
        if (!empty($amwcore) && (strlen($amwcore->callSuffix) > 0)) {
            $deleteLogData = 'deleteLogData' . $amwcore->callSuffix;
            $this->$deleteLogData();
        }
    }

    /********************************************************************
     * Add a record to the activity log for "Manage Feeds"
     ********************************************************************/

    private static function addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword)
    {
        global $amwcore;
        $addNewFeedData = 'addNewFeedData' . $amwcore->callSuffix;
        AMWSCP_PFeedActivityLog::$addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword);
    }

    private static function addNewFeedDataJ($category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();
        $db = JFactory::getDBO();

        $sql = 'SELECT COUNT(*) FROM #__cartproductfeed_feeds';
        $db->setQuery($sql);
        $db->query();
        $ordering = $db->loadResult() + 1;

        $newData = new stdClass();
        $newData->title = $file_name;
        $newData->category = $category;
        $newData->remote_category = $remote_category;
        $newData->filename = $file_name;
        $newData->url = $file_path;
        $newData->type = $providerName;
        $newData->product_count = $productCount;
        $newData->ordering = $ordering;
        $newData->created = $date->toSql();
        $newData->created_by = $user->get('id');
        //$newData->catid int,
        $newData->modified = $date->toSql();
        $newData->modified_by = $user->get('id');
        //$productCount
        $db->insertObject('#__cartproductfeed_feeds', $newData, 'id');
    }

    private static function addNewFeedDataJH($category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {
        AMWSCP_PFeedActivityLog::addNewFeedDataJ($category, $remote_category, $file_name, $file_path, $providerName, $productCount);
    }

    private static function addNewFeedDataJS($category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {

        global $amwcore;
        $shopID = $amwcore->shopID;

        $date = JFactory::getDate();
        $user = JFactory::getUser();
        $db = JFactory::getDBO();

        $sql = 'SELECT COUNT(*) FROM #__cartproductfeed_feeds';
        $db->setQuery($sql);
        $db->query();
        $ordering = $db->loadResult() + 1;

        $newData = new stdClass();
        $newData->title = substr($file_name, 3);
        $newData->category = $category;
        $newData->remote_category = $remote_category;
        $newData->filename = $file_name;
        $newData->url = $file_path;
        $newData->type = $providerName;
        $newData->product_count = $productCount;
        $newData->ordering = $ordering;
        $newData->created = $date->toSql();
        $newData->created_by = $user->get('id');
        //$newData->catid int,
        $newData->modified = $date->toSql();
        $newData->modified_by = $user->get('id');
        $newData->shop_id = $shopID;
        //$productCount
        $db->insertObject('#__cartproductfeed_feeds', $newData, 'id');
    }

    private static function addNewFeedDataW($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category)
    {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'amwscp_feeds';
        $sql = "INSERT INTO $feed_table(`category`, `remote_category`,`amazon_category`, `filename`, `url`, `type`, `product_count`,`previous_product_count`) VALUES ('$category','$remote_category','$amazon_category','$file_name','$file_path','$providerName', '$productCount','$productCount')";
        $wpdb->query($sql);
    }

    private static function addNewFeedDataWe($category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {
        AMWSCP_PFeedActivityLog::addNewFeedDataW($category, $remote_category, $file_name, $file_path, $providerName, $productCount);
    }

    /********************************************************************
     * Search the DB for a feed matching filename / providerName
     ********************************************************************/

    public static function feedDataToID($file_name, $providerName)
    {
        global $amwcore;
        $feedDataToID = 'feedDataToID' . $amwcore->callSuffix;
        return AMWSCP_PFeedActivityLog::$feedDataToID($file_name, $providerName);
    }

    private static function feedDataToIDJ($file_name, $providerName)
    {
        $db = JFactory::getDBO();
        $query = "
			SELECT id
			FROM #__cartproductfeed_feeds
			WHERE filename='$file_name' AND type='$providerName'";
        $db->setQuery($query);
        $db->query();
        $result = $db->loadObject();
        if (!$result)
            return -1;

        return $result->id;

    }

    private static function feedDataToIDJH($file_name, $providerName)
    {

        return AMWSCP_PFeedActivityLog::feedDataToIDJ($file_name, $providerName);

    }

    private static function feedDataToIDJS($file_name, $providerName)
    {

        global $amwcore;
        $shopID = $amwcore->shopID;

        $db = JFactory::getDBO();
        $db->setQuery('
			SELECT id
			FROM #__cartproductfeed_feeds
			WHERE (filename=' . $db->quote($file_name) . ') AND (type=' . $db->quote($providerName) . ') AND (shop_id = ' . (int)$shopID . ')');
        $result = $db->loadObject();
        if (!$result)
            return -1;

        return $result->id;

    }

    private static function feedDataToIDW($file_name, $providerName)
    {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'amwscp_feeds';
        $sql = "SELECT * from $feed_table WHERE `filename`='$file_name' AND `type`='$providerName'";
        $list_of_feeds = $wpdb->get_results($sql, ARRAY_A);
        if ($list_of_feeds) {
            return $list_of_feeds[0]['id'];
        } else {
            return -1;
        }
    }

    private static function feedDataToIDWe($file_name, $providerName)
    {
        return AMWSCP_PFeedActivityLog::feedDataToIDW($file_name, $providerName);
    }

    public static function recordFeedId($params)
    {
        $id = AMWSCP_PFeedActivityLog::feedDataToID($params['file_name'], $params['providerName']);
        if ($id > 0) {
            return $id;
        } else {
            global $wpdb;
            $table = $wpdb->prefix . 'amwscp_feeds';
            $data = array(
                'category' => $params['category'],
                'remote_category' => $params['remote_category'],
                'amazon_category' => $params['amazon_category'],
                'filename' => $params['file_name'],
                'url' => $params['file_path'],
                'type' => $params['providerName'],
                'product_count' => 0,
                'variation_theme' => $params['variationtheme'],
                'feed_product_type' => $params['feed_product_type'],
                'recommended_browse_nodes' => $params['recommended_browse_nodes'],
                'item_type_keyword' => $params['item_type_keyword'],
            );
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }
    }

    public static function recordProductinFeed($product, $feedId)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'amwscp_feed_product_record';
        $data = array(
            'product_id' => $product->id,
            'sku' => $product->attributes['sku'],
            'feed_id' => $feedId,
            'product_name' => $product->attributes['title'],
            'stock_quantity' => $product->attributes['stock_quantity']
        );
        $check = self::checkProductExistence($data);
        if ($check) {
            $wpdb->update($table, $data, array('id' => $check));
            return $check;
        } else {
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

    }

    public static function checkProductExistence($params)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'amwscp_feed_product_record';
        $result = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table where product_id = %d AND feed_id=%d", array($params['product_id'], $params['feed_id'])));
        if ($result) {
            return $result->id;
        }
        return false;
    }

    /********************************************************************
     * Called from outside... this class has to make sure the feed shows under "Manage Feeds"
     ********************************************************************/

    public static function updateFeedList($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword)
    {
        $id = AMWSCP_PFeedActivityLog::feedDataToID($file_name, $providerName);
        if ($id == -1)
            AMWSCP_PFeedActivityLog::addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword);
        else
            AMWSCP_PFeedActivityLog::updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword);
    }

    /********************************************************************
     * Update a record in the activity log
     ********************************************************************/

    private static function updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword)
    {
        global $amwcore;
        $updateFeedData = 'updateFeedData' . $amwcore->callSuffix;
        AMWSCP_PFeedActivityLog::$updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword);
    }

    private static function updateFeedDataJ($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {

        $date = JFactory::getDate();
        $user = JFactory::getUser();
        $db = JFactory::getDBO();

        $newData = new stdClass();
        $newData->id = $id;
        $newData->category = $category;
        $newData->remote_category = $remote_category;
        $newData->filename = $file_name;
        $newData->url = $file_path;
        $newData->type = $providerName;
        $newData->product_count = $productCount;
        $newData->modified = $date->toSql();
        $newData->modified_by = $user->get('id');
        //$productCount
        $db->updateObject('#__cartproductfeed_feeds', $newData, 'id');
    }

    private static function updateFeedDataJH($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {

        AMWSCP_PFeedActivityLog::updateFeedDataJ($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount);

    }

    private static function updateFeedDataJS($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {

        $date = JFactory::getDate();
        $user = JFactory::getUser();
        $db = JFactory::getDBO();

        //global $amwcore;
        //$shopID = $amwcore->shopID;

        $newData = new stdClass();
        $newData->id = $id;
        $newData->category = $category;
        $newData->remote_category = $remote_category;
        $newData->filename = $file_name;
        $newData->url = $file_path;
        $newData->type = $providerName;
        $newData->product_count = $productCount;
        $newData->modified = $date->toSql();
        $newData->modified_by = $user->get('id');
        //$newData->shop_id = $shopID;
        //$productCount

        $db->updateObject('#__cartproductfeed_feeds', $newData, 'id');

    }

    private static function updateFeedDataW($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword)
    {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'amwscp_feeds';
        $sql = "
			UPDATE $feed_table 
			SET 
				`category`='$category',
				`remote_category`='$remote_category',
				`amazon_category`=\"$amazon_category\",
				`filename`='$file_name',
				`url`='$file_path',
				`type`='$providerName',
				`product_count`='$productCount',
				`variation_theme`='$variationtheme',
				`feed_product_type`='$feed_product_type',
				`recommended_browse_nodes`='$recommended_browse_nodes',
				`item_type_keyword`='$item_type_keyword'
			WHERE `id`=$id";
        $wpdb->query($sql);
    }

    private static function updateFeedDataWe($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount,$amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword)
    {
        AMWSCP_PFeedActivityLog::updateFeedDataW($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount,$amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword);
    }

    /********************************************************************
     * Save a Feed Phase
     ********************************************************************/

    function logPhase($activity)
    {
        global $amwcore;
        $amwcore->settingSet('cp_feedActivity_' . $this->feedIdentifier, $activity);
    }

    /********************************************************************
     * Remove Log info
     ********************************************************************/

    function deleteLogDataJ()
    {

    }

    function deleteLogDataJH()
    {

    }

    function deleteLogDataJS()
    {

    }

    function deleteLogDataW()
    {
        delete_option('cp_feedActivity_' . $this->feedIdentifier);
    }

    function deleteLogDataWe()
    {
        delete_option('cp_feedActivity_' . $this->feedIdentifier);
    }

}

?>
