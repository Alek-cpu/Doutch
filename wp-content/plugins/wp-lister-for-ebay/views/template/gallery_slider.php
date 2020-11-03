<div class="wplister-gallery">
    <?php
    for ( $i = 1; $i < count( $images ); $i++ ):
        $image_url = $images[$i];
    ?>
        <input type="radio" name="thumb_switch" id="id<?php echo $i; ?>" <?php checked( 1, $i ); ?> value="<?php echo $image_url; ?>">
        <label for="id<?php echo $i; ?>">
            <span><img src="<?php echo $image_url; ?>" width="100"></span>
        </label>
        <div class="gallery-thumbnail"><img src="<?php echo $image_url; ?>"></div>
    <?php endfor; ?>
</div>