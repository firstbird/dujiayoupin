<?php
/*
Plugin Name: Fancy Product Designer PRO Export
Plugin URI: https://fancyproductdesigner.com/
Description: Professional export for Fancy Product Designer.
Version: 1.3.1
Author: fancyproductdesigner.com
Author URI: https://fancyproductdesigner.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('Fancy_Product_Designer_Export') ) {

    class Fancy_Product_Designer_Export {

        const VERSION = '1.3.1';
        const MIN_FPD_VERSION = '4.5.2';
        const MIN_FPD_PLUS_VERSION = '1.2.3';
        const FONTS_DIR = WP_CONTENT_DIR . '/_fpd_pr_fonts/';
        const FPD_EXPORT_REMOTE_URL = 'https://bsdpvidswl.execute-api.us-east-1.amazonaws.com/Prod/dispatch/';
        const ROUTE_NAMESPACE = 'fpd-export/v1.0';
        const TRANSIENT_JOB_KEY = 'fpd__print_job_';
        const ENABLE_PRINT_JOB = true;
        const JOB_TIMEOUT = 180; //in secs

        private $min_fpd_enabled = false;

        public function __construct() {

            add_action( 'plugins_loaded', array( &$this,'plugins_loaded' ) );

        }

        public function plugins_loaded() {

            $this->min_fpd_enabled = class_exists('Fancy_Product_Designer') && version_compare(Fancy_Product_Designer::VERSION, self::MIN_FPD_VERSION, '>=');

            if( $this->min_fpd_enabled ) {

                require_once __DIR__ . '/class-admin.php';
                require_once __DIR__ . '/class-export-provider.php';
                require_once __DIR__ . '/printful/class-printful.php';

            }

            add_action( 'admin_notices',  array( &$this, 'display_admin_notices' ) );

        }

        public function display_admin_notices() {
            
            if( !$this->min_fpd_enabled ) :
            ?>
            <div class="error">
                <p><?php _e( 'Fancy Product Designer Export requires <a href="https://codecanyon.net/item/fancy-product-designer-woocommerce-plugin/6318393?ref=radykal" target="_blank">Fancy Product Designer Plugin for WordPress/WooCommerce V'.self::MIN_FPD_VERSION.'</a>. Please install the plugin, otherwise you can not use the Export Add-On.', 'radykal' ); ?></p>
            </div>
            <?php
            endif;

            if( class_exists('Fancy_Product_Designer_Plus') && version_compare(Fancy_Product_Designer_Plus::VERSION, self::MIN_FPD_PLUS_VERSION, '<') ) :
            ?>
            <div class="error">
                <p><?php _e( 'Please update Fancy Product Designer Plus add-on to '.self::MIN_FPD_PLUS_VERSION.' for the Fancy Product Designer Export add-on.', 'radykal' ); ?></p>
            </div>
            <?php
            endif;

        }

        public static function create_print_ready_file( $print_data, $job_async=true ) {

            $print_data = array_merge( array(
                'print_ready' 			=> true,
                'output_format'		 	=> 'pdf',
                'name' 					=> 'test',
                'svg_data' 				=> array(),
                'used_fonts' 			=> array(),
                'include_font_files' 	=> false,
                'summary_json' 			=> array(),
                'dpi' 					=> 300,
                'include_images' 		=> null,
                'api_version' 			=> 2,
                'hide_crop_marks'		=> fpd_get_option('fpd_ae_hide_crop_marks'),
                'print_job_id'			=> null,
                'file_ready_webhook'	=> get_rest_url( null, self::ROUTE_NAMESPACE  . '/print_job/'),
                'sec_token'             => "fdsfjsadkdsf8s9f8sa9fa9ds8d9adssdla",
            ), $print_data );
            
            if( self::ENABLE_PRINT_JOB && is_null($print_data['print_job_id']) ) {

                $print_job_id = uniqid( time() );
                $transient_value = array(
                    'print_job_id' => $print_job_id
                );

                set_transient( self::TRANSIENT_JOB_KEY . $print_job_id, $transient_value, 300);

                $print_data['print_job_id'] = $print_job_id;

            }

            if( isset($print_data['print_job_id']) )
                $print_data['file_ready_webhook'] .= $print_data['print_job_id'];

            if( !file_exists(FPD_ORDER_DIR) )
                wp_mkdir_p(FPD_ORDER_DIR);

            if( !file_exists(FPD_ORDER_DIR . 'print_ready_files') )
                wp_mkdir_p(FPD_ORDER_DIR . 'print_ready_files');

            $google_webfonts = self::get_google_webfonts();
            $custom_fonts_dir = FPD_WP_CONTENT_DIR.'/uploads/fpd_fonts/';

            $used_fonts = is_array( $print_data['used_fonts'] ) ? $print_data['used_fonts'] : array();
            $fonts_to_embed = array();

            foreach($used_fonts as $used_font) {

                if( !isset($used_font['url']) )
                    continue;

                if( $used_font['url'] == 'google' ) { //google fonts

                    $font_name = $used_font['name'];

                    if(!empty($google_webfonts)) {
                        $font_data = array_filter($google_webfonts, function ($gfont) use (&$font_name) {
                            return $gfont['family'] == $font_name;
                        });
                    }

                    if( !empty($font_data) ) {

                        $font_data = array_pop($font_data);

                        $fonts_to_embed[] = array(
                            'name' => $used_font['name'],
                            'url'  => $font_data['files']['regular']
                        );

                        if( isset($font_data['files']['700']) ) {

                            $fonts_to_embed[] = array(
                                'name' => $used_font['name'].'__bold',
                                'url'  => $font_data['files']['700']
                            );

                        }

                        if( isset($font_data['files']['italic']) ) {

                            $fonts_to_embed[] = array(
                                'name' => $used_font['name'].'__italic',
                                'url'  => $font_data['files']['italic']
                            );

                        }

                        if( isset($font_data['files']['700italic']) ) {

                            $fonts_to_embed[] = array(
                                'name' => $used_font['name'].'__bi',
                                'url'  => $font_data['files']['700italic']
                            );

                        }

                    }

                }
                else if( substr($used_font['url'], 0, 4) == 'http' ) { //custom fonts

                    $fonts_to_embed[] = array(
                        'name' => $used_font['name'],
                        'url'  => $used_font['url']
                    );

                    //deprecated: leave for older orders made with V5.2.9 or earlier. getUsedFonts now includes variants. Remove mid 2022
                    $font_name = pathinfo($used_font['url']);
                    $font_name = $font_name['filename'];

                    if( array_search($used_font['url'], array_column($fonts_to_embed, 'url')) == false ) {

                        if( file_exists($custom_fonts_dir.$font_name.'__bold.ttf') ) {

                            $fonts_to_embed[] = array(
                                'name' => $used_font['name'] . '__bold',
                                'url' => content_url('/uploads/fpd_fonts/'.$font_name.'__bold.ttf')
                            );

                        }

                        if( file_exists($custom_fonts_dir.$font_name.'__italic.ttf') ) {

                            $fonts_to_embed[] = array(
                                'name' => $used_font['name'] . '__italic',
                                'url' => content_url('/uploads/fpd_fonts/'.$font_name.'__italic.ttf')
                            );

                        }

                        if( file_exists($custom_fonts_dir.$font_name.'__bolditalic.ttf') ) {

                            $fonts_to_embed[] = array(
                                'name' => $used_font['name'] . '__bi',
                                'url' => content_url('/uploads/fpd_fonts/'.$font_name.'__bolditalic.ttf')
                            );

                        }

                    }


                }

            }

            $print_data['fonts'] = $fonts_to_embed;
            unset($print_data['used_fonts']);

            $remote_url = Fancy_Product_Designer::LOCAL ? 'http://localhost:3003/' : self::FPD_EXPORT_REMOTE_URL;
            $remote_result = self::http_post_json($remote_url, $print_data);
                
            //get print file directly and save on local server
            if( is_array($remote_result) && isset( $remote_result['file_url'] ) ) {

                $local_file = self::save_remote_file( $remote_result['file_url'] );

                if( $local_file )
                    return $local_file;
                else
                    throw new Exception(__('Local file could not be stored. Please try again!'));

            }
            //create print job and return array with job id
            else if( is_array($remote_result) && isset( $remote_result['print_job_id'] ) ) {

                if(!$job_async) return $remote_result;

                $file_downloaded = false;
                $count = 0;
                $sleep = 2; //sleep for 2 secs

                while(!$file_downloaded) {

                    wp_cache_flush();
                    $local_file = get_transient( self::TRANSIENT_JOB_KEY . $remote_result['print_job_id'] );

                    if($count > intval(self::JOB_TIMEOUT / $sleep) || is_string($local_file)) {

                        if( is_string($local_file) )
                            return explode('/print_ready_files/', $local_file)[1];
                        else
                            throw new Exception(__('The file generation tooks more than 120 seconds. Process canceled!', 'radykal'));

                        $file_downloaded = true;

                    }

                    sleep($sleep);
                    $count++;

                }

            }
            else {

                $error_msg = __('Remote file could not be created. Please try again!', 'radykal');

                if( is_array($remote_result) && isset($remote_result['message']) )
                    $error_msg = $remote_result['message'];

                throw new Exception($error_msg);

            }

        }

        public static function get_google_webfonts() {

            //delete_transient( 'fpd_google_webfonts_lib' );
            $google_webfonts = get_transient( 'fpd_google_webfonts_lib' );

            if ( empty( $google_webfonts ) ) {

                $google_webfonts = fpd_admin_get_file_content( 'https://www.googleapis.com/webfonts/v1/webfonts?key='.Fancy_Product_Designer::GOOGLE_API_KEY );

                if( $google_webfonts === false ) {
                    $google_webfonts = array();
                }
                else {
                    $google_webfonts = json_decode($google_webfonts, true);
                    $google_webfonts = $google_webfonts['items'];
                }

                //no webfonts could be loaded, try again in one min otherwise store them for one week
                set_transient('fpd_google_webfonts_lib', $google_webfonts, sizeof($google_webfonts) === 0 ? 60 : 604800 );

            }

            return $google_webfonts;


        }

        public static function http_post_json($url, $data=null) {

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            if( $data )
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
            );

            $result = curl_exec($ch);
            curl_close($ch);

            return json_decode($result, true);

        }

        public static function save_remote_file( $remote_file_url ) {

            $local_file = basename( $remote_file_url );
            $unique_dir = time().bin2hex(random_bytes(16));
            $temp_dir = FPD_ORDER_DIR . 'print_ready_files/' . $unique_dir;
            mkdir($temp_dir);

            $local_file_path = $temp_dir . '/' . $local_file;

            $result_save = fpd_admin_write_file_content(
                $remote_file_url,
                $local_file_path
            );

            return $result_save ? $unique_dir . '/' . $local_file : null;

        }

    }
}

new Fancy_Product_Designer_Export();

?>