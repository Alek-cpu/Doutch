<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpdb,$woocommerce;
$tpl_id = sanitize_text_field($_REQUEST['tpl_id']);
$product_id = sanitize_text_field($_REQUEST['product_id']);

$table = $wpdb->prefix."amwscp_template_values";
$sql = $wpdb->prepare("SELECT * FROM $table WHERE tmpl_id = %d AND required = %d",[$tpl_id,1]);
$values = $wpdb->get_results($sql);

// these are values that will be added later in advanced section
$restricted = [
    'feed_product_type',
];

foreach ($values as $field){

    /*if (in_array($field->fields,$restricted)){
        continue;
    }*/
    #echo "<pre>";print_r($field);echo "</pre>";
    $valid_values = array();
    if ($field->valid_values){
        $data = maybe_unserialize($field->valid_values);
        foreach ($data as $key => $value){
            $valid_values[$value] = $value;
        }
    }

    if(in_array($field->fields,$restricted)){
        woocommerce_wp_text_input([
            'id'            => 'amwscpf_'.$field->fields,
            'label'         => __($field->labels,'amwscpf'),
            'placeholder'   => $field->examples,
            'description'   => $field->definition,
            'desc_tip'      => true,
            'value'         => get_post_meta($_REQUEST['product_id'],'_amwscpf_'.$field->fields,true),
//                    'custom_attributes' => ['onKeyup'=>'dothis(this)']
        ]);
    } elseif ($field->valid_values){
        woocommerce_wp_select([
            'id'            => 'amwscpf_'.$field->fields,
            'label'         => __($field->labels,'amwscpf'),
            'options'       => $valid_values,
            'description'   => $field->definition,
            'desc_tip'      => true,
            'value'         => get_post_meta($_REQUEST['product_id'],'_amwscpf_'.$field->fields,true)
        ]);
    } else {
        woocommerce_wp_text_input([
            'id'            => 'amwscpf_'.$field->fields,
            'label'         => __($field->labels,'amwscpf'),
            'placeholder'   => $field->examples,
            'description'   => $field->definition,
            'desc_tip'      => true,
            'value'         => get_post_meta($_REQUEST['product_id'],'_amwscpf_'.$field->fields,true)
        ]);
    }
}
?>
<script type="text/javascript">
    jQuery('.woocommerce-help-tip').tipTip({
        'attribute' : 'data-tip',
        'maxWidth' : '250px',
        'fadeIn' : 50,
        'fadeOut' : 50,
        'delay' : 200
    });
</script>
