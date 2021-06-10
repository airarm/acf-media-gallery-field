<?php
if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACF_Field_Media_Gallery'))
{
    class ACF_Field_Media_Gallery extends acf_field
    {
        var $settings;

        function __construct($settings)
        {
            $this->name = 'media_gallery';
            $this->label = __('Media Gallery');
            $this->category = 'content';
            $this->defaults = array();

            $this->l10n = array(
                'error'	=> __('Error! Please select a media gallery file'),
            );

            $this->settings = $settings;

            if(is_admin()){
                add_filter('acf/validate_value/type=media_gallery', array($this, 'validate_field_value'), 10, 3);
            }

            parent::__construct();
        }

        function render_field_settings($field)
        {
            $return_format_choices = array(
                'array' => __('Items Array'),
                'url' => __('Items URL'),
                'id' => __('Items ID')
            );

            $return_format_choices = apply_filters('acf_mgf_return_format_choices', $return_format_choices, $field);

            acf_render_field_setting($field, array(
                'label' => __('Return Format'),
                'instructions' => '',
                'type' => 'radio',
                'name' => 'return_format',
                'layout' => 'horizontal',
                'choices' => $return_format_choices
            ));
        }

        function render_field($field)
        {
            $media_gallery_ids = !empty($field['value']) ? explode(',', $field['value']) : array();
            $media_gallery_ids = array_map('intval', $media_gallery_ids);
            ?>
            <div id="acf_media_gallery_field_<?php echo $field['ID'];?>" class="acf-media-gallery-field-wrapper" data-active="false">
                <input type="hidden" name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($field['value']) ?>" class="acf-media-gallery-field-input"/>
                <div class="acf-media-gallery-preview">
                    <?php
                    if(!empty($media_gallery_ids))
                    {
                        foreach ($media_gallery_ids as $media_gallery_id)
                        {
                            if(empty($media_gallery_id)){
                                continue;
                            }

                            $media_gallery_edit_url = add_query_arg(array(
                                'post' => $media_gallery_id,
                                'action' => 'edit'
                            ), admin_url('post.php'));
                            $media_gallery_url = wp_get_attachment_image_url($media_gallery_id, 'thumbnail');
                            ?>
                            <div class="item" data-id="<?php echo $media_gallery_id;?>">
                                <img src="<?php echo $media_gallery_url;?>"/>
                                <div class="item-tools">
                                    <a target="_blank" href="<?php echo $media_gallery_edit_url;?>" class="button button--edit">
                                        <i class="dashicons-before dashicons-edit"></i>
                                    </a>
                                    <button type="button" class="button button--remove">
                                        <i class="dashicons-before dashicons-no"></i>
                                    </button>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <button type="button" class="acf-media-gallery-add-btn button button-primary">
                    <span><?php _e('Add Items');?></span>
                </button>
                <button type="button" class="acf-media-gallery-remove-btn button button-secondary">
                    <span><?php _e('Remove All');?></span>
                </button>
            </div>
            <?php
        }

        function input_admin_enqueue_scripts()
        {
            $url = $this->settings['url'];
            $version = $this->settings['version'];

            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-widget');
            wp_enqueue_script('jquery-ui-sortable');

            wp_register_script('acf_media_gallery_field', "{$url}assets/field.js", array('acf-input'), $version);
            wp_enqueue_script('acf_media_gallery_field');

            wp_register_style('acf_media_gallery_field', "{$url}assets/field.css", array('acf-input'), $version);
            wp_enqueue_style('acf_media_gallery_field');
        }

        function validate_field_value($value, $field, $input)
        {
            return $value;
        }

        function format_value($value, $post_id, $field)
        {
            if(empty($value)){
                return false;
            }

            if(is_string($value)){
                $media_gallery_ids = explode(',', $value);
            }elseif (is_array($value)){
                $media_gallery_ids = $value;
            }else{
                $media_gallery_ids = array();
            }

            $media_gallery_ids = array_unique(array_map('intval', $media_gallery_ids));

            if(empty($field['return_format'])){
               return false;
            }

            if($field['return_format'] == 'id'){
                return $media_gallery_ids;
            }

            if($field['return_format'] == 'url')
            {
                return array_map(function($id){
                    return wp_get_attachment_url($id);
                }, $media_gallery_ids);
            }

            $media_posts = new WP_Query(array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'orderby' => 'post__in',
                'post__in' => $media_gallery_ids,
            ));

            if(!$media_posts->post_count){
                return false;
            }

            $media_posts_array = array();

            foreach ($media_posts->posts as $index => $media_post)
            {
                $media_post_arr = acf_array($media_post);

                $metadata = wp_get_attachment_metadata($media_post_arr['ID']);

                foreach ($metadata['sizes'] as $size_index => $size_item)
                {
                    $file_exp = explode('/', $metadata['file']);
                    unset($file_exp[count($file_exp)-1]);
                    $file_folder = implode('/', $file_exp);

                    $metadata['sizes'][$size_index]['url'] = site_url('wp-content/uploads/'.$file_folder.'/'.$size_item['file']);
                }

                $media_post_arr['metadata'] = $metadata;
                $media_posts_array[] = $media_post_arr;
            }

            $value = $media_posts_array;

            $value = apply_filters('acf_mgf_format_value', $value, $post_id, $field);

            return $value;
        }
    }

    new ACF_Field_Media_Gallery($this->settings);
}