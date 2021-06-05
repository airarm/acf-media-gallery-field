<?php
/*
Plugin Name: Advanced Custom Fields: Media Gallery Field
Description: Media Gallery field for ACF
Version: 1.0
Author: Arman H
Author URI: https://airarm.wordpress.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: acf_mgf
*/
if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACF_Media_Gallery_Field_Init') )
{
    class ACF_Media_Gallery_Field_Init
    {
        var $settings;

        public function __construct()
        {
            $this->settings = array(
                'version' => '1.0.0',
                'url' => plugin_dir_url(__FILE__),
                'path' => plugin_dir_path(__FILE__)
            );

            add_action('acf/include_field_types', array($this, 'include_field')); // v5
            add_action('acf/register_fields', array($this, 'include_field')); // v4
        }

        function include_field( $version = false )
        {
            if(!$version){
                $version = 4;
            }

            load_plugin_textdomain('acf_mgf', false, plugin_basename(dirname( __FILE__ )) . '/lang');

            if($version == 4)
            {
                add_action('admin_notices', array($this, 'show_admin_version_notice'));
                return;
            }

            include_once('fields/acf-media-gallery-field-v' . $version . '.php');
        }

        function show_admin_version_notice()
        {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Please use ACF plugin version 5 or higher');?></p>
            </div>
            <?php
        }
    }

    new ACF_Media_Gallery_Field_Init();
}