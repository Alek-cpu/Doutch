<?php
global $amwcore;
?>
<style type="text/css">
    .progress {
        display: block;
        text-align: center;
        width: 0;
        height: 3px;
        background: red;
        transition: width .3s;
    }
    .progress.hide {
        opacity: 0;
        transition: opacity 1.3s;
    }
</style>
<div class="wrap">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!--User Action-->
            <div id="upload-instruction" class="postbox-container">
                <div class="postbox" id="amwscpf-bulk-feed">
                    <h3 class="hndle">Instructions</h3>
                    <div class="inside">
                        <?php $url = admin_url().'admin.php?page=amwscpf-feed-account&action=aabfs&feed_id='.$_REQUEST['id']; ?>
                        <p>Welcome to Feed Upload page. Please read the instructions carefully.</p>
                        <p>You will have to provide the information below in order to submit your Amazon feed.</p>
                        <p>If you have already added the valid credential <strong>Select from the tab below and submit</strong>.</p>
                        <p>If you dont have any valid credentials <strong>Goto <a href="<?php echo $url; ?>">Account page</a> and start creating a new valid account</strong>.</p>
                    </div>
                </div>
            </div>
            <div id="postbox-container-3" class="postbox-container">

                <div class="postbox">
                    <h3 class="hndle">1.Credentials</h3>
                    <div class="inside">
                        <?php if (count($cpf_credentials) > 0){
                            #echo "<pre>";print_r($cpf_credentials);echo "</pre>";
                            if(count($cpf_credentials)==1){
                         ?>
                                <table id="cpf_credentials_list">
                                    <?php foreach ($cpf_credentials as $i => $account ){ ?>
                                        <tr>
                                            <th><input type="radio" checked value="<?php echo $account->id ?>" name="cpf_credentials" onclick="save_feed_credential(this)"></th>
                                            <td><?php echo $account->title.' ('. $account->market_code .')' ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                         <?php
                            }else{
                            ?>
                            <table id="cpf_credentials_list">
                                <?php foreach ($cpf_credentials as $i => $account ){ ?>
                                    <tr>
                                        <th><input type="radio" <?php if($i==1) echo 'checked'; ?> value="<?php echo $account->id ?>" name="cpf_credentials" onclick="save_feed_credential(this);"></th>
                                        <td><?php echo $account->title.' ('. $account->market_code .')' ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php }
                        } else {
                            $url = admin_url().'admin.php?page=amwscpf-feed-account&action=aabfs&feed_id='.$_REQUEST['id'];
                            echo '<p>You dont have any credentials filled yet. <a href="'.$url.'">Please add the credentials</a>.</p>';
                        } ?>

                    </div>
                </div>

                <div class="postbox" id="submitbtn">
                    <h3 class="hndle">2. Submit Feed</h3>
                    <div class="inside">
                        <div class="progress"></div>
                        <input type="button" class="button-primary" onclick="amwscp_submitFeed()" value="Submit Feed" />
                        <p style="color:red"><strong>Note:</strong>Amazon has standard charges for feed submission. <a href="https://www.amazon.com/gp/help/customer/display.html?nodeId=1161240" target="_blank">Learn more</a>.</p>
                        <input type="hidden" value="<?php echo $cpf_id ?>" id="feed_id" />
                    </div>
                </div>

                <div class="postbox" id="jstincse">
                    <h3 class="hndle">3. Upload Report</h3>
                    <div class="inside">
                        <div id="updload_report">Feed is not submitted yet.</div>
                        <div id="report-spinner">
                            <?php $amwcore->amwscpf_loader_2('report-spinner'); ?>
                        </div>
                    </div>
                </div>

                <div class="postbox" id="Reports">
                    <h3 class="hndle">4. Report Page</h3>
                    <div class="inside">
                        <pre id="updload_report" style="font-family: 'Apple SD Gothic Neo', 'Malgun Gothic', 'Nanum Gothic', Dotum, sans-serif">Once you submit your feed goto Report Page for its reports and results.</pre>
                        <a href="<?php echo admin_url('admin.php?page=amwscpf-feed-reports') ?>" class="button-primary" >Reports</a>
                    </div>
                </div>
            </div>



        </div>
    </div>
</div>