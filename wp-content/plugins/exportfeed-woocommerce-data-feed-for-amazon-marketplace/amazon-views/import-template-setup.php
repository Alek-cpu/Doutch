<?php
global $amwcore;
?>
<style type="text/css">
    #message_for_import_template {
        border: solid 1px yellow;
        height: 45px;
        padding: 13px 10px;
        background-color: rgb(255, 252, 227);
    }

    #template-lists{
        overflow: scroll;
        height:575px;
    }

    dl.template {
        width: 185px;
        height: 90px;
        float: left;
        margin: 10px 15px;
    }
    dl.template:hover{
        border:1px solid yellow;
    }

    dl.template dt {
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        padding-top: 25px;
        padding-bottom: 5px;
        border:0;
    }

    .template dd button {
        color: #6a6b66;
        margin: 10px 20px;
        border: 0;
        background: #fff;
    }
</style>
<div class="amazon-setup">
    <div id="message_for_import_template">
        <span class="dashicons dashicons-warning"></span>
        Select any of these template and start importing.
    </div>
    <?php $amwcore->amwscpf_overlay_loader('template_loader',[
        'position'  =>'absolute',
        'background'=>'#fff',
        'opacity'   => '0.45',
        'display'   => 'none',
        'top'       => '0',
        'bottom'    => '0',
        'left'      => '0',
        'right'     => '0'
    ],[
        'position'  =>'absolute',
        'right'     => '0',
        'left'      => '0',
        'top'       => '0',
        'bottom'    => '0',
        'margin'    => 'auto'
    ]) ?>
    <div id="template-lists">
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <dl class="template">
            <dt>Auto Accessory</dt>
            <dd><button>Import</button></dd>
        </dl>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>