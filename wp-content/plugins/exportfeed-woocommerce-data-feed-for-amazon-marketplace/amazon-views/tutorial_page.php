<?php 

Class View{
   
   public function tutorial_page_view()
   {
    // require_once '/core/data/productlist.php';
    // $providers = new AMWSCP_PProviderList();
    require_once plugin_dir_path(__FILE__).'../core/classes/providerlist.php';
    $providers = new AMWSCP_PProviderList();
    $embed_code = wp_oembed_get('https://www.youtube.com/watch?v=QEHoUtlDN54&feature=youtu.be');
    $embed_code1 = wp_oembed_get('https://www.youtube.com/watch?v=loeJuYLdVvQ&feature=youtu.be');
    $output = '<div class="wrap">
    
     <div style="clear:both"></div>
    <div class="clear"></div>
    

   <div id="for_amzon" class="cpf_tutorials_page" style="margin-top: 59px;">
                <div id="for_amzon" class="cpf_google_merchant_tutorials postbox report">
                    <h2 class="hndle" id="tutorial_title" > ExportFeed : Amazon Marketplace Feed Creation Tutorials</h2>
                    <div class="inside"> <span>If you need help getting started, please watch this short video which demonstrates how you can create and upload feed to Amazon Marketplaces. </span>
                    </div>
                </div>'.$embed_code.'</div>
 <div style="display:none;" id="for_google" class="cpf_tutorials_page" style="margin-top: 59px;">
                <div id="for_google" class="cpf_google_merchant_tutorials">
                    <h2 id="tutorial_title" > ExportFeed : Google Feed Creation Tutorials</h2>
                </div>'.$embed_code1.'</div>
 <div style="display:none;" id="for_other" class="cpf_tutorials_page" style="margin-top: 59px;">
                <div id="for_other" class="cpf_google_merchant_tutorials">
                    <h2 id="tutorial_title_other" > ExportFeed : Google Feed Creation Tutorials</h2>
                </div>Video is not available. <a target="_blank" href="http://www.exportfeed.com/documentation">Here</a> is the detail documentation for it.</div>


    
</div>


<div class="clear"></div></div>
<div class="cpf_tutorials_page" style="margin-top: 59px;">
<p><b>Was this helpful ? For Further Support Contact us <a target="_blank" href="http://www.exportfeed.com/contact/">here</a></b></p>
</div>
<script type="text/javascript">
    // jQuery("#selectFeedType").click(function(){
    //      var merchant_lists=amwscp_doFetchLocalCategories();
    //      console.log(merchant_lists);
    // });
    function selectFunction(value){
        if(value=="Google"){
            jQuery("#for_amzon").hide();
            jQuery("#for_other").hide();
            jQuery("#for_google").show();
        }
        else if(value=="Amazon"){
             jQuery("#for_amzon").show();
             jQuery("#for_google").hide();
             jQuery("#for_other").hide();
        }
        else{
             jQuery("#for_amzon").hide();
             jQuery("#for_google").hide();
             jQuery("#for_other").show();
             jQuery("#tutorial_title_other").html(value);
        }
        jQuery("#tutorial_title").html(value);
    }
</script>';
echo $output;
   }
}
$view=new View(); 
?>
