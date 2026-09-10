<?php
/**
 * ACF Pro options and field groups.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'acf/init',
	function (): void {
		if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => __( 'Blue Theme Options', 'blue-mattress' ),
				'menu_title' => __( 'Theme Options', 'blue-mattress' ),
				'menu_slug'  => 'blue-theme-options',
				'capability' => 'manage_options',
				'redirect'   => false,
				'position'   => 61,
				'icon_url'   => 'dashicons-admin-customizer',
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_blue_finder_page',
				'title'  => __( 'Mattress Finder Page', 'blue-mattress' ),
				'fields' => array(
					array( 'key' => 'field_blue_finder_eyebrow', 'label' => __( 'Eyebrow', 'blue-mattress' ), 'name' => 'finder_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_finder_heading', 'label' => __( 'Heading', 'blue-mattress' ), 'name' => 'finder_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_finder_intro', 'label' => __( 'Introduction', 'blue-mattress' ), 'name' => 'finder_intro', 'type' => 'textarea', 'rows' => 3 ),
				),
				'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-mattress-finder.php' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_blue_story_page',
				'title'  => __( 'Our Story Page', 'blue-mattress' ),
				'fields' => array(
					array( 'key' => 'field_blue_story_page_eyebrow', 'label' => __( 'Hero eyebrow', 'blue-mattress' ), 'name' => 'story_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_story_page_heading', 'label' => __( 'Hero heading', 'blue-mattress' ), 'name' => 'story_hero_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_story_page_lead', 'label' => __( 'Hero introduction', 'blue-mattress' ), 'name' => 'story_hero_lead', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_blue_story_page_hero_image', 'label' => __( 'Hero image', 'blue-mattress' ), 'name' => 'story_hero_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
					array( 'key' => 'field_blue_story_origin_heading', 'label' => __( 'Origin heading', 'blue-mattress' ), 'name' => 'story_origin_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_story_origin_body', 'label' => __( 'Origin body', 'blue-mattress' ), 'name' => 'story_origin_body', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic' ),
					array( 'key' => 'field_blue_story_origin_image', 'label' => __( 'Origin image', 'blue-mattress' ), 'name' => 'story_origin_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
					array( 'key' => 'field_blue_story_craft_heading', 'label' => __( 'Craft heading', 'blue-mattress' ), 'name' => 'story_craft_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_story_craft_text', 'label' => __( 'Craft text', 'blue-mattress' ), 'name' => 'story_craft_text', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_blue_story_craft_image', 'label' => __( 'Craft image', 'blue-mattress' ), 'name' => 'story_craft_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
					array( 'key' => 'field_blue_story_values', 'label' => __( 'Values', 'blue-mattress' ), 'name' => 'story_values', 'type' => 'repeater', 'layout' => 'block', 'button_label' => __( 'Add value', 'blue-mattress' ), 'sub_fields' => array(
						array( 'key' => 'field_blue_story_value_title', 'label' => __( 'Title', 'blue-mattress' ), 'name' => 'title', 'type' => 'text', 'required' => 1 ),
						array( 'key' => 'field_blue_story_value_description', 'label' => __( 'Description', 'blue-mattress' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
					) ),
					array( 'key' => 'field_blue_story_timeline', 'label' => __( 'Timeline', 'blue-mattress' ), 'name' => 'story_timeline', 'type' => 'repeater', 'layout' => 'block', 'button_label' => __( 'Add milestone', 'blue-mattress' ), 'sub_fields' => array(
						array( 'key' => 'field_blue_story_timeline_year', 'label' => __( 'Year / label', 'blue-mattress' ), 'name' => 'year', 'type' => 'text' ),
						array( 'key' => 'field_blue_story_timeline_title', 'label' => __( 'Title', 'blue-mattress' ), 'name' => 'title', 'type' => 'text' ),
						array( 'key' => 'field_blue_story_timeline_description', 'label' => __( 'Description', 'blue-mattress' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
					) ),
					array( 'key' => 'field_blue_story_final_heading', 'label' => __( 'Final CTA heading', 'blue-mattress' ), 'name' => 'story_final_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_story_final_text', 'label' => __( 'Final CTA text', 'blue-mattress' ), 'name' => 'story_final_text', 'type' => 'textarea', 'rows' => 2 ),
				),
				'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-our-story.php' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_blue_stark_page',
				'title'  => __( 'STARK Page', 'blue-mattress' ),
				'fields' => array(
					array( 'key' => 'field_blue_stark_tagline', 'label' => __( 'Tagline', 'blue-mattress' ), 'name' => 'stark_tagline', 'type' => 'text' ),
					array( 'key' => 'field_blue_stark_expansion', 'label' => __( 'STARK name expansion', 'blue-mattress' ), 'name' => 'stark_expansion', 'type' => 'text' ),
					array( 'key' => 'field_blue_stark_facts', 'label' => __( 'Facts', 'blue-mattress' ), 'name' => 'stark_facts', 'type' => 'repeater', 'layout' => 'table', 'button_label' => __( 'Add fact', 'blue-mattress' ), 'sub_fields' => array(
						array( 'key' => 'field_blue_stark_fact_value', 'label' => __( 'Value', 'blue-mattress' ), 'name' => 'value', 'type' => 'text' ),
						array( 'key' => 'field_blue_stark_fact_label', 'label' => __( 'Label', 'blue-mattress' ), 'name' => 'label', 'type' => 'text' ),
					) ),
					array( 'key' => 'field_blue_stark_overview_heading', 'label' => __( 'Overview heading', 'blue-mattress' ), 'name' => 'stark_overview_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_stark_overview_body', 'label' => __( 'Overview body', 'blue-mattress' ), 'name' => 'stark_overview_body', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic' ),
					array( 'key' => 'field_blue_stark_factory_image', 'label' => __( 'Factory image', 'blue-mattress' ), 'name' => 'stark_factory_image', 'type' => 'image', 'return_format' => 'array' ),
					array( 'key' => 'field_blue_stark_heritage_heading', 'label' => __( 'Heritage heading', 'blue-mattress' ), 'name' => 'stark_heritage_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_stark_milestones', 'label' => __( 'Milestones', 'blue-mattress' ), 'name' => 'stark_milestones', 'type' => 'repeater', 'layout' => 'block', 'button_label' => __( 'Add milestone', 'blue-mattress' ), 'sub_fields' => array(
						array( 'key' => 'field_blue_stark_milestone_label', 'label' => __( 'Label', 'blue-mattress' ), 'name' => 'label', 'type' => 'text' ),
						array( 'key' => 'field_blue_stark_milestone_description', 'label' => __( 'Description', 'blue-mattress' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
					) ),
					array( 'key' => 'field_blue_stark_vision', 'label' => __( 'Vision', 'blue-mattress' ), 'name' => 'stark_vision', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_blue_stark_mission', 'label' => __( 'Mission', 'blue-mattress' ), 'name' => 'stark_mission', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_blue_stark_craft_image', 'label' => __( 'Craft image', 'blue-mattress' ), 'name' => 'stark_craft_image', 'type' => 'image', 'return_format' => 'array' ),
					array( 'key' => 'field_blue_stark_craft_caption', 'label' => __( 'Craft caption', 'blue-mattress' ), 'name' => 'stark_craft_caption', 'type' => 'text' ),
					array( 'key' => 'field_blue_stark_wood_image', 'label' => __( 'Wood division image', 'blue-mattress' ), 'name' => 'stark_wood_image', 'type' => 'image', 'return_format' => 'array' ),
					array( 'key' => 'field_blue_stark_wood_text', 'label' => __( 'Wood division text', 'blue-mattress' ), 'name' => 'stark_wood_text', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_stark_bed_image', 'label' => __( 'Mattress division image', 'blue-mattress' ), 'name' => 'stark_bed_image', 'type' => 'image', 'return_format' => 'array' ),
					array( 'key' => 'field_blue_stark_bed_text', 'label' => __( 'Mattress division text', 'blue-mattress' ), 'name' => 'stark_bed_text', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_stark_email', 'label' => __( 'Email', 'blue-mattress' ), 'name' => 'stark_email', 'type' => 'email' ),
					array( 'key' => 'field_blue_stark_website', 'label' => __( 'Website', 'blue-mattress' ), 'name' => 'stark_website', 'type' => 'url' ),
					array( 'key' => 'field_blue_stark_address', 'label' => __( 'Address', 'blue-mattress' ), 'name' => 'stark_address', 'type' => 'text' ),
				),
				'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-stark.php' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key' => 'group_blue_contact_page', 'title' => __( 'Contact Page', 'blue-mattress' ),
				'fields' => array(
					array( 'key' => 'field_blue_contact_eyebrow', 'label' => __( 'Eyebrow', 'blue-mattress' ), 'name' => 'contact_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_contact_heading', 'label' => __( 'Heading', 'blue-mattress' ), 'name' => 'contact_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_contact_intro', 'label' => __( 'Introduction', 'blue-mattress' ), 'name' => 'contact_intro', 'type' => 'textarea', 'rows' => 3 ),
				),
				'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-contact.php' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_blue_product_category_language',
				'title'  => __( 'Arabic Category Content', 'blue-mattress' ),
				'fields' => array(
					array( 'key' => 'field_blue_product_category_name_ar', 'label' => __( 'Category name (Arabic)', 'blue-mattress' ), 'name' => 'product_category_name_ar', 'type' => 'text', 'dir' => 'rtl', 'instructions' => __( 'The standard category name remains the English value and this field is shown on Arabic storefront URLs.', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_product_category_description_ar', 'label' => __( 'Category description (Arabic)', 'blue-mattress' ), 'name' => 'product_category_description_ar', 'type' => 'textarea', 'rows' => 3, 'dir' => 'rtl' ),
				),
				'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'product_cat' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_blue_product_tag_language',
				'title'  => __( 'Arabic Product Tag Content', 'blue-mattress' ),
				'fields' => array(
					array( 'key' => 'field_blue_product_tag_name_ar', 'label' => __( 'Tag name (Arabic)', 'blue-mattress' ), 'name' => 'product_tag_name_ar', 'type' => 'text', 'dir' => 'rtl', 'instructions' => __( 'The standard tag name remains the English value.', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_product_tag_description_ar', 'label' => __( 'Tag description (Arabic)', 'blue-mattress' ), 'name' => 'product_tag_description_ar', 'type' => 'textarea', 'rows' => 3, 'dir' => 'rtl' ),
				),
				'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'product_tag' ) ) ),
			)
		);

		$attribute_term_locations = array();
		$attribute_taxonomies     = function_exists( 'wc_get_attribute_taxonomy_names' )
			? wc_get_attribute_taxonomy_names()
			: array_filter( get_object_taxonomies( 'product' ), fn( $taxonomy ) => str_starts_with( $taxonomy, 'pa_' ) );
		foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
			$attribute_term_locations[] = array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => $attribute_taxonomy ) );
		}
		if ( $attribute_term_locations ) {
			acf_add_local_field_group(
				array(
					'key'    => 'group_blue_product_attribute_term_language',
					'title'  => __( 'Arabic Attribute Option Content', 'blue-mattress' ),
					'fields' => array(
						array( 'key' => 'field_blue_product_attribute_term_name_ar', 'label' => __( 'Option name (Arabic)', 'blue-mattress' ), 'name' => 'product_attribute_term_name_ar', 'type' => 'text', 'dir' => 'rtl', 'instructions' => __( 'For example: Single, Queen or Firm. The standard term name remains English.', 'blue-mattress' ) ),
						array( 'key' => 'field_blue_product_attribute_term_description_ar', 'label' => __( 'Option description (Arabic)', 'blue-mattress' ), 'name' => 'product_attribute_term_description_ar', 'type' => 'textarea', 'rows' => 3, 'dir' => 'rtl' ),
					),
					'location' => $attribute_term_locations,
				)
			);
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_blue_theme_options',
				'title'    => __( 'Theme Options', 'blue-mattress' ),
				'fields'   => array(
					array( 'key' => 'field_blue_tab_global', 'label' => __( 'Global content', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_announcement_en', 'label' => __( 'Announcement (English)', 'blue-mattress' ), 'name' => 'announcement_en', 'type' => 'text', 'default_value' => 'Free delivery and setup across Saudi Arabia.' ),
					array( 'key' => 'field_blue_announcement_ar', 'label' => __( 'Announcement (Arabic)', 'blue-mattress' ), 'name' => 'announcement_ar', 'type' => 'text', 'default_value' => 'توصيل وتركيب مجاني في جميع أنحاء المملكة العربية السعودية.' ),
					array( 'key' => 'field_blue_footer_tagline_en', 'label' => __( 'Footer tagline (English)', 'blue-mattress' ), 'name' => 'footer_tagline_en', 'type' => 'textarea', 'rows' => 2, 'default_value' => 'Feels like magic, but it is really just science.' ),
					array( 'key' => 'field_blue_footer_tagline_ar', 'label' => __( 'Footer tagline (Arabic)', 'blue-mattress' ), 'name' => 'footer_tagline_ar', 'type' => 'textarea', 'rows' => 2, 'default_value' => 'الإحساس كالسحر، لكنه علمٌ خالص.' ),
					array( 'key' => 'field_blue_footer_note_en', 'label' => __( 'Footer description (English)', 'blue-mattress' ), 'name' => 'footer_note_en', 'type' => 'textarea', 'rows' => 3, 'default_value' => 'Where dreams begin — mattresses, pillows, toppers and bedding, delivered across the Kingdom from Alrowdah, Jeddah.' ),
					array( 'key' => 'field_blue_footer_note_ar', 'label' => __( 'Footer description (Arabic)', 'blue-mattress' ), 'name' => 'footer_note_ar', 'type' => 'textarea', 'rows' => 3, 'default_value' => 'حيث تبدأ الأحلام — مراتب ومخدّات ولبّاد ومفارش تصل إلى كل مناطق المملكة من الروضة، جدة.' ),
					array( 'key' => 'field_blue_footer_sale_en', 'label' => __( 'Footer sale label (English)', 'blue-mattress' ), 'name' => 'footer_sale_label_en', 'type' => 'text', 'default_value' => 'Sale', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_footer_sale_ar', 'label' => __( 'Footer sale label (Arabic)', 'blue-mattress' ), 'name' => 'footer_sale_label_ar', 'type' => 'text', 'default_value' => 'التخفيضات', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_footer_logo', 'label' => __( 'Footer logo', 'blue-mattress' ), 'name' => 'footer_logo', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium', 'instructions' => __( 'Use a white or light logo for the navy footer. Falls back to the bundled white logo.', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_newsletter_button_en', 'label' => __( 'Newsletter button (English)', 'blue-mattress' ), 'name' => 'newsletter_button_en', 'type' => 'text', 'default_value' => 'Join', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_newsletter_button_ar', 'label' => __( 'Newsletter button (Arabic)', 'blue-mattress' ), 'name' => 'newsletter_button_ar', 'type' => 'text', 'default_value' => 'اشترك', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_copyright_en', 'label' => __( 'Copyright text (English)', 'blue-mattress' ), 'name' => 'copyright_en', 'type' => 'text' ),
					array( 'key' => 'field_blue_copyright_ar', 'label' => __( 'Copyright text (Arabic)', 'blue-mattress' ), 'name' => 'copyright_ar', 'type' => 'text' ),
					array( 'key' => 'field_blue_commercial_register', 'label' => __( 'Commercial Register', 'blue-mattress' ), 'name' => 'commercial_register', 'type' => 'text', 'default_value' => '7054657023' ),
					array( 'key' => 'field_blue_vat_number', 'label' => __( 'VAT Account Number', 'blue-mattress' ), 'name' => 'vat_number', 'type' => 'text', 'default_value' => '314868318200003' ),
					array( 'key' => 'field_blue_terms_page', 'label' => __( 'Terms page', 'blue-mattress' ), 'name' => 'terms_page', 'type' => 'page_link', 'post_type' => array( 'page' ), 'allow_null' => 1 ),
					array( 'key' => 'field_blue_privacy_page', 'label' => __( 'Privacy page', 'blue-mattress' ), 'name' => 'privacy_page', 'type' => 'page_link', 'post_type' => array( 'page' ), 'allow_null' => 1 ),

					array( 'key' => 'field_blue_tab_contact', 'label' => __( 'Contact details', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_contact_phone', 'label' => __( 'Phone', 'blue-mattress' ), 'name' => 'contact_phone', 'type' => 'text' ),
					array( 'key' => 'field_blue_whatsapp_url', 'label' => __( 'WhatsApp chat URL', 'blue-mattress' ), 'name' => 'whatsapp_url', 'type' => 'url', 'instructions' => __( 'Example: https://wa.me/966500000000', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_contact_email', 'label' => __( 'Public email', 'blue-mattress' ), 'name' => 'contact_email', 'type' => 'email' ),
					array( 'key' => 'field_blue_notification_email', 'label' => __( 'Contact form notification email', 'blue-mattress' ), 'name' => 'contact_notification_email', 'type' => 'email', 'instructions' => __( 'New contact submissions are emailed here. Falls back to the WordPress administration email.', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_address_en', 'label' => __( 'Address (English)', 'blue-mattress' ), 'name' => 'contact_address_en', 'type' => 'textarea', 'rows' => 3, 'default_value' => 'Prince Saud Al Faisal St, Ar Rawdah, Jeddah' ),
					array( 'key' => 'field_blue_address_ar', 'label' => __( 'Address (Arabic)', 'blue-mattress' ), 'name' => 'contact_address_ar', 'type' => 'textarea', 'rows' => 3, 'default_value' => 'شارع الأمير سعود الفيصل، الروضة، جدة' ),
					array( 'key' => 'field_blue_hours_en', 'label' => __( 'Hours (English)', 'blue-mattress' ), 'name' => 'contact_hours_en', 'type' => 'textarea', 'rows' => 3, 'default_value' => "Saturday–Thursday: 9:00–22:00\nFriday: 16:00–22:00" ),
					array( 'key' => 'field_blue_hours_ar', 'label' => __( 'Hours (Arabic)', 'blue-mattress' ), 'name' => 'contact_hours_ar', 'type' => 'textarea', 'rows' => 3, 'default_value' => "السبت–الخميس: 9:00–22:00\nالجمعة: 16:00–22:00" ),
					array( 'key' => 'field_blue_map_url', 'label' => __( 'Google Maps embed URL', 'blue-mattress' ), 'name' => 'contact_map_url', 'type' => 'url', 'instructions' => __( 'Use the URL from the src attribute of a Google Maps embed iframe.', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_map_link', 'label' => __( 'Google Maps public link', 'blue-mattress' ), 'name' => 'contact_map_link', 'type' => 'url', 'instructions' => __( 'Public directions link opened from the address below the map.', 'blue-mattress' ) ),

					array( 'key' => 'field_blue_tab_catalog_language', 'label' => __( 'Catalog translations', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array(
						'key'          => 'field_blue_global_attribute_translations',
						'label'        => __( 'Global attribute labels', 'blue-mattress' ),
						'name'         => 'global_attribute_translations',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add attribute label', 'blue-mattress' ),
						'instructions' => __( 'Add each WooCommerce global attribute once. Use its taxonomy slug, such as pa_size or pa_firmness. Product-level translations can override these values.', 'blue-mattress' ),
						'sub_fields'   => array(
							array( 'key' => 'field_blue_global_attribute_taxonomy', 'label' => __( 'Taxonomy slug', 'blue-mattress' ), 'name' => 'attribute', 'type' => 'text', 'placeholder' => 'pa_size' ),
							array( 'key' => 'field_blue_global_attribute_label_en', 'label' => __( 'Label (English)', 'blue-mattress' ), 'name' => 'label_en', 'type' => 'text' ),
							array( 'key' => 'field_blue_global_attribute_label_ar', 'label' => __( 'Label (Arabic)', 'blue-mattress' ), 'name' => 'label_ar', 'type' => 'text', 'dir' => 'rtl' ),
						),
					),

					array( 'key' => 'field_blue_tab_social', 'label' => __( 'Social media', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array(
						'key'          => 'field_blue_social_links',
						'label'        => __( 'Social links', 'blue-mattress' ),
						'name'         => 'social_links',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add social network', 'blue-mattress' ),
						'sub_fields'   => array(
							array( 'key' => 'field_blue_social_label', 'label' => __( 'Label', 'blue-mattress' ), 'name' => 'label', 'type' => 'text', 'required' => 1 ),
							array( 'key' => 'field_blue_social_url', 'label' => __( 'URL', 'blue-mattress' ), 'name' => 'url', 'type' => 'url', 'required' => 1 ),
							array( 'key' => 'field_blue_social_icon', 'label' => __( 'Icon', 'blue-mattress' ), 'name' => 'icon', 'type' => 'select', 'choices' => array( 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X / Twitter', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'snapchat' => 'Snapchat', 'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp' ), 'default_value' => 'instagram' ),
						),
					),
				),
				'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'blue-theme-options' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_blue_home',
				'title'    => __( 'Homepage Content', 'blue-mattress' ),
				'fields'   => array(
					array( 'key' => 'field_blue_home_tab_hero', 'label' => __( 'Hero', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_hero_heading', 'label' => __( 'Hero heading', 'blue-mattress' ), 'name' => 'hero_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_hero_lead', 'label' => __( 'Hero lead', 'blue-mattress' ), 'name' => 'hero_lead', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_hero_primary_cta', 'label' => __( 'Primary button label', 'blue-mattress' ), 'name' => 'hero_primary_cta', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_hero_secondary_cta', 'label' => __( 'Secondary button label', 'blue-mattress' ), 'name' => 'hero_secondary_cta', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_hero_scroll_label', 'label' => __( 'Scroll label', 'blue-mattress' ), 'name' => 'hero_scroll_label', 'type' => 'text' ),
					array( 'key' => 'field_blue_hero_video', 'label' => __( 'Hero video', 'blue-mattress' ), 'name' => 'hero_video', 'type' => 'file', 'return_format' => 'url', 'mime_types' => 'mp4,webm' ),
					array( 'key' => 'field_blue_hero_poster', 'label' => __( 'Hero poster', 'blue-mattress' ), 'name' => 'hero_poster', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),

					array( 'key' => 'field_blue_home_tab_benefits', 'label' => __( 'Benefits', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_home_benefits_heading', 'label' => __( 'Accessible section heading', 'blue-mattress' ), 'name' => 'home_benefits_heading', 'type' => 'text', 'instructions' => __( 'Visually hidden heading used by screen readers.', 'blue-mattress' ) ),
					array(
						'key'          => 'field_blue_home_benefits',
						'label'        => __( 'Benefit cards', 'blue-mattress' ),
						'name'         => 'home_benefits',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add benefit', 'blue-mattress' ),
						'sub_fields'   => array(
							array( 'key' => 'field_blue_home_benefit_icon', 'label' => __( 'Icon', 'blue-mattress' ), 'name' => 'icon', 'type' => 'select', 'choices' => array( 'trial' => __( 'Night trial', 'blue-mattress' ), 'delivery' => __( 'Delivery', 'blue-mattress' ), 'warranty' => __( 'Warranty', 'blue-mattress' ), 'payments' => __( 'Split payments', 'blue-mattress' ) ), 'wrapper' => array( 'width' => 25 ) ),
							array( 'key' => 'field_blue_home_benefit_title', 'label' => __( 'Title', 'blue-mattress' ), 'name' => 'title', 'type' => 'text', 'required' => 1 ),
							array( 'key' => 'field_blue_home_benefit_description', 'label' => __( 'Description', 'blue-mattress' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
						),
					),

					array( 'key' => 'field_blue_home_tab_collection', 'label' => __( 'Collection', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_collection_eyebrow', 'label' => __( 'Collection eyebrow', 'blue-mattress' ), 'name' => 'collection_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_collection_heading', 'label' => __( 'Collection heading', 'blue-mattress' ), 'name' => 'collection_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_collection_intro', 'label' => __( 'Collection introduction', 'blue-mattress' ), 'name' => 'collection_intro', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_collection_empty', 'label' => __( 'Empty collection message', 'blue-mattress' ), 'name' => 'collection_empty_message', 'type' => 'text' ),

					array( 'key' => 'field_blue_home_tab_anatomy', 'label' => __( 'Anatomy', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_anatomy_eyebrow', 'label' => __( 'Anatomy eyebrow', 'blue-mattress' ), 'name' => 'anatomy_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_anatomy_heading', 'label' => __( 'Anatomy heading', 'blue-mattress' ), 'name' => 'anatomy_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_anatomy_intro', 'label' => __( 'Anatomy introduction', 'blue-mattress' ), 'name' => 'anatomy_intro', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_anatomy_cta', 'label' => __( 'Anatomy button label', 'blue-mattress' ), 'name' => 'anatomy_cta_label', 'type' => 'text' ),
					array(
						'key'          => 'field_blue_anatomy_product',
						'label'        => __( 'Anatomy product', 'blue-mattress' ),
						'name'         => 'anatomy_product',
						'type'         => 'post_object',
						'post_type'    => array( 'product' ),
						'return_format'=> 'id',
						'allow_null'   => 1,
						'instructions' => __( 'Product opened by the Anatomy button. Select BLUE 1 to match the homepage diagram.', 'blue-mattress' ),
					),
					array( 'key' => 'field_blue_anatomy_alt', 'label' => __( 'Anatomy graphic description', 'blue-mattress' ), 'name' => 'anatomy_alt_text', 'type' => 'text', 'instructions' => __( 'Accessible description for the mattress layer graphic.', 'blue-mattress' ) ),
					array(
						'key'          => 'field_blue_anatomy_layers',
						'label'        => __( 'Homepage anatomy layers', 'blue-mattress' ),
						'name'         => 'anatomy_layers',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add anatomy layer', 'blue-mattress' ),
						'instructions' => __( 'Add the editable layers shown in the homepage anatomy graphic.', 'blue-mattress' ),
						'sub_fields'   => array(
							array( 'key' => 'field_blue_anatomy_layer_title', 'label' => __( 'Title', 'blue-mattress' ), 'name' => 'title', 'type' => 'text', 'required' => 1 ),
							array( 'key' => 'field_blue_anatomy_layer_description', 'label' => __( 'Description', 'blue-mattress' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
							array(
								'key'           => 'field_blue_anatomy_layer_visual',
								'label'         => __( 'Layer appearance', 'blue-mattress' ),
								'name'          => 'visual_style',
								'type'          => 'select',
								'choices'       => array(
									'knit'     => __( 'Knit cover', 'blue-mattress' ),
									'quilt'    => __( 'Quilted comfort', 'blue-mattress' ),
									'foam'     => __( 'Comfort foam', 'blue-mattress' ),
									'airflow'  => __( 'Airflow layer', 'blue-mattress' ),
									'springs'  => __( 'Pocket springs', 'blue-mattress' ),
									'base'     => __( 'Support base', 'blue-mattress' ),
								),
								'default_value' => 'foam',
								'wrapper'       => array( 'width' => 50 ),
							),
							array(
								'key'           => 'field_blue_anatomy_layer_image',
								'label'         => __( 'Custom layer image', 'blue-mattress' ),
								'name'          => 'image',
								'type'          => 'image',
								'return_format' => 'id',
								'preview_size'  => 'thumbnail',
								'wrapper'       => array( 'width' => 50 ),
								'instructions'  => __( 'Optional. When set, this image is cropped inside the matching layer.', 'blue-mattress' ),
							),
						),
					),

					array( 'key' => 'field_blue_home_tab_feel', 'label' => __( 'Find Your Feel', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_feel_eyebrow', 'label' => __( 'Section eyebrow', 'blue-mattress' ), 'name' => 'feel_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_feel_heading', 'label' => __( 'Find Your Feel heading', 'blue-mattress' ), 'name' => 'feel_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_feel_intro', 'label' => __( 'Find Your Feel introduction', 'blue-mattress' ), 'name' => 'feel_intro', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_feel_scale_plush', 'label' => __( 'Scale: plush', 'blue-mattress' ), 'name' => 'feel_scale_plush', 'type' => 'text', 'wrapper' => array( 'width' => 33 ) ),
					array( 'key' => 'field_blue_feel_scale_balanced', 'label' => __( 'Scale: balanced', 'blue-mattress' ), 'name' => 'feel_scale_balanced', 'type' => 'text', 'wrapper' => array( 'width' => 34 ) ),
					array( 'key' => 'field_blue_feel_scale_firm', 'label' => __( 'Scale: firm', 'blue-mattress' ), 'name' => 'feel_scale_firm', 'type' => 'text', 'wrapper' => array( 'width' => 33 ) ),
					array( 'key' => 'field_blue_feel_zone_plush', 'label' => __( 'Readout: plush', 'blue-mattress' ), 'name' => 'feel_zone_plush', 'type' => 'text', 'wrapper' => array( 'width' => 33 ) ),
					array( 'key' => 'field_blue_feel_zone_balanced', 'label' => __( 'Readout: balanced', 'blue-mattress' ), 'name' => 'feel_zone_balanced', 'type' => 'text', 'wrapper' => array( 'width' => 34 ) ),
					array( 'key' => 'field_blue_feel_zone_firm', 'label' => __( 'Readout: firm', 'blue-mattress' ), 'name' => 'feel_zone_firm', 'type' => 'text', 'wrapper' => array( 'width' => 33 ) ),
					array( 'key' => 'field_blue_feel_recommendation', 'label' => __( 'Recommendation sentence', 'blue-mattress' ), 'name' => 'feel_recommendation_template', 'type' => 'text', 'instructions' => __( 'Use {value}, {name}, and {description}. Example: At {value}, you want {name} — {description}', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_feel_recommendation_fallback', 'label' => __( 'Recommendation fallback', 'blue-mattress' ), 'name' => 'feel_recommendation_fallback', 'type' => 'text' ),
					array( 'key' => 'field_blue_feel_more_text', 'label' => __( 'Finder prompt', 'blue-mattress' ), 'name' => 'feel_more_text', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_feel_cta_label', 'label' => __( 'Finder button label', 'blue-mattress' ), 'name' => 'feel_cta_label', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_feel_empty', 'label' => __( 'No firmness products message', 'blue-mattress' ), 'name' => 'feel_empty_message', 'type' => 'text' ),

					array( 'key' => 'field_blue_home_tab_categories', 'label' => __( 'Categories', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_categories_eyebrow', 'label' => __( 'Categories eyebrow', 'blue-mattress' ), 'name' => 'categories_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_categories_heading', 'label' => __( 'Categories heading', 'blue-mattress' ), 'name' => 'categories_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_categories_intro', 'label' => __( 'Categories introduction', 'blue-mattress' ), 'name' => 'categories_intro', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_categories_empty', 'label' => __( 'Empty categories message', 'blue-mattress' ), 'name' => 'categories_empty_message', 'type' => 'text' ),

					array( 'key' => 'field_blue_home_tab_story', 'label' => __( 'Story', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_home_story_eyebrow', 'label' => __( 'Story eyebrow', 'blue-mattress' ), 'name' => 'home_story_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_story_heading', 'label' => __( 'Story heading', 'blue-mattress' ), 'name' => 'story_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_story_body', 'label' => __( 'Story body', 'blue-mattress' ), 'name' => 'story_body', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0 ),
					array( 'key' => 'field_blue_story_image', 'label' => __( 'Story image', 'blue-mattress' ), 'name' => 'story_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),

					array( 'key' => 'field_blue_home_tab_reviews', 'label' => __( 'Reviews', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_reviews_eyebrow', 'label' => __( 'Reviews eyebrow', 'blue-mattress' ), 'name' => 'reviews_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_reviews_heading', 'label' => __( 'Reviews heading', 'blue-mattress' ), 'name' => 'reviews_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_reviews_intro', 'label' => __( 'Reviews introduction', 'blue-mattress' ), 'name' => 'reviews_intro', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_reviews_dot_label', 'label' => __( 'Review navigation label', 'blue-mattress' ), 'name' => 'reviews_dot_label', 'type' => 'text', 'instructions' => __( 'Use {number} for the review number.', 'blue-mattress' ) ),

					array( 'key' => 'field_blue_home_tab_lifestyle', 'label' => __( 'Lifestyle', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_lifestyle_image', 'label' => __( 'Lifestyle image', 'blue-mattress' ), 'name' => 'lifestyle_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
					array( 'key' => 'field_blue_lifestyle_quote', 'label' => __( 'Lifestyle quote', 'blue-mattress' ), 'name' => 'lifestyle_quote', 'type' => 'text' ),
					array( 'key' => 'field_blue_lifestyle_cta', 'label' => __( 'Lifestyle button label', 'blue-mattress' ), 'name' => 'lifestyle_cta_label', 'type' => 'text' ),

					array( 'key' => 'field_blue_home_tab_stark', 'label' => __( 'STARK', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_home_stark_image', 'label' => __( 'STARK image', 'blue-mattress' ), 'name' => 'home_stark_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
					array( 'key' => 'field_blue_home_stark_image_alt', 'label' => __( 'STARK image description', 'blue-mattress' ), 'name' => 'home_stark_image_alt', 'type' => 'text', 'instructions' => __( 'Accessible alternative text for the factory image.', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_home_stark_eyebrow', 'label' => __( 'STARK eyebrow', 'blue-mattress' ), 'name' => 'home_stark_eyebrow', 'type' => 'text' ),
					array( 'key' => 'field_blue_home_stark_heading', 'label' => __( 'STARK heading', 'blue-mattress' ), 'name' => 'home_stark_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_home_stark_intro', 'label' => __( 'STARK introduction', 'blue-mattress' ), 'name' => 'home_stark_intro', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_blue_home_stark_materials', 'label' => __( 'STARK materials paragraph', 'blue-mattress' ), 'name' => 'home_stark_materials', 'type' => 'textarea', 'rows' => 3 ),
					array(
						'key'          => 'field_blue_home_stark_stats',
						'label'        => __( 'STARK statistics', 'blue-mattress' ),
						'name'         => 'home_stark_stats',
						'type'         => 'repeater',
						'layout'       => 'table',
						'min'          => 1,
						'max'          => 4,
						'button_label' => __( 'Add statistic', 'blue-mattress' ),
						'sub_fields'   => array(
							array( 'key' => 'field_blue_home_stark_stat_value', 'label' => __( 'Value', 'blue-mattress' ), 'name' => 'value', 'type' => 'number', 'min' => 0, 'wrapper' => array( 'width' => 20 ) ),
							array( 'key' => 'field_blue_home_stark_stat_unit', 'label' => __( 'Unit', 'blue-mattress' ), 'name' => 'unit', 'type' => 'text', 'wrapper' => array( 'width' => 20 ) ),
							array( 'key' => 'field_blue_home_stark_stat_label', 'label' => __( 'Label', 'blue-mattress' ), 'name' => 'label', 'type' => 'text', 'wrapper' => array( 'width' => 60 ) ),
						),
					),
					array( 'key' => 'field_blue_home_stark_cta', 'label' => __( 'STARK button label', 'blue-mattress' ), 'name' => 'home_stark_cta_label', 'type' => 'text' ),

					array( 'key' => 'field_blue_home_tab_final', 'label' => __( 'Final CTA', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_final_heading', 'label' => __( 'Final CTA heading', 'blue-mattress' ), 'name' => 'final_cta_heading', 'type' => 'text' ),
					array( 'key' => 'field_blue_final_text', 'label' => __( 'Final CTA text', 'blue-mattress' ), 'name' => 'final_cta_text', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_blue_final_primary_cta', 'label' => __( 'Primary button label', 'blue-mattress' ), 'name' => 'final_primary_cta', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_final_secondary_cta', 'label' => __( 'Secondary button label', 'blue-mattress' ), 'name' => 'final_secondary_cta', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
				),
				'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-home.php' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_blue_product',
				'title'    => __( 'Blue Product Details', 'blue-mattress' ),
				'fields'   => array(
					array( 'key' => 'field_blue_product_tab_language', 'label' => __( 'English & Arabic content', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array( 'key' => 'field_blue_product_title_en', 'label' => __( 'Product title (English)', 'blue-mattress' ), 'name' => 'product_title_en', 'type' => 'text', 'instructions' => __( 'Optional. Falls back to the main WooCommerce product name.', 'blue-mattress' ), 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_title_ar', 'label' => __( 'Product title (Arabic)', 'blue-mattress' ), 'name' => 'product_title_ar', 'type' => 'text', 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_short_en', 'label' => __( 'Short description (English)', 'blue-mattress' ), 'name' => 'product_short_description_en', 'type' => 'textarea', 'rows' => 3, 'instructions' => __( 'Optional. Falls back to the WooCommerce short description.', 'blue-mattress' ), 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_short_ar', 'label' => __( 'Short description (Arabic)', 'blue-mattress' ), 'name' => 'product_short_description_ar', 'type' => 'textarea', 'rows' => 3, 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_description_en', 'label' => __( 'Full description (English)', 'blue-mattress' ), 'name' => 'product_description_en', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'instructions' => __( 'Optional. Falls back to the WooCommerce description.', 'blue-mattress' ), 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_description_ar', 'label' => __( 'Full description (Arabic)', 'blue-mattress' ), 'name' => 'product_description_ar', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_kind', 'label' => __( 'Product kind / technology (English)', 'blue-mattress' ), 'name' => 'product_kind', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_kind_ar', 'label' => __( 'Product kind / technology (Arabic)', 'blue-mattress' ), 'name' => 'product_kind_ar', 'type' => 'text', 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_badge', 'label' => __( 'Product card badge (English)', 'blue-mattress' ), 'name' => 'product_badge', 'type' => 'text', 'instructions' => __( 'Optional label such as Best seller or Luxury. The badge is displayed on product cards throughout the site.', 'blue-mattress' ), 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_badge_ar', 'label' => __( 'Product card badge (Arabic)', 'blue-mattress' ), 'name' => 'product_badge_ar', 'type' => 'text', 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_tab_shared', 'label' => __( 'Shared commerce & specifications', 'blue-mattress' ), 'name' => '', 'type' => 'tab' ),
					array(
						'key'           => 'field_blue_product_pairings',
						'label'         => __( 'Pairs well with', 'blue-mattress' ),
						'name'          => 'product_pairings',
						'type'          => 'relationship',
						'instructions'  => __( 'Choose up to three products for the Complete the bed section. Cross-sells, upsells and related products fill empty spaces.', 'blue-mattress' ),
						'post_type'     => array( 'product' ),
						'filters'       => array( 'search', 'taxonomy' ),
						'return_format' => 'id',
						'max'           => 3,
					),
					array( 'key' => 'field_blue_product_core', 'label' => __( 'Construction title (English)', 'blue-mattress' ), 'name' => 'product_core', 'type' => 'text', 'instructions' => __( 'Main heading above the construction layers. Example: The technology inside, named.', 'blue-mattress' ), 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_core_ar', 'label' => __( 'Construction title (Arabic)', 'blue-mattress' ), 'name' => 'product_core_ar', 'type' => 'text', 'dir' => 'rtl', 'instructions' => __( 'Arabic main heading above the construction layers.', 'blue-mattress' ), 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_construction_intro', 'label' => __( 'Construction introduction (English)', 'blue-mattress' ), 'name' => 'product_construction_intro', 'type' => 'textarea', 'rows' => 3, 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_construction_intro_ar', 'label' => __( 'Construction introduction (Arabic)', 'blue-mattress' ), 'name' => 'product_construction_intro_ar', 'type' => 'textarea', 'rows' => 3, 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_feel', 'label' => __( 'Firmness (1–10)', 'blue-mattress' ), 'name' => 'product_feel', 'type' => 'range', 'min' => 1, 'max' => 10, 'step' => 1 ),
					array( 'key' => 'field_blue_product_feel_label', 'label' => __( 'Firmness label (English)', 'blue-mattress' ), 'name' => 'product_feel_label', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_feel_label_ar', 'label' => __( 'Firmness label (Arabic)', 'blue-mattress' ), 'name' => 'product_feel_label_ar', 'type' => 'text', 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_sleep_positions', 'label' => __( 'Recommended sleep positions', 'blue-mattress' ), 'name' => 'product_sleep_positions', 'type' => 'checkbox', 'choices' => array( 'side' => __( 'Side', 'blue-mattress' ), 'back' => __( 'Back', 'blue-mattress' ), 'stomach' => __( 'Stomach', 'blue-mattress' ), 'mixed' => __( 'Combination', 'blue-mattress' ) ), 'layout' => 'horizontal', 'return_format' => 'value' ),
					array( 'key' => 'field_blue_product_cooling', 'label' => __( 'Cooling performance (1–10)', 'blue-mattress' ), 'name' => 'product_cooling', 'type' => 'range', 'min' => 1, 'max' => 10, 'step' => 1, 'default_value' => 6 ),
					array( 'key' => 'field_blue_product_motion', 'label' => __( 'Motion isolation (1–10)', 'blue-mattress' ), 'name' => 'product_motion_isolation', 'type' => 'range', 'min' => 1, 'max' => 10, 'step' => 1, 'default_value' => 6 ),
					array( 'key' => 'field_blue_product_height', 'label' => __( 'Height (cm)', 'blue-mattress' ), 'name' => 'product_height', 'type' => 'number', 'min' => 1, 'append' => 'cm' ),
					array( 'key' => 'field_blue_product_assurance_trial', 'label' => __( 'Show 50-night trial', 'blue-mattress' ), 'name' => 'product_assurance_trial', 'type' => 'true_false', 'default_value' => 1, 'wrapper' => array( 'width' => 25 ) ),
					array( 'key' => 'field_blue_product_assurance_delivery', 'label' => __( 'Show Kingdom-wide delivery', 'blue-mattress' ), 'name' => 'product_assurance_delivery', 'type' => 'true_false', 'default_value' => 1, 'wrapper' => array( 'width' => 25 ) ),
					array( 'key' => 'field_blue_product_assurance_warranty', 'label' => __( 'Show 10-year warranty', 'blue-mattress' ), 'name' => 'product_assurance_warranty', 'type' => 'true_false', 'default_value' => 1, 'wrapper' => array( 'width' => 25 ) ),
					array( 'key' => 'field_blue_product_delivery', 'label' => __( 'Delivery & returns (English)', 'blue-mattress' ), 'name' => 'product_delivery_returns', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_delivery_ar', 'label' => __( 'Delivery & returns (Arabic)', 'blue-mattress' ), 'name' => 'product_delivery_returns_ar', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_warranty', 'label' => __( 'Warranty (English)', 'blue-mattress' ), 'name' => 'product_warranty', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_warranty_ar', 'label' => __( 'Warranty (Arabic)', 'blue-mattress' ), 'name' => 'product_warranty_ar', 'type' => 'wysiwyg', 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'wrapper' => array( 'width' => 50 ) ),
					array( 'key' => 'field_blue_product_wide_image', 'label' => __( 'Wide lifestyle image', 'blue-mattress' ), 'name' => 'product_wide_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'large', 'instructions' => __( 'Full-width image shown between the purchase area and construction section. A landscape image at least 1600 pixels wide is recommended.', 'blue-mattress' ) ),
					array( 'key' => 'field_blue_product_cutaway', 'label' => __( 'Cutaway image', 'blue-mattress' ), 'name' => 'product_cutaway_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
					array( 'key' => 'field_blue_product_cutaway_video', 'label' => __( 'Cutaway animation', 'blue-mattress' ), 'name' => 'product_cutaway_video', 'type' => 'file', 'return_format' => 'array', 'mime_types' => 'mp4,webm', 'instructions' => __( 'Optional MP4 or WebM used when the product image is hovered and in the construction section. The matching animation from the supplied design pack is used when this is empty.', 'blue-mattress' ) ),
					array(
						'key'          => 'field_blue_product_layers',
						'label'        => __( 'Core technologies / construction layers', 'blue-mattress' ),
						'name'         => 'product_layers',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add layer', 'blue-mattress' ),
						'sub_fields'   => array(
							array( 'key' => 'field_blue_layer_title', 'label' => __( 'Title (English)', 'blue-mattress' ), 'name' => 'title', 'type' => 'text', 'required' => 1, 'wrapper' => array( 'width' => 50 ) ),
							array( 'key' => 'field_blue_layer_title_ar', 'label' => __( 'Title (Arabic)', 'blue-mattress' ), 'name' => 'title_ar', 'type' => 'text', 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
							array( 'key' => 'field_blue_layer_description', 'label' => __( 'Description (English)', 'blue-mattress' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2, 'wrapper' => array( 'width' => 50 ) ),
							array( 'key' => 'field_blue_layer_description_ar', 'label' => __( 'Description (Arabic)', 'blue-mattress' ), 'name' => 'description_ar', 'type' => 'textarea', 'rows' => 2, 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
						),
					),
					array(
						'key'          => 'field_blue_product_attribute_translations',
						'label'        => __( 'Variation attribute translations', 'blue-mattress' ),
						'name'         => 'product_attribute_translations',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add attribute translation', 'blue-mattress' ),
						'instructions' => __( 'Use the WooCommerce attribute slug, for example pa_size. Option values must match their saved slugs, for example queen.', 'blue-mattress' ),
						'sub_fields'   => array(
							array( 'key' => 'field_blue_attribute_name', 'label' => __( 'Attribute slug', 'blue-mattress' ), 'name' => 'attribute', 'type' => 'text', 'placeholder' => 'pa_size' ),
							array( 'key' => 'field_blue_attribute_label_en', 'label' => __( 'Label (English)', 'blue-mattress' ), 'name' => 'label_en', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
							array( 'key' => 'field_blue_attribute_label_ar', 'label' => __( 'Label (Arabic)', 'blue-mattress' ), 'name' => 'label_ar', 'type' => 'text', 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
							array(
								'key'          => 'field_blue_attribute_options',
								'label'        => __( 'Option translations', 'blue-mattress' ),
								'name'         => 'options',
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'Add option', 'blue-mattress' ),
								'sub_fields'   => array(
									array( 'key' => 'field_blue_attribute_option_value', 'label' => __( 'Saved value / slug', 'blue-mattress' ), 'name' => 'value', 'type' => 'text' ),
									array( 'key' => 'field_blue_attribute_option_en', 'label' => __( 'English label', 'blue-mattress' ), 'name' => 'label_en', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
									array( 'key' => 'field_blue_attribute_option_ar', 'label' => __( 'Arabic label', 'blue-mattress' ), 'name' => 'label_ar', 'type' => 'text', 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
									array( 'key' => 'field_blue_attribute_option_description_en', 'label' => __( 'English description', 'blue-mattress' ), 'name' => 'description_en', 'type' => 'textarea', 'rows' => 2, 'wrapper' => array( 'width' => 50 ) ),
									array( 'key' => 'field_blue_attribute_option_description_ar', 'label' => __( 'Arabic description', 'blue-mattress' ), 'name' => 'description_ar', 'type' => 'textarea', 'rows' => 2, 'dir' => 'rtl', 'wrapper' => array( 'width' => 50 ) ),
								),
							),
						),
					),
				),
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ) ),
			)
		);
	}
);

/** The four editable BLUE 1 technologies used by the current demo. */
function blue_homepage_anatomy_layers( string $language ): array {
	if ( 'ar' === $language ) {
		return array(
			array( 'title' => 'نظام التبريد COMBOCOOL™', 'description' => 'برودة مستدامة تنظّم الحرارة والرطوبة بفعالية، عبر قماش بتكنولوجيا مزدوجة مصنوع من محتوى بيولوجي يزيد عن 50% مشتق من زيت نباتي معتمد من وزارة الزراعة الأمريكية.', 'visual_style' => 'knit', 'image' => '' ),
			array( 'title' => 'طبقة TPE لتوزيع الضغط', 'description' => 'توزّع وزنك بالتساوي على سطح المرتبة فيقلّ الضغط على نقاط الاتصال، بينما توفّر النوابض المنفصلة دعمًا متكيّفًا مع حركات الجسم.', 'visual_style' => 'foam', 'image' => '' ),
			array( 'title' => 'قماش PUROTEX+™ المضاد للحساسية', 'description' => 'يخفض مسببات الحساسية من عثّ الغبار بنسبة 96.6%، ومن شعر القطط بنسبة 83.6%، ومن شعر الكلاب بنسبة 76.5%. حاصل على شهادات Allergy UK وOEKO-TEX. تركيبة طبيعية 100% غير مهيّجة للبشرة وتظل فعّالة حتى بعد عشرين غسلة منزلية.', 'visual_style' => 'knit', 'image' => '' ),
			array( 'title' => 'أساس نوابض مستقلّة بخمس مناطق', 'description' => 'نوابض مغلّفة بشكل مستقل توزّع الوزن على خمس مناطق (الرجل، الركبة، الخصر، الأكتاف، الرأس) لدعم مثالي، وتقلّل انتقال الحركة لضمان نوم هادئ.', 'visual_style' => 'springs', 'image' => '' ),
		);
	}

	return array(
		array( 'title' => 'COMBOCOOL™ cooling system', 'description' => 'Sustained cooling that regulates heat and moisture, in a dual-technology fabric made with over 50% bio-based content derived from USDA-certified vegetable oil.', 'visual_style' => 'knit', 'image' => '' ),
		array( 'title' => 'TPE pressure-relief layer', 'description' => 'Spreads your weight evenly across the surface so contact points carry less load, easing pressure on the body while the independent springs adapt to every movement.', 'visual_style' => 'foam', 'image' => '' ),
		array( 'title' => 'PUROTEX+™ anti-allergen fabric', 'description' => 'Cuts dust-mite allergens by 96.6%, cat hair by 83.6% and dog hair by 76.5%. Certified by Allergy UK and OEKO-TEX. A 100% natural formulation that is non-irritating to skin and stays effective after twenty home washes.', 'visual_style' => 'knit', 'image' => '' ),
		array( 'title' => '5-zone pocket-spring support core', 'description' => 'Individually pocketed springs spread weight across five zones — legs, knees, waist, shoulders and head — for balanced support, and cut motion transfer so a partner turning over does not wake you.', 'visual_style' => 'springs', 'image' => '' ),
	);
}

/** Initial editable homepage copy, saved into ACF instead of rendered as template constants. */
function blue_homepage_seed_content( string $language ): array {
	if ( 'ar' === $language ) {
		return array(
			'hero_heading' => 'حيث تبدأ الأحلام.',
			'hero_lead' => 'إحساسٌ كالسحر، لكنه في الحقيقة مجرّد علم.',
			'hero_primary_cta' => 'تسوّق المراتب',
			'hero_secondary_cta' => 'جرّب مرشد المراتب',
			'hero_scroll_label' => 'مرّر',
			'home_benefits_heading' => 'لماذا مراتب بلو',
			'home_benefits' => array(
				array( 'icon' => 'trial', 'title' => 'تجربة 50 ليلة', 'description' => 'خمسون ليلة لتطمئن، مع واقي مرتبة مجّاني. لم تناسبك؟ نستبدلها لك أوّلًا.' ),
				array( 'icon' => 'delivery', 'title' => 'توصيل إلى كل المملكة', 'description' => 'توصيل إلى مناطق المملكة، مباشرة من مصنعنا في جدة.' ),
				array( 'icon' => 'warranty', 'title' => 'ضمان 10 سنوات', 'description' => 'عشر سنوات من الضمان على كل نابض وطبقة وغرزة.' ),
				array( 'icon' => 'payments', 'title' => 'دفعات مقسّطة', 'description' => 'أربع دفعات ميسّرة. بلا فوائد، وبلا عناء.' ),
			),
			'collection_eyebrow' => 'التشكيلة',
			'collection_heading' => 'ثمانية أسرّة. وعد واحد.',
			'collection_intro' => 'ثماني مراتب، كل واحدة تُصنع حسب الطلب لنمط نوم مختلف. الأصعب أن تختار واحدة فقط — وهذا ما سنساعدك فيه.',
			'collection_empty_message' => 'أضف منتجات ووكومرس منشورة لعرض التشكيلة.',
			'anatomy_eyebrow' => 'ماذا في الداخل',
			'anatomy_heading' => 'تشريح مرتبة بلو.',
			'anatomy_intro' => 'التقنيات ونواة الدعم داخل بلو 1، مرتبتنا الهجينة المميزة — وفق مواصفات المصنّع. الرسم توضيحي وغير مطابق للمقياس.',
			'anatomy_cta_label' => 'اكتشف بلو 1',
			'anatomy_alt_text' => 'مقطع تفصيلي يوضح التقنيات داخل مرتبة بلو 1',
			'anatomy_layers' => blue_homepage_anatomy_layers( 'ar' ),
			'feel_eyebrow' => 'مؤشر الصلابة',
			'feel_heading' => 'اعثر على إحساسك.',
			'feel_intro' => 'كل مراتب بلو متوسّطة الليونة — حرّك المؤشر عبر النطاق لتجد موضعك الأنسب فيه.',
			'feel_scale_plush' => '1 · أطرى',
			'feel_scale_balanced' => '5 · متوازنة',
			'feel_scale_firm' => '10 · أثبت',
			'feel_zone_plush' => 'ناعمة',
			'feel_zone_balanced' => 'متوازنة',
			'feel_zone_firm' => 'صلبة',
			'feel_recommendation_template' => 'عند {value}، خيارك هو {name} — {description}',
			'feel_recommendation_fallback' => 'أقرب درجة صلابة إلى اختيارك.',
			'feel_more_text' => 'هل تريد إجابة موجهة؟',
			'feel_cta_label' => 'افتح مرشد المراتب',
			'feel_empty_message' => 'أضف قيمة صلابة للمنتجات لعرض النتائج.',
			'categories_eyebrow' => 'تسوّق حسب الفئة',
			'categories_heading' => 'كل ما يرتديه السرير.',
			'categories_intro' => 'من المرتبة فما فوق — مخدّات ولبّاد ومفارش بألوان الفجر، مفصّلة من القماش الهادئ نفسه.',
			'categories_empty_message' => 'أضف فئات منتجات مع صور لعرض هذا القسم.',
			'home_story_eyebrow' => 'صُنع في جدة',
			'story_heading' => 'صُنعت لصباحات أفضل.',
			'story_body' => '<p>تجمع بلو بين المعرفة الصناعية السعودية والمواد المدروسة بعناية لصناعة نوم أهدأ وصباح أفضل.</p>',
			'reviews_eyebrow' => 'التقييمات',
			'reviews_heading' => 'محبوبة في كل مناطق المملكة.',
			'reviews_intro' => 'صباحاتٌ يرويها عملاؤنا، من الرياض إلى الخبر.',
			'reviews_dot_label' => 'الانتقال إلى التقييم {number}',
			'lifestyle_quote' => '«أجمل الصباحات تُصنع في الليلة السابقة.»',
			'lifestyle_cta_label' => 'افتح مرشد المراتب',
			'home_stark_eyebrow' => 'الشركة الأم',
			'home_stark_heading' => 'خلف بلو تقف ستارك.',
			'home_stark_intro' => 'تُصنع كل مرتبة من مراتب بلو لدى ستارك على مساحة 60,000 م² من المنشآت وبأكثر من 200 متخصّص، في صناعة الأخشاب والأثاث والنوم منذ 1967.',
			'home_stark_materials' => 'وتُختار الخامات لتناسب المناخ: أقمشة COMBOCOOL™ التي تنظّم الحرارة والرطوبة، ولاتكس عضوي 100% بشهادة GOLS، وقلوب نوابض مستقلّة موزّعة على خمس مناطق للجسم.',
			'home_stark_image_alt' => 'خط إنتاج ستارك في جدة حيث تُصنع مراتب بلو',
			'home_stark_stats' => array(
				array( 'value' => 8, 'unit' => '', 'label' => 'طُرز مراتب' ),
				array( 'value' => 35, 'unit' => 'سم', 'label' => 'أسمك مرتبة' ),
				array( 'value' => 50, 'unit' => '', 'label' => 'ليلة تجربة' ),
				array( 'value' => 10, 'unit' => 'سنوات', 'label' => 'ضمان' ),
			),
			'home_stark_cta_label' => 'تعرّف على الشركة الأم',
			'final_cta_heading' => 'جرّب بلو لمدة 50 ليلة.',
			'final_cta_text' => 'خمسون ليلة في منزلك مع واقٍ مجاني. إن لم تناسبك المرتبة نستبدلها أولاً، ثم نسترد قيمتها إن لم تكن البديلة مناسبة أيضاً.',
			'final_primary_cta' => 'تسوّق التشكيلة',
			'final_secondary_cta' => 'افتح مرشد المراتب',
		);
	}

	return array(
		'hero_heading' => 'Where dreams begin.',
		'hero_lead' => 'Feels like magic, but it’s really just science.',
		'hero_primary_cta' => 'Shop mattresses',
		'hero_secondary_cta' => 'Try the Mattress Finder',
		'hero_scroll_label' => 'Scroll',
		'home_benefits_heading' => 'Why Blue Mattresses',
		'home_benefits' => array(
			array( 'icon' => 'trial', 'title' => '50-Night Trial', 'description' => 'Fifty nights to be sure, with a free protector included. Not right? We exchange it first.' ),
			array( 'icon' => 'delivery', 'title' => 'Delivered Kingdom-wide', 'description' => 'Delivered across the Kingdom, direct from our Jeddah plant.' ),
			array( 'icon' => 'warranty', 'title' => '10-Year Warranty', 'description' => 'A decade of cover on every coil, layer and seam.' ),
			array( 'icon' => 'payments', 'title' => 'Split Payments', 'description' => 'Four gentle payments. Zero interest, zero drama.' ),
		),
		'collection_eyebrow' => 'The collection',
		'collection_heading' => 'Eight beds. One promise.',
		'collection_intro' => 'Eight mattresses, each made to order for a different kind of sleeper. The hard part is just picking one — so we’ll help you do that too.',
		'collection_empty_message' => 'Add published WooCommerce products to display the collection.',
		'anatomy_eyebrow' => 'What’s inside',
		'anatomy_heading' => 'Anatomy of a Blue.',
		'anatomy_intro' => 'The technologies and support core inside BLUE 1, our signature hybrid — as specified by the manufacturer. Schematic, not to scale.',
		'anatomy_cta_label' => 'Explore BLUE 1',
		'anatomy_alt_text' => 'Exploded cross-section showing the technologies inside the BLUE 1 mattress',
		'anatomy_layers' => blue_homepage_anatomy_layers( 'en' ),
		'feel_eyebrow' => 'The firmness index',
		'feel_heading' => 'Find your feel.',
		'feel_intro' => 'Every Blue is a medium mattress — slide along the range to find where in it you sleep best.',
		'feel_scale_plush' => '1 · Softer',
		'feel_scale_balanced' => '5 · Balanced',
		'feel_scale_firm' => '10 · Firmer',
		'feel_zone_plush' => 'Plush',
		'feel_zone_balanced' => 'Balanced',
		'feel_zone_firm' => 'Firm',
		'feel_recommendation_template' => 'At {value}, you want {name} — {description}',
		'feel_recommendation_fallback' => 'the closest firmness to your selection.',
		'feel_more_text' => 'Want a guided answer?',
		'feel_cta_label' => 'Open the Mattress Finder',
		'feel_empty_message' => 'Add a firmness value to products to display matches.',
		'categories_eyebrow' => 'Shop by category',
		'categories_heading' => 'Everything the bed wears.',
		'categories_intro' => 'From the mattress up — pillows, toppers and dawn-coloured bedding, cut from the same calm cloth.',
		'categories_empty_message' => 'Add product categories with images to populate this section.',
		'home_story_eyebrow' => 'Made in Jeddah',
		'story_heading' => 'Made for better mornings.',
		'story_body' => '<p>Blue brings Saudi industrial knowledge and carefully selected materials together to make calmer nights and better mornings.</p>',
		'reviews_eyebrow' => 'Reviews',
		'reviews_heading' => 'Loved across the Kingdom.',
		'reviews_intro' => 'Mornings, reported from Riyadh to Khobar.',
		'reviews_dot_label' => 'Go to review {number}',
		'lifestyle_quote' => '“The best mornings are built the night before.”',
		'lifestyle_cta_label' => 'Open the Mattress Finder',
		'home_stark_eyebrow' => 'The mother company',
		'home_stark_heading' => 'Behind Blue stands STARK.',
		'home_stark_intro' => 'Every Blue mattress is built by STARK across 60,000 m² of facilities and more than 200 specialists, making wood, furniture and sleep since 1967.',
		'home_stark_materials' => 'The materials answer the climate: COMBOCOOL™ covers that regulate heat and moisture, 100% organic latex certified to GOLS, and pocket-spring cores zoned across five areas of the body.',
		'home_stark_image_alt' => 'The STARK production line in Jeddah where Blue mattresses are made',
		'home_stark_stats' => array(
			array( 'value' => 8, 'unit' => '', 'label' => 'Mattresses' ),
			array( 'value' => 35, 'unit' => 'cm', 'label' => 'Tallest bed' ),
			array( 'value' => 50, 'unit' => '', 'label' => 'Night trial' ),
			array( 'value' => 10, 'unit' => 'yr', 'label' => 'Warranty' ),
		),
		'home_stark_cta_label' => 'Discover the mother company',
		'final_cta_heading' => 'Try Blue for 50 nights.',
		'final_cta_text' => 'Fifty nights at home with a complimentary protector. If it isn’t right we exchange it first, and refund you if that still isn’t the one.',
		'final_primary_cta' => 'Shop the collection',
		'final_secondary_cta' => 'Open the Mattress Finder',
	);
}

/** Seed newly added Homepage Content fields once, preserving editor changes. */
add_action(
	'acf/init',
	function (): void {
		if ( ! function_exists( 'update_field' ) || '2.1.0' === get_option( 'blue_home_content_seed_version' ) ) {
			return;
		}
		$page_ids = get_posts(
			array(
				'post_type' => 'page',
				'post_status' => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_key' => '_wp_page_template',
				'meta_value' => 'page-home.php',
			)
		);
		foreach ( $page_ids as $page_id ) {
			$language = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $page_id, 'slug' ) : 'en';
			foreach ( blue_homepage_seed_content( $language ?: 'en' ) as $field_name => $value ) {
				$current = get_field( $field_name, $page_id, false );
				if ( null === $current || '' === $current || array() === $current ) {
					update_field( $field_name, $value, $page_id );
				}
			}
		}
		update_option( 'blue_home_content_seed_version', '2.1.0', false );
	},
	20
);

/** Synchronize the stored Anatomy fields with the current four-step demo once. */
add_action(
	'acf/init',
	function (): void {
		if ( ! function_exists( 'update_field' ) || '2.4.0' === get_option( 'blue_anatomy_sequence_version' ) ) {
			return;
		}

		$page_ids = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_wp_page_template',
				'meta_value'     => 'page-home.php',
			)
		);

		$blue_one = get_page_by_path( 'blue-1', OBJECT, 'product' );
		if ( ! $blue_one ) {
			$blue_one = get_page_by_path( 'horizon', OBJECT, 'product' );
		}
		if ( ! $blue_one ) {
			$matches  = get_posts(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					's'              => 'BLUE 1',
				)
			);
			$blue_one = $matches ? $matches[0] : null;
		}

		foreach ( $page_ids as $page_id ) {
			$language = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $page_id, 'slug' ) : 'en';
			$language = 'ar' === $language ? 'ar' : 'en';
			$desired  = blue_homepage_anatomy_layers( $language );
			update_field( 'anatomy_layers', $desired, $page_id );

			$copy = blue_homepage_seed_content( $language );
			foreach ( array( 'anatomy_eyebrow', 'anatomy_heading', 'anatomy_intro', 'anatomy_cta_label', 'anatomy_alt_text' ) as $field_name ) {
				update_field( $field_name, $copy[ $field_name ], $page_id );
			}

			if ( $blue_one && ! get_field( 'anatomy_product', $page_id, false ) ) {
				$product_id = $blue_one->ID;
				if ( function_exists( 'pll_get_post' ) ) {
					$product_id = (int) ( pll_get_post( $product_id, $language ) ?: $product_id );
				}
				update_field( 'anatomy_product', $product_id, $page_id );
			}

		}

		update_option( 'blue_anatomy_sequence_version', '2.4.0', false );
	},
	25
);

/** Populate the complete demo-style STARK showcase on existing home pages. */
add_action(
	'acf/init',
	function (): void {
		if ( ! function_exists( 'update_field' ) || '2.5.0' === get_option( 'blue_stark_showcase_version' ) ) {
			return;
		}

		$page_ids = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_wp_page_template',
				'meta_value'     => 'page-home.php',
			)
		);

		$fields = array(
			'home_stark_image_alt',
			'home_stark_eyebrow',
			'home_stark_heading',
			'home_stark_intro',
			'home_stark_materials',
			'home_stark_stats',
			'home_stark_cta_label',
		);

		foreach ( $page_ids as $page_id ) {
			$language = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $page_id, 'slug' ) : 'en';
			$language = 'ar' === $language ? 'ar' : 'en';
			$content  = blue_homepage_seed_content( $language );
			foreach ( $fields as $field_name ) {
				update_field( $field_name, $content[ $field_name ], $page_id );
			}
		}

		update_option( 'blue_stark_showcase_version', '2.5.0', false );
	},
	27
);

/** Synchronize the editable homepage and global footer fields with BM0002 once. */
add_action(
	'acf/init',
	function (): void {
		if ( ! function_exists( 'update_field' ) || '2.7.1' === get_option( 'blue_bm0002_content_version' ) ) {
			return;
		}

		$page_ids = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_wp_page_template',
				'meta_value'     => 'page-home.php',
			)
		);
		foreach ( $page_ids as $page_id ) {
			$language = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $page_id, 'slug' ) : 'en';
			foreach ( blue_homepage_seed_content( 'ar' === $language ? 'ar' : 'en' ) as $field_name => $value ) {
				update_field( $field_name, $value, $page_id );
			}
		}

		$options = array(
			'footer_tagline_en'          => "Feels like magic, but it's really just science.",
			'footer_tagline_ar'          => 'إحساسٌ كالسحر، لكنه في الحقيقة مجرّد علم.',
			'footer_note_en'             => 'Where dreams begin — mattresses, pillows, toppers and bedding, delivered across the Kingdom from Alrowdah, Jeddah.',
			'footer_note_ar'             => 'حيث تبدأ الأحلام — مراتب ومخدّات ولبّاد ومفارش تصل إلى كل مناطق المملكة من الروضة، جدة.',
			'footer_sale_label_en'       => 'Sale',
			'footer_sale_label_ar'       => 'التخفيضات',
			'newsletter_button_en'       => 'Join',
			'newsletter_button_ar'       => 'اشترك',
			'copyright_en'               => sprintf( '© %d Blue Mattresses. All rights reserved.', wp_date( 'Y' ) ),
			'copyright_ar'               => sprintf( '© %d مراتب بلو. جميع الحقوق محفوظة.', wp_date( 'Y' ) ),
			'commercial_register'        => '7054657023',
			'vat_number'                 => '314868318200003',
			'contact_address_en'         => 'Prince Saud Al Faisal St, Ar Rawdah, Jeddah',
			'contact_address_ar'         => 'شارع الأمير سعود الفيصل، الروضة، جدة',
			'contact_map_url'            => 'https://maps.google.com/maps?q=Blue%20mattresses%2C%20Prince%20Saud%20Al%20Faisal%2C%20Ar%20Rawdah%2C%20Jeddah%2023432%2C%20Saudi%20Arabia&ftid=0x15c3d13ffc986767:0x1086c576b37305d9&output=embed',
			'contact_map_link'           => 'https://maps.app.goo.gl/m5WL6tQQJVz1jDB47',
			'whatsapp_url'               => 'https://wa.me/966598006600',
			'social_links'               => array(
				array( 'label' => 'Instagram', 'url' => 'https://www.instagram.com/blue.mattresses', 'icon' => 'instagram' ),
				array( 'label' => 'TikTok', 'url' => 'https://www.tiktok.com/@blue_mattresses', 'icon' => 'tiktok' ),
				array( 'label' => 'Snapchat', 'url' => 'https://snapchat.com/t/fHPsmeFM', 'icon' => 'snapchat' ),
				array( 'label' => 'Facebook', 'url' => 'https://www.facebook.com/profile.php?id=61575523236187', 'icon' => 'facebook' ),
				array( 'label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/company/137164312/', 'icon' => 'linkedin' ),
				array( 'label' => 'WhatsApp', 'url' => 'https://wa.me/966598006600', 'icon' => 'whatsapp' ),
			),
		);
		foreach ( $options as $field_name => $value ) {
			update_field( $field_name, $value, 'option' );
		}

		update_option( 'blue_bm0002_content_version', '2.7.1', false );
	},
	30
);
