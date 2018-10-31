<?php
class Stock {

    function __construct() {
        add_action( 'admin_menu', array($this, 'menu_options') );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_assets') );
        add_action( 'wp_ajax_actualizar_stock', array($this, 'ajax_actualizar_stock') );
        add_action( 'wp_ajax_revisar_sin_stock', array($this, 'ajax_revisar_sin_stock') );
    }

    function admin_assets() {

        wp_enqueue_style( 'actualizar_stock_admin', plugin_dir_url( __FILE__ ) . 'assets/css/actualizar.css', array(), time() );
        wp_enqueue_script( 'actualizar_stock_admin', plugin_dir_url( __FILE__ ) . 'assets/js/actualizar.js', array(), time(), true );

    }

    function menu_options() {

        add_submenu_page(
            'tools.php',
            __( 'Actualizar Stock', 'actualizar_stock' ),
            __( 'Actualizar Stock', 'actualizar_stock' ),
            'manage_options',
            'actualizar-stock',
            array($this, 'page_options')
        );
    }

    function page_options() { ?>
        <p>&nbsp;</p>
        <h1><?php _e( 'Actualizar Stock', 'actualizar_stock' ) ?></h1>
        <form id="actualizar_stock" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" enctype="multipart/form-data">
            <p>Seleccionar archivo para actualizar (.csv)<br>
                <input type="file" name="actualizar_stock_file" />
            </p>
            <input type="hidden" name="action" value="actualizar_stock">
            <?php wp_nonce_field( 'actualizar_stock', 'wpnonce' ); ?>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Actualizar', 'importador_usuarios'); ?>">
                <a href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/demo/demo.csv'; ?>" class="button button-button" target="blank">
                    <?php _e('Descargar Ejemplo', 'actualizar_stock'); ?>
                </a>
            </p>
        </form>
        <p>
            <input type="hidden" id="revisar_ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>" />
            <button type="button" id="revisar_sin_stock" class="button button-primary"><?php _e('Revisar productos sin stock', 'importador_usuarios'); ?></button>
        </p>
        <div id="actualizar_sinstock_done"></div>
        <div id="actualizar_stock_done"></div>
    <?php }

    function ajax_actualizar_stock(){

        if ( ! wp_verify_nonce( $_POST['wpnonce'], 'actualizar_stock' ) ) die ( 'Busted!');

        $csv = array();
        $lines = file($_FILES['actualizar_stock_file']['tmp_name'], FILE_IGNORE_NEW_LINES); ?>

        <h2>Productos Actualizados</h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-author">ID</th>
                    <th scope="col" class="manage-column column-author">SKU</th>
                    <th scope="col" class="manage-column column-author">Producto</th>
                    <th scope="col" class="manage-column column-author">Stock</th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php $c = 0;
                foreach ($lines as $key => $value) {
                    $c++;
                    $row = str_getcsv($value);
                    if ($c > 1) {
                        $sku = $row[0];
                        $idproducto = wc_get_product_id_by_sku($sku);
                        if ($idproducto > 0) {
                            $idtranslate = icl_object_id($idproducto, 'product', false, 'en');
                            $nombre = $row[1];
                            $stock = $row[2];
                            update_post_meta($idproducto, '_stock', $stock);
                            update_post_meta($idtranslate, '_stock', $stock);
                            if ($stock > 0) {
                                update_post_meta($idproducto, '_stock_status', 'instock');
                                update_post_meta($idtranslate, '_stock_status', 'instock');
                            }
                            ?>
                            <tr>
                                <td class="author column-author"><?php echo $idproducto; ?></td>
                                <td class="author column-author"><?php echo $sku; ?></td>
                                <td class="author column-author"><?php echo $nombre; ?></td>
                                <td class="author column-author"><?php echo $stock; ?></td>
                            </tr>
                        <?php }
                    }
                } ?>
            </tbody>
        </table>

        <?php exit();

    }

    function ajax_revisar_sin_stock(){

        $productos = new WP_Query(array(
    		'post_type' => 'product',
    		'posts_per_page' => -1
    	));
    	$anterior_parent = 1;
    	$c = 0;
        $ss = 0;
    	while ($productos->have_posts()) { $productos->the_post();
    		$idproduct = get_the_id();
    		$tiene_stock = false;
    		$handle = new WC_Product_Variable($idproduct);
    		$variations = $handle->get_children();
    		if ($variations) {
    			foreach ($variations as $value) {
    				$single_variation=new WC_Product_Variation($value);
    				$idvariable = $single_variation->get_variation_id();
    				$stock = get_post_meta($idvariable, '_stock', true);
    				if ($stock > 0) {
    					$tiene_stock = true;
    					update_post_meta($idvariable, '_stock_status', 'instock');
    				} else {
    					update_post_meta($idvariable, '_stock_status', 'outofstock');
    				}
    			}
    		}
    		if ($tiene_stock) {
    			update_post_meta($idproduct, '_stock_status', 'instock');
    		} else {
                $ss++;
    			update_post_meta($idproduct, '_stock_status', 'outofstock');
    		}
    	};
        echo 'Terminado de revisar. Se pasaron '.$ss.' productos a "sin stock"';

        exit();
    }

}
?>