<?php
global $amwcore;
//require_once AMWSCPF_PATH."/core/classes/amazon.php";

$amazon = new AMWSCPF_Amazon();
$template = $cpf_templates;


// echo "<pre>";
// print_r($cpf_class);exit;


#$cpf_class->importCategory2(); // testing for template fetch
#die;
#echo "<pre>";print_r($template);echo "</pre>";die;


if (empty($template))
    $display = 'Import the template from the left!';
else{
    $display = '<div id="message_box">';
    if (isset($_GET['action']) && $_GET['action'] == 'remove_template') $display .= '<p>Template Removed</p>';
    if (isset($_GET['action']) && $_GET['action'] == 'default_template') $display .= '<p>Template Updated</p>';
    $display .= '</div>';
    $display .= "<ul>";
    foreach ($template as $temp){
        $display .= '<li><hr>'.$temp->tpl_name.'('.$temp->country.')';
        $display .= '&nbsp;&nbsp;<a href="admin.php?page=amwscpf-feed-template&action=remove_template&tmp_id='.$temp->id.'" class="button button-small">Remove</a>';
        $display .= '</li>';
    }
    $display .= "</ul>";
}

$cpf_class->prepare_items($template);
?>

<!-- Added For Showing the pagination of wordpress default Table -->
  
  <script type="text/javascript">
    jQuery(document).ready(function(){
     jQuery('.wp-list-table').DataTable({
             "order": [[ 1, "asc" ]]
     });
     // jQuery('.tablenav,.top').css({'display':'block'});
    });
</script>

<div style="display: none;" id="ajax-loader-cat-import" ><span id="gif-message-span"></span></div>

<!-- Added For Showing the pagination of wordpress default Table -->

<div class="wrap template-page">
    <div id="setting-error-settings_updated" class="updated settings-error">
     <p><strong>This section allows you to manage specific categories of products in your online store.</strong></p>
     <p style="margin-top: -26px;"><strong>Instructions:</strong></p>
        <ul class="template-page-ul" >
         <li>To import product categories, click on the "Import" button below the required product categorie's title.</li>
         <li>To remove product categories, click on the "Remove" button. You can still retrieve removed product categories in the future.
         </li>
         <li>The product categories that have already been imported display "Imported" as their status.</li>
     </ul>
     <p style="margin-top: -20px;"><strong>Note: Only the product categories lists of Amazon Marketplace with default account will be listed below. Right now you are importing amazon category product categories for <u><?php echo $this->countryfullname; ?></u></strong></p>
       <!--  <br>
         The templates that have already been imported display "Imported" as their status.<br>
         
     </p> -->
    </div>

     <div style="display: none;" id="import_template_page" class="updated settings-error">
        <p>Importing product categories. please wait.....</p>
        <img class="amwscpf_import_template" src="<?php echo AMWSCPF_URL; ?>/images/loading_balls.gif" height="25" width="30" />
    </div>

    <?php
    if (isset($_GET['response']) && $_GET['response'] != 200){
        if($_GET['response']==500){
    ?>
    <div id="template-import-message" class="updated settings-error">
         <span style=" float:right;
    display:inline-block;
    padding:2px 5px;
    background:#ccc;" id='close' onclick='this.parentNode.remove(this.parentNode); return false;'><a href="javascript:void();">x</a></span>
        <p><strong style="color: red;">Internal Server Error:</strong>Template You selected may not be there in server. Please contact our <strong><a href="#">Support</a></strong> for more details.</p>
    </div>

    <?php } }else{ if (isset($_GET['response']) && $_GET['response'] == 200){?>
      <div id="template-import-message" class="updated settings-error">
        <span style=" float:right;
    display:inline-block;
    border-radius: 24px;
    padding:2px 5px;
    background:#ccc;" id='close' onclick='this.parentNode.remove(this.parentNode); return false;'><a href="javascript:void();">x</a></span>
        <p><strong style="color: green;">Product categories Imported Successfully</strong></p>
    </div>
     <?php } } ?>
    
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!--            CATEGORES VIEWS -->
            <div id="postbox-container-3" class="postbox-container template-data">
                <form id="etcpf-uploaded-product-filter" method="get">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']?>" />
                    <input type="hidden" name="tab" value="categories" />
                    <?php $cpf_class->display() ?>
                </form>

           <div id="postbox-container-1" class="postbox-container">
                <div id="side-sortables" class="meta-box">

                    <!-- first sidebox -->
                    <div class="postbox" id="submitdiv">
                        <!--<div title="Click to toggle" class="handlediv"><br></div>-->
                        <h3><span>Guidelines</span></h3>
                        <div class="inside">

                            <div id="submitpost" class="submitbox">

                                <div id="misc-publishing-actions">
                                    <div class="misc-pub-section">
                                        <table border="1">
                                            <tbody><tr>
                                                <th>Action</th>
                                                <th>Description</th>
                                            </tr>
                                            <tr>
                                                <th>Import</th>
                                                <td><p>This action allows you to import the desired categories listed to the right. Just make sure, you hover around the template name and then click on <strong>Import</strong>.</p></td>
                                            </tr>
                                            <tr>
                                                <th>Remove</th>
                                                <td><p>It will remove the template that you have imported which can be retrieved later on too.</p></td>
                                            </tr>
                                        </tbody></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <!--END CATEGOGRIES VIEW -->


                         <!--    Guidance Box        -->
          


        </div>
    </div>
</div>
<script>

    jQuery(document).ready(function(){
        jQuery('select[name=action]').find('option[value=-1]').html('Marketplace');
        jQuery('#message_box p').toggle(3000);
        jQuery(".tips, .help_tip").tipTip({
            'attribute' : 'data-tip',
            'maxWidth' : '250px',
            'fadeIn' : 50,
            'fadeOut' : 50,
            'delay' : 200
        });
    });
</script>