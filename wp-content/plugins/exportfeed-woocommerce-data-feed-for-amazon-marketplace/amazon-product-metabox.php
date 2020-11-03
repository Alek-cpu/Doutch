<?php
class AMWSCPF_ProductMerabox {
    function __construct()
    {
        add_action('add_meta_boxes',[&$this,'add_meta_box']);
        add_action('woocommerce_process_product_meta',[&$this,'save_meta_box'],0,2);
    }

    function add_meta_box() {

        ?>
        <style type="text/css">
            /* standard input fields */
            #amwscpf-amazon-options label,
            #amwscpf-amazon-advanced label {
                float: left;
                width:25%;
                line-height: 2em;
            }
            #amwscpf-amazon-options input,
            #amwscpf-amazon-advanced input {
                width:70%;
            }

            /* radio buttons */
            #amwscpf-amazon-options label ul.wc-radios label,
            #amwscpf-amazon-advanced label ul.wc-radios label {
                float: right;
                width:auto;
            }
            #amwscpf-amazon-options input.select,
            #amwscpf-amazon-advanced input.select {
                width:auto;
            }

            #amwscpf-amazon-options .description,
            #amwscpf-amazon-advanced .description {
                clear: both;
                margin-left: 25%;
            }
            #wpl_amazon_product_description {
                height: 10em;
            }
        </style>
        <?php
        $title = __('Amazon Basics','amwscpf');
        add_meta_box(
            'amwscpf-amazon-options',
            $title,
            [&$this,'item_description'],
            'product',
            'normal',
            'default'
        );
    }

    function item_type_details(){
        global $wpdb, $woocommerce,$post;

        $this->addInLineJS();
    }

    function save_meta_box( $post_id, $post) {
        #echo "<pre>";print_r($_REQUEST);die;

        // check nonce
        if ( ! isset( $_POST['amwscpf_save_product_nonce'] ) || ! wp_verify_nonce( $_POST['amwscpf_save_product_nonce'], 'amwscpf_save_product' ) ) return;

        // adding meta fields
        foreach ($_REQUEST as $key => $value){
            preg_match('/amwscpf_/',$key,$matches);
            if(isset($matches[0]) && $key !== 'amwscpf_save_product_nonce'){
                $option_name = '_'.$key;
                update_post_meta($post_id,$option_name, esc_attr(@$_REQUEST[$key]));
            }
        }
    }

    function item_description(){
        wp_nonce_field( 'amwscpf_save_product', 'amwscpf_save_product_nonce' );
        global $woocommerce, $post, $wpdb;

        $this->addInLineJS();
//        wp_nonce_field('amazon-exportfeed','amazon-exportfeed-nonce');
        // select template
        $value_table = $wpdb->prefix."amwscp_template_values";
        $template_table = $wpdb->prefix."amwscp_amazon_templates";

        $template_list = $wpdb->get_results("SELECT * FROM $template_table");
        $options[''] = __('---select template---','amwscpf');
        foreach ($template_list as $tpl){
            $options[$tpl->id] = __($tpl->tpl_name,'amwscpf');
        }
        #echo "<pre>";print_r($options);echo "</pre>";
        woocommerce_wp_select([
            'id'            => 'amwscpf_template',
            'label'         => __('Amazon Template','amwscpf'),
            'options'       => $options,
            'description'   => 'Select the template that you have imported from the Template section of the Amazon Feed Plugin',
            'desc_tip'      => true,
            'value'         => get_post_meta($post->ID,'_amwscpf_template',true),
        ]);
    }

    function addInLineJS(){
        global $wpdb, $post;
        wc_enqueue_js("
            jQuery(document).ready(function(){
                ajaxhost = '".plugins_url('/', __FILE__)."';
                jQuery('#amwscpf_template').change(function(){
                    jQuery('.amwscpf_template_data').remove();
                     amwscpf_template_data(this);
                });
            
                jQuery('#amwscpf_feed_product_type').on('Keyup',function(e){
                    console.log(this);
                });
        
            // load template data
            function amwscpf_template_data(template){
            jQuery('#amwscpf-amazon-options').find('div.inside').append('<div class=\'amwscpf_template_data\'></div>')
                jQuery.ajax({
                    url     : ajaxurl,
                    method  : 'post',
                    data    : {
                        security    : '".wp_create_nonce( 'amazon-exportfeed-nonce' )."',
                        action      : 'amazon_seller_ajax_handle',
                        feedpath    : 'core/ajax/wp/get_tpl_values.php',
                        cmd         : 'load_template_data',
                        tpl_id      : jQuery(template).val(),
                        product_id  : ".$post->ID.",
                        
                    },
                    success : function(res){
                        jQuery('.amwscpf_template_data').html(res);
                    }
                });
            }
            
           
            
            jQuery('#amwscpf_template').change();
            });
        ");
    }


}

$object = new AMWSCPF_ProductMerabox();