jQuery(document).ready(function ($) {

    var acfMGF_wpMediaLibraryOptions = {
        frame: 'select',
        title: 'Select image',
        multiple: true,
        library: {
            order: 'DESC',
            orderby: 'date',
            type: 'image/jpg,image/jpeg,image/png,image/gif,image/svg+xml'
        },
        button: {
            text: 'Select'
        }
    };

    var acfMGF_wpMediaLibrary = window.wp.media(acfMGF_wpMediaLibraryOptions);

    acfMGF_wpMediaLibrary.on('open', function () {
        if(acfMGF_wpMediaLibrary.content.get() !== null){
            acfMGF_wpMediaLibrary.content.get().collection._requery(true);
        }
    });

    acfMGF_wpMediaLibrary.on('select', function() {
        var selectedImages = acfMGF_wpMediaLibrary.state().get('selection');
        var acfMGF_wrapper = $('.acf-media-gallery-field-wrapper[data-active=true]');

        selectedImages.map(function(attachment){
            attachment = attachment.toJSON();

            var acfMGF_item_html = '<div class="item" data-id="{{ID}}">';
            acfMGF_item_html += '<img src="{{URL}}"/>';
                acfMGF_item_html += '<div class="item-tools">';
                    acfMGF_item_html += '<a target="_blank" href="{{EDIT_URL}}" class="button button--edit">';
                        acfMGF_item_html += '<i class="dashicons-before dashicons-edit"></i>';
                    acfMGF_item_html += '</a>';
                    acfMGF_item_html += '<button type="button" class="button button--remove">';
                        acfMGF_item_html += '<i class="dashicons-before dashicons-no"></i>';
                    acfMGF_item_html += '</button>';
                acfMGF_item_html += '</div>';
            acfMGF_item_html += '</div>';

            acfMGF_item_html = acfMGF_item_html.replaceAll('{{ID}}', attachment.id);
            acfMGF_item_html = acfMGF_item_html.replaceAll('{{URL}}', attachment.sizes.thumbnail.url);

            var acfMGF_item_edit_url = window.location.origin + '/wp-admin/post.php?' + $.param({post: attachment.id, action: 'edit'});
            acfMGF_item_html = acfMGF_item_html.replaceAll('{{EDIT_URL}}', acfMGF_item_edit_url);

            var acfMGF_item = acfMGF_wrapper.find('.item[data-id='+ attachment.id +']');

            if(!acfMGF_item.length){
                acfMGF_wrapper.find('.acf-media-gallery-preview').append(acfMGF_item_html);
            }
        });

        acfMGF_collectionItemsID(acfMGF_wrapper);
        acfMGF_initItemsSortable();
    });

    $(document).on('click', '.acf-media-gallery-add-btn', function () {
        var wrapper = $(this).closest('.acf-media-gallery-field-wrapper');
        wrapper.attr('data-active', 'true');

        acfMGF_wpMediaLibrary.open();
    });

    $(document).on('click', '.acf-media-gallery-remove-btn', function () {
        var acfMGF_wrapper = $(this).closest('.acf-media-gallery-field-wrapper');
        var acfMGF_items = acfMGF_wrapper.find('.item');

        if(acfMGF_items.length > 0 && window.confirm('Are you sure you want to delete all items?'))
        {
            acfMGF_items.remove();
            acfMGF_collectionItemsID(acfMGF_wrapper);
        }
    });

    $(document).on('click', '.acf-media-gallery-preview .item .button--remove', function () {
        var item = $(this).closest('.item');
        var wrapper = item.closest('.acf-media-gallery-field-wrapper');
        wrapper.attr('data-active', 'true');
        item.remove();
        acfMGF_collectionItemsID(wrapper);
    });

    function acfMGF_collectionItemsID(acfMGF_wrapper)
    {
        var acfMGF_items_ids = [];

        acfMGF_wrapper = acfMGF_wrapper || $('.acf-media-gallery-field-wrapper');

        acfMGF_wrapper.find('.acf-media-gallery-preview .item').each(function () {
            acfMGF_items_ids.push($(this).data('id'));
        });

        acfMGF_wrapper.find('.acf-media-gallery-field-input').val(acfMGF_items_ids.join(','));
        acfMGF_wrapper.attr('data-active', 'false');
    }

    var acfMGF_items_sortable_options = {
        cursor: 'move',
        opacity: 0.8,
        placeholder: 'placeholder',
        stop: function (event, ui) {
            var acfMGF_wrapper = $(event.target).closest('.acf-media-gallery-field-wrapper');
            acfMGF_collectionItemsID(acfMGF_wrapper);
        }
    };

   function  acfMGF_initItemsSortable()
   {
       $('.acf-media-gallery-preview').sortable(acfMGF_items_sortable_options).disableSelection();
   }

    acfMGF_initItemsSortable();

});