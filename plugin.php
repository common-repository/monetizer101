<?php
/**
  Plugin Name: Monetizer101
  Description: Display price comparison widget using a simple shortcode.
  Author: Monetizer101
  Author URI: https://monetizer101.com
  Version: 1.0.0
 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class Monetizer101_Plugin {

    public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

      // Hook for the shortcode
      add_shortcode('m101widget', array($this, 'shortcode'));

      $this->templates = array();
    }

    public function create_plugin_settings_page() {
    	// Add the menu item and page
    	$page_title = 'Monetizer101 Settings Page';
    	$menu_title = 'Monetizer101';
    	$capability = 'manage_options';
    	$slug       = 'monetizer101';
    	$callback   = array( $this, 'plugin_settings_page_content' );
    	$icon       = 'dashicons-cart';
    	$position   = 100;

    	add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
    }

    /**
     * Custom admin page
     */
    public function plugin_settings_page_content() {
        if( isset( $_POST['updated'] ) && $_POST['updated'] === 'true' ){
            $this->handle_form();
        } ?>
    	<div class="wrap">
    		<h2>Monetizer101 Settings Page</h2>
    		<form method="POST">
                <input type="hidden" name="updated" value="true" />
                <?php wp_nonce_field( 'm101_update', 'm101_form' ); ?>
                <table class="form-table">
                	<tbody>
                        <tr>
                    		<th><label for="m101_api_key">API Key</label></th>
                    		<td><input name="m101_api_key" id="m101_api_key" type="text" value="<?php echo get_option('m101_api_key'); ?>" class="regular-text" /></td>
                    	</tr>
                        <tr>
                    		<th><label for="m101_site_id">Site ID</label></th>
                    		<td>
                          <input name="m101_site_id" id="m101_site_id" type="text" value="<?php echo get_option('m101_site_id'); ?>" class="regular-text" />
                          <p class="description">The unique identifier of the publishers site.</p>
                        </td>
                    	</tr>
                        <tr>
                    		<th><label for="m101_xp">External Partner</label></th>
                    		<td>
                          <input name="m101_xp" id="m101_xp" type="text" value="<?php echo get_option('m101_xp'); ?>" class="regular-text" />                          
                        </td>
                    	</tr>
                	</tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
                </p>
    		</form>
    	</div> <?php
    }

    /**
     * Handle Admin form settings
     */
    public function handle_form() {
        if( ! isset( $_POST['m101_form'] ) || ! wp_verify_nonce( $_POST['m101_form'], 'm101_update' ) ){ ?>
           <div class="error">
               <p>Sorry, your nonce was not correct. Please try again.</p>
           </div> <?php
           exit;
        } else {        
            $api_key = sanitize_text_field( $_POST['m101_api_key'] );
            $site_id = sanitize_text_field( $_POST['m101_site_id'] );
            $xp = sanitize_text_field( $_POST['m101_xp'] );

            if( !empty($api_key) && !empty($site_id) ){
                update_option( 'm101_api_key', $api_key );
                update_option( 'm101_xp', $xp );
                update_option( 'm101_site_id', $site_id );?>
                <div class="updated">
                    <p>Settings were successfully saved.</p>
                </div> <?php
            } else { ?>
                <div class="error">
                    <p>Your API Key or Site ID were invalid.</p>
                </div> <?php
            }
        }
    }

    /**
     * Call the Api Rest and template service
     */
    private function get_data( $url, $api_key = '' )
    {
        $args = array(
            'headers'    => array('X-Api-Key' => $api_key),            
            'user-agent' => 'Monetizer101 WP-Plugin'
        );        
        return wp_remote_retrieve_body( wp_remote_get( $url, $args ) );
    }

    /**
     * Get the user IP
     */
    private function get_ip() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }    

    /**
     * Build the shortcode
     */
	  public function shortcode( $atts = [], $content = null, $tag = '' ) {
        $api_key = get_option( 'm101_api_key' );
        $site_id = get_option( 'm101_site_id' );
        $xp = get_option( 'm101_xp' );

        // normalize attribute keys, lowercase
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );

        // override default attributes with user attributes
        $m101_atts = shortcode_atts(
            array(
                'type'             => 'price-comparison',
                'template'         => 'default',
                'market'           => 'usd_en',
                'geolocation'      => 'false',
                'barcode'          => '',
                'plainlink'        => '',
                'search-keywords'  => '',
                'exclude-keywords' => '',
                'price-range'      => '',
                'filter-merchant'  => '',
                'limit'            => '3',
                'sid'              => '',
                'ip'               => $this->get_ip(),
                'x-forwarded-for'  => '',
                'title'            => '',
                'xp'               => $xp              
            ), $atts, $tag
        );

        $params = array();
        foreach ($m101_atts as $key => $value) {
            if ($value != '' && $key != 'type' && $key != 'template' && $key != 'title' && $key != 'geolocation') {
                array_push($params, $key . '=' . urlencode($value));
            }
        }
        
        // Call the service
        $geolocated = $m101_atts['geolocation'] == 'true' ? 'geolocated' : $m101_atts['market'];        
        $url = 'https://api.monetizer101.com/rest-v3.5/sites/'. $site_id .'/compare/prices/'. $geolocated .'/by/accuracy?' . join("&", $params);
        $result = $this->get_data($url, $api_key);
        $jsonArrayResponse = json_decode($result, true);  
              
        return $this->build_widget($m101_atts['template'], $m101_atts['title'], $jsonArrayResponse);
    }    

    /**
     * Load the template for the widget
     */
    public function get_template($template) {        
        if (!in_array($template, array_unique($this->templates))) {
            $site_id = get_option( 'm101_site_id' );                                    
            if ($template == 'default') {                                
                $result = $this->get_data('https://link.monetizer101.com/css/shop/default/default');
            } else {
                $result = $this->get_data('https://link.monetizer101.com/css/shop/'. $site_id .'/'. $template);                
            }                                    
            wp_enqueue_style( 'aff-widget', plugin_dir_url( __FILE__ ) . 'css/widget.css', array(), false, 'all' );
            wp_add_inline_style( 'aff-widget', $result );            
            array_push($this->templates, $template);                                                      
        }
    }

    /**
     * Build the widget with the data returned by the server
     */
    public function build_widget($template, $title, $data ) {  
        $this->get_template($template);      
        $widget = '<div class="m101-widget '. $template .'">';
        if ($title) {
            $widget .= '<div class="m101-widget-title">'. $title .'</div>';
        }        
        foreach ($data as $item) {
            $productName = $item['name'];
            $deeplink = $item['deeplink'];
            $image = $item['image'];
            $thumbnail = $item['thumbnail'];
            $currency = $this->currency_symbol($item['currency']);
            $salePrice = $currency .''. number_format($item['salePrice'], 2);
            $retailPrice = $currency .''. number_format($item['retailPrice'], 2);
            $discountRate = $item['discountRate'];
            $merchantName = $item['merchant']['name'];
            $merchantLogo = $item['merchant']['logo'];
            $widget .= <<<EOT
            <div class="m101-widget-row">
                <div class="m101-widget-image">
                    <a href="$deeplink" target="_blank" rel="nofollow">
                        <img src="$image">
                    </a>
                </div>
                <div class="m101-widget-product-name">
                    <a href="$deeplink" target="_blank" title="$productName" rel="nofollow">$productName</a>
                </div>     
                <div class="m101-widget-merchant-name">
                    <a href="$deeplink" target="_blank" rel="nofollow">$merchantName</a>
                </div>
                <div class="m101-widget-merchant-image">
                    <a href="$deeplink" target="_blank" rel="nofollow">
                        <img src="$merchantLogo">
                    </a>
                </div>
                <div class="m101-widget-original-price">
                    <a href="$deeplink" target="_blank" rel="nofollow">$retailPrice</a>
                </div>      
                <div class="m101-widget-final-price">
                    <a href="$deeplink" target="_blank" rel="nofollow">$salePrice</a>              
                </div>                    
                <div class="m101-widget-discount">
                    <a href="$deeplink" target="_blank" rel="nofollow">$discountRate</a>              
                </div> 
                <div class="m101-widget-button">
                    <a href="$deeplink" target="_blank" rel="nofollow"></a>
                </div> 
            </div>
EOT;
        }
        $widget .= '</div>';
        return $widget;
    }

    /**
     * Return a currency symbol
     */
    public function currency_symbol($currency) {
        switch ($currency) {
            case 'GBP':
                return '&pound;';
            case 'AUD':
            case 'USD':
            case 'CAD':
              return '&dollar;';
            case 'EUR':
              return '&euro;';
            case 'CHF':
              return 'CHF';
            case 'DKK':
              return 'DKK';
        }        
    }        
}

new Monetizer101_Plugin();