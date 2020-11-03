<?php

	$d = $wpl_transaction['details'];

?><html>
<head>
    <title>Transaction details</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        body.wple_modal_container { font:12px/16px sans-serif; }
        td,p { color:#2f2f2f; font-size: 12px; line-height: 16px; }
        a { text-decoration: none; }
        a:hover { color: #000; }
    </style>
</head>

<body class="wple_modal_container">

    <h2>Details for transaction #<?php echo $wpl_transaction['id'] ?></h2>

    <table width="100%" border="0">
        <tr>
            <td width="20%">            
                <b>Date:</b>
            </td><td>
                <?php echo $wpl_transaction['date_created'] ?>
            </td>
        </tr>
        <tr>
            <td>            
                <b>eBay Order ID:</b>
            </td><td>
                <?php echo $wpl_transaction['order_id'] ?>
            </td>
        </tr>
        <tr>
            <td>            
                <b>eBay Item ID:</b>
            </td><td>
                <?php echo $wpl_transaction['item_id'] ?>
            </td>
        </tr>
        <tr>
            <td>            
                <b>eBay Buyer:</b>
            </td><td>
                <?php echo $d->Buyer->UserID ? $d->Buyer->UserID : $wpl_transaction['buyer_userid'] ?>
            </td>
        </tr>
        <tr>
            <td>            
                <b>Buyer Email:</b>
            </td><td>
                <?php echo $d->Buyer->Email ?>
            </td>
        </tr>
        <?php if ( $d->BuyerCheckoutMessage != '' ) : ?>
        <tr>
            <td>            
                <b>Message:</b>
            </td><td>
                <?php echo $d->BuyerCheckoutMessage ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>

        
    <h2>Shipping and Payment</h2>

    <table width="100%" border="0">
        <tr><td width="50%">
            
            <b>Shipping address:</b><br>
            <?php echo @$d->Buyer->BuyerInfo->ShippingAddress->Name ? $d->Buyer->BuyerInfo->ShippingAddress->Name : $wpl_transaction['buyer_name']?> <br>
            <?php echo @$d->Buyer->BuyerInfo->ShippingAddress->Street1 ?> <br>
            <?php if (@$d->Buyer->BuyerInfo->ShippingAddress->Street2): ?>
            <?php echo $d->Buyer->BuyerInfo->ShippingAddress->Street2 ?> <br>
            <?php endif; ?>
            <?php echo @$d->Buyer->BuyerInfo->ShippingAddress->PostalCode ?> 
            <?php echo @$d->Buyer->BuyerInfo->ShippingAddress->CityName ?> <br>
            <?php echo @$d->Buyer->BuyerInfo->ShippingAddress->CountryName ?> <br>
            <br>
            <b>Shipping service:</b><br>
            <?php echo @$d->ShippingServiceSelected->ShippingService ?> <br>
            <br>

        </td><td width="50%">

            <b>Payment address:</b><br>
            <?php if ( $d->Buyer->RegistrationAddress ) : ?>
                <?php echo $d->Buyer->RegistrationAddress->Name ?> <br>
                <?php echo $d->Buyer->RegistrationAddress->Street1 ?> <br>
                <?php if ($d->Buyer->RegistrationAddress->Street2): ?>
                <?php echo $d->Buyer->RegistrationAddress->Street2 ?> <br>
                <?php endif; ?>
                <?php echo $d->Buyer->RegistrationAddress->PostalCode ?> 
                <?php echo $d->Buyer->RegistrationAddress->CityName ?> <br>
                <?php echo $d->Buyer->RegistrationAddress->CountryName ?> <br>
            <?php else: ?>
                No registration address provided.<br>
            <?php endif; ?>
            <br>
            <b>Payment method:</b><br>
            <?php echo $d->Status->PaymentMethodUsed ?> <br>
            <br>
            
        </td></tr>
    </table>

    <h2>Purchased Item</h2>

    <?php if ( $wpl_auction_item ) : ?>
        <table width="100%" border="0">
            <tr><th>            
                <?php echo __( 'Quantity', 'wp-lister-for-ebay' ) ?> 
            </th><th>
                <?php echo __( 'Name', 'wp-lister-for-ebay' ) ?> 
            </th><th>
                <?php echo __( 'Price', 'wp-lister-for-ebay' ) ?> 
            </th></tr>
            <tr><td width="20%">                      
                <?php echo $wpl_transaction['quantity'] ?> 
            </td><td>
                <?php echo $wpl_auction_item->auction_title ?>
                <?php if ( is_object( @$d->Variation ) ) : ?>
                    <?php foreach ($d->Variation->VariationSpecifics as $spec) : ?>
                        <br> -
                        <?php echo $spec->Name ?>:
                        <?php echo $spec->Value[0] ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td><td>
                <?php echo wc_price( $wpl_auction_item->price ) ?>
            </td></tr>
        </table>
    <?php else: ?>
        Item <b><?php echo $wpl_transaction['item_id'] ?></b> could not be found in WP-Lister.<br>
    <?php endif; ?>
    
    <?php if ( is_array( $wpl_transaction['history'] ) ) : ?>

        <h2>History</h2>

        <table width="100%" border="0">
            <tr><th>            
                <?php echo __( 'Date', 'wp-lister-for-ebay' ) ?> 
            </th><th>
                <?php echo __( 'Time', 'wp-lister-for-ebay' ) ?> 
            </th><th>
                <?php echo __( 'Message', 'wp-lister-for-ebay' ) ?> 
            </th><th>
                <?php #echo __( 'Success', 'wp-lister-for-ebay' ) ?> 
            </th></tr>

            <?php foreach ( $wpl_transaction['history'] as $record ) : ?>

                <tr><td width="16%">                      
                    <?php echo gmdate( get_option( 'date_format' ), $record->time ) ?> 
                </td><td width="12%">                      
                    <?php echo gmdate( 'H:i:s', $record->time ) ?> 
                </td><td>
                    <?php echo $record->msg ?> 
                </td><td>
                    <?php echo $record->success ? '<span style="color:darkgreen;">OK</span>' : '<span style="color:darkred;">FAILED</span>' ?> 
                </td></tr>

            <?php endforeach; ?>

        </table>

        <?php if ( get_option('wplister_log_level') ) : ?>
            <h2>Debug data</h2>
            <pre><?php print_r($wpl_transaction['history']) ?></pre>
        <?php endif; ?>

    <?php endif; ?>
    
           
    <pre><?php #print_r( $d ); ?></pre>
    <pre><?php #print_r( $wpl_auction_item ); ?></pre>
    <pre><?php #print_r( $wpl_transaction ); ?></pre>


</body>
</html>



