<div class="gaoo-promotion-box">
    <h2><?php esc_html_e( 'Google Analytics Opt-Out', 'ga-opt-out' ); ?></h2>

    <div class="gaoo-promotion-box-body">
        <a href="<?php echo esc_url( $promotion[ 'link' ] ); ?>" target="_blank"></a>

        <?php if ( ! empty( $promotion[ 'img' ] ) ): ?>
            <img src="<?php echo esc_url( $promotion[ 'img' ] ); ?>" alt="<?php echo empty( $promotion[ 'link_txt' ] ) ? '' : esc_attr( $promotion[ 'link_txt' ] ); ?>">
        <?php else: ?>
            <?php echo $promotion[ 'link_txt' ]; ?>
        <?php endif; ?>
    </div>

    <div class="gaoo-promotion-box-footer">
        <a href="<?php echo esc_url( add_query_arg( 'gaoo_promo', 1 ) ); ?>"><?php esc_html_e( 'Close it for ever', 'ga-opt-out' ); ?></a>
    </div>
</div>


