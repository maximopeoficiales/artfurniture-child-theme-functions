<?php
add_action('wp_enqueue_scripts', 'artfurniture_child_enqueue_styles');
function artfurniture_child_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}


/**
 * Añade el campo NIF a la página de checkout de WooCommerce
 */
add_action('woocommerce_after_order_notes', 'agrega_mi_campo_personalizado');

function agrega_mi_campo_personalizado($checkout)
{

    echo '<div id="additional_checkout_field"><h2>' . __('Información adicional') . '</h2>';

    woocommerce_form_field('nif', array(
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Documento'),
        'required'      => true,
        'placeholder'   => __('Introduzca el Nº de documento'),
    ), $checkout->get_value('nif'));

    echo '</div>';
}
/**
 * Comprueba que el campo NIF no esté vacío
 */
add_action('woocommerce_checkout_process', 'comprobar_campo_nif');

function comprobar_campo_nif()
{

    // Comprueba si se ha introducido un valor y si está vacío se muestra un error.
    if (!$_POST['nif'])
        wc_add_notice(__('Documento, es un campo requerido. Debe de introducir su NIF DNI para finalizar la compra.'), 'error');
}

/**
 * Actualiza la información del pedido con el nuevo campo
 */
add_action('woocommerce_checkout_update_order_meta', 'actualizar_info_pedido_con_nuevo_campo');

function actualizar_info_pedido_con_nuevo_campo($order_id)
{
    if (!empty($_POST['nif'])) {
        update_post_meta($order_id, 'NIF', sanitize_text_field($_POST['nif']));
    }
}

/**
 * Muestra el valor del nuevo campo NIF en la página de edición del pedido
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'mostrar_campo_personalizado_en_admin_pedido', 10, 1);

function mostrar_campo_personalizado_en_admin_pedido($order)
{
    echo '<p><strong>' . __('DOCUMENTO') . ':</strong> ' . get_post_meta($order->id, 'NIF', true) . '</p>';
}

/**
 * Incluye el campo NIF en el email de notificación del cliente
 */

add_filter('woocommerce_email_order_meta_keys', 'muestra_campo_personalizado_email');

function muestra_campo_personalizado_email($keys)
{
    $keys[] = 'NIF';
    return $keys;
}

/**
 *Incluir NIF en la factura (necesario el plugin WooCommerce PDF Invoices & Packing Slips)
 */

add_filter('wpo_wcpdf_billing_address', 'incluir_nif_en_factura');

function incluir_nif_en_factura($address)
{
    global $wpo_wcpdf;

    echo $address . '<p>';
    $wpo_wcpdf->custom_field('NIF', 'Documento: ');
    echo '</p>';
}

/**
 * Set a minimum order amount for checkout
 */
add_action('woocommerce_checkout_process', 'wc_minimum_order_amount');
add_action('woocommerce_before_cart', 'wc_minimum_order_amount');

function wc_minimum_order_amount()
{
    // Set this variable to specify a minimum order value
    $minimum = 30000;
    if (WC()->cart->total < $minimum) {
        if (is_cart()) {

            wc_print_notice(
                sprintf(
                    'El total de tu pedido actual es %s — debe tener un pedido con un mínimo de %s para hacer tu pedido',
                    wc_price(WC()->cart->total),
                    wc_price($minimum)
                ),
                'error'
            );
        } else {

            wc_add_notice(
                sprintf(
                    'El total de su pedido actual es %s — debe tener un pedido con un mínimo de %s para hacer tu pedido',
                    wc_price(WC()->cart->total),
                    wc_price($minimum)
                ),
                'error'
            );
        }
    }
}

// Remove scripts from head.
function move_scripts_from_head_to_footer()
{
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'wp_enqueue_scripts', 1);

    add_action('wp_footer', 'wp_print_scripts', 5);
    add_action('wp_footer', 'wp_enqueue_scripts', 5);
    add_action('wp_footer', 'wp_print_head_scripts', 5);
}
add_action('wp_enqueue_scripts', 'move_scripts_from_head_to_footer');


function add_async_attribute($tag, $handle)
{

    // add script handles to the array below
    $scripts_to_async = array('contact-form-7', 'themepunchboxext', 'tp-tools', 'revmin', 'jquery-blockui', 'wc-add-to-cart', 'js-cookie', 'woocommerce', 'vc_woocommerce-add-to-cart-js', 'prettyPhoto', 'bootstrap', 'owl-carousel', 'fancybox', 'fancybox-buttons', 'fancybox-media', 'fancybox-thumbs', 'superfish', 'modernizr', 'shuffle', 'mousewheel', 'countdown', 'counterup', 'variables', 'artfurniture-theme', 'mmm_menu_functions', 'wp-embed', 'underscore', 'wp-util', 'wc-add-to-cart-variation', 'wpb_composer_front_js', 'product-options');

    foreach ($scripts_to_async as $async_script) {
        if ($async_script === $handle) {
            return str_replace(' src', ' defer="defer" src', $tag);
        }
        // if ($async_script == ''){
        // 	 return str_replace(' src', ' defer="defer" src', $tag);
        // }
    }
    return $tag;
}


add_filter('script_loader_tag', 'add_async_attribute', 10, 2);


function add_rel_preload($html, $handle, $href, $media)
{

    if (is_admin())
        return $html;

    $html = <<<EOT
<link rel='preload' as='style' onload="this.onload=null;this.rel='stylesheet'" id='$handle' href='$href' type='text/css' media='all' />
EOT;
    return $html;
}
add_filter('style_loader_tag', 'add_rel_preload', 10, 4);

// Remove Query String from Static Resources
function remove_cssjs_ver($src)
{
    if (strpos($src, '?ver='))
        $src = remove_query_arg('ver', $src);
    return $src;
}
add_filter('style_loader_src', 'remove_cssjs_ver', 10, 2);
add_filter('script_loader_src', 'remove_cssjs_ver', 10, 2);

// Remove Emojis
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

// Remove Shortlink
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

// Disable Embed
function disable_embed()
{
    wp_dequeue_script('wp-embed');
}
add_action('wp_footer', 'disable_embed');

// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// Remove RSD Link
remove_action('wp_head', 'rsd_link');

// Hide Version
remove_action('wp_head', 'wp_generator');

// Remove WLManifest Link
remove_action('wp_head', 'wlwmanifest_link');

// Disable JQuery Migrate
function deregister_qjuery()
{
    if (!is_admin()) {
        wp_deregister_script('jquery');
    }
}
// add_action('wp_enqueue_scripts', 'deregister_qjuery'); 

// Disable Self Pingback
function disable_pingback(&$links)
{
    foreach ($links as $l => $link)
        if (0 === strpos($link, get_option('home')))
            unset($links[$l]);
}

add_action('pre_ping', 'disable_pingback');

// Disable Heartbeat
add_action('init', 'stop_heartbeat', 1);
function stop_heartbeat()
{
    wp_deregister_script('heartbeat');
}

// Disable Dashicons in Front-end
function wpdocs_dequeue_dashicon()
{
    if (current_user_can('update_core')) {
        return;
    }
    wp_deregister_style('dashicons');
}
add_action('wp_enqueue_scripts', 'wpdocs_dequeue_dashicon');

// Disable Contact Form 7 CSS/JS on Every Page
add_filter('wpcf7_load_js', '__return_false');
add_filter('wpcf7_load_css', '__return_false');


function dequeue_my_css()
{
    wp_dequeue_style('style_optimizate');
    wp_deregister_style('style_optimizate');
}
add_action('wp_enqueue_scripts', 'dequeue_my_css');
// add a priority if you need it
// add_action('wp_enqueue_scripts','dequeue_my_css',100);

wp_enqueue_style('style_optimizate', get_template_directory_uri() . '/css/style_optimizate.css', false, '1.1', 'all');


/***
 ** Comprimir HTML
 ***/
class Compression_html_g
{
    protected $chtmlg_compress_css = true;
    protected $chtmlg_compress_js = true;
    protected $chtmlg_info_comment = true;
    protected $chtmlg_remove_comments = true;
    protected $html;
    public
    function __construct($html)
    {
        if (!empty($html)) {
            $this->chtmlg_parseHTML($html);
        }
    }
    public
    function __toString()
    {
        return $this->html;
    }
    protected
    function chtmlg_bottomComment($raw, $compressed)
    {
        $raw = strlen($raw);
        $compressed = strlen($compressed);
        $savings = ($raw - $compressed) / $raw * 100;
        $savings = round($savings, 2);
        return '<!--HTML compressed, size saved ' . $savings .
            '%. From ' . $raw .
            ' bytes, now ' . $compressed .
            ' bytes-->';
    }
    protected
    function chtmlg_minifyHTML($html)
    {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $overriding = false;
        $raw_tag = false;
        $html = '';
        foreach ($matches as $token) {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
            $content = $token[0];
            if (is_null($tag)) {
                if (!empty($token['script'])) {
                    $strip = $this->chtmlg_compress_js;
                } else if (!empty($token['style'])) {
                    $strip = $this->chtmlg_compress_css;
                } else if ($content == '<!--wp-html-compression no compression-->') {
                    $overriding = !$overriding;
                    continue;
                } else if ($this->chtmlg_remove_comments) {
                    if (!$overriding && $raw_tag != 'textarea') {
                        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                    }
                }
            } else {
                if ($tag == 'pre' || $tag == 'textarea') {
                    $raw_tag = $tag;
                } else if ($tag == '/pre' || $tag == '/textarea') {
                    $raw_tag = false;
                } else {
                    if ($raw_tag || $overriding) {
                        $strip = false;
                    } else {
                        $strip = true;
                        $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                        $content = str_replace(' />', '/>', $content);
                    }
                }
            }
            if ($strip) {
                $content = $this->chtmlg_removeWhiteSpace($content);
            }
            $html .= $content;
        }
        return $html;
    }
    public
    function chtmlg_parseHTML($html)
    {
        $this->html = $this->chtmlg_minifyHTML($html);
        if ($this->chtmlg_info_comment) {
            $this->html .= "\n" . $this->chtmlg_bottomComment($html, $this->html);
        }
    }
    protected
    function chtmlg_removeWhiteSpace($str)
    {
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n", '', $str);
        $str = str_replace("\r", '', $str);
        while (stristr($str, '  ')) {
            $str = str_replace('  ', ' ', $str);
        }
        return $str;
    }
}

function chtmlg_wp_html_compression_finish($html)
{
    return new Compression_html_g($html);
}

function chtmlg_wp_html_compression_start()
{
    ob_start('chtmlg_wp_html_compression_finish');
}
add_action('get_header', 'chtmlg_wp_html_compression_start');

/**
 * Obtengo nombre de categoria por Product.
 *
 * @return string
 */
function getCategoryNameByIdProduct($product): string
{
    // significa que no tiene un padre
    $productCurrent = null;
    if ($product->get_parent_id() == 0) {
        $productCurrent = wc_get_product($product->get_id());
    } else {
        $productCurrent = wc_get_product($product->get_parent_id());
    }

    $category_id = $productCurrent->get_category_ids()[0];
    $term = get_term_by("id", $category_id, "product_cat", "ARRAY_A");
    return $term["name"] ?? "Sin Categoria";
}

//cambio el nombre del archivo que llega al correo
add_filter('wpo_wcpdf_filename', 'wpo_wcpdf_custom_filename', 10, 4);
/**
 * Cambio el nombre de los pdf a Pedido-N.pdf
 *
 * @return void
 */
function wpo_wcpdf_custom_filename($filename, $template_type, $order_ids, $context)
{
    // prepend your shopname to the file
    $invoice_string = _n('invoice', 'invoices', count($order_ids), 'woocommerce-pdf-invoices-packing-slips');
    $new_prefix = "Pedido-";
    $new_filename = str_replace($invoice_string, $new_prefix, $filename);

    return $new_filename;
}
