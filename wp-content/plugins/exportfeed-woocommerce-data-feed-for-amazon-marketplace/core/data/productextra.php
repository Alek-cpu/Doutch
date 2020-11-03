<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('AMWSCP_PProductSupplementalData')) {
    class AMWSCP_PProductSupplementalData
    {

        public $data = array();
        public $initialized = false;
        public $taxonomy = '';

        public function __construct($taxonomy)
        {
            $this->taxonomy = $taxonomy;
        }

        public function check($attributeName, $item)
        {

            if (!$this->initialized)
                $this->getData();

            foreach ($this->data as $datum) {
                if ($datum->id == $item->id) {
                    if (strlen($datum->name) > 0)
                        $item->attributes[$attributeName] = $datum->name;
                    break;
                }
            }

        }

        public function getData()
        {
            $this->initialized = true;
            global $amwcore;
            $proc = 'getData' . $amwcore->callSuffix;
            return $this->$proc();
        }

        public function getDataJ()
        {
        }

        public function getDataJH()
        {
        }

        public function getDataJS()
        {
        }

        public function getDataW()
        {

            global $wpdb;
            $sql = "
			SELECT id, post_title, post_name, $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name
			FROM $wpdb->posts
			LEFT JOIN $wpdb->term_relationships on ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
			WHERE $wpdb->posts.post_type='product'
			AND $wpdb->term_taxonomy.taxonomy = '$this->taxonomy'
		";
            $this->data = $wpdb->get_results($sql);

        }

        public function getDataWe()
        {

            global $wpdb;
            $sql = "
			SELECT id, post_title, post_name, $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->term_taxonomy.taxonomy, $wpdb->terms.name
			FROM $wpdb->posts
			LEFT JOIN $wpdb->term_relationships on ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			LEFT JOIN $wpdb->terms on ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
			WHERE $wpdb->posts.post_type='wpsc-product'
			AND $wpdb->term_taxonomy.taxonomy = '$this->taxonomy'
		";
            $this->data = $wpdb->get_results($sql);

        }

    }
}