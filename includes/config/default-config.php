<?php
/**
 * Default config file
 *
 * @var array
 */
$config = array(
	'xml' => array(
		'enabled'    => true,
		'use_upload' => true,
		'path'       => false,
	),
	'import' => array(
		'chunk_size'            => $this->chunk_size,
		'regenerate_chunk_size' => 3,
		'allow_types'           => false,
	),
	'remap' => array(
		'post_meta' => array(),
		'term_meta' => array(),
		'options'   => array(
			'jet_woo_builder',
			'woocommerce_catalog_columns',
			'woocommerce_catalog_rows',
		),
	),
	'export' => array(
		'message' => __( 'Export all content with Jet Data Export tool', 'jet-data-importer' ),
		'logo'    => $this->url( 'assets/img/monster-logo.png' ),
		'options' => array(),
		'tables'  => array(),
	),
	'success-links' => array(
		'home' => array(
			'label'  => __( 'View your site', 'jet-data-importer' ),
			'type'   => 'primary',
			'target' => '_self',
			'icon'   => 'dashicons-welcome-view-site',
			'desc'   => __( 'Take a look at your site', 'jet-data-importer' ),
			'url'    => home_url( '/' ),
		),
		'edit' => array(
			'label'  => __( 'Start editing', 'jet-data-importer' ),
			'type'   => 'primary',
			'target' => '_self',
			'icon'   => 'dashicons-welcome-write-blog',
			'desc'   => __( 'Proceed to editing pages', 'jet-data-importer' ),
			'url'    => admin_url( 'edit.php?post_type=page' ),
		),
		'documentation' => array(
			'label'  => __( 'Check documentation', 'jet-data-importer' ),
			'type'   => 'primary',
			'target' => '_blank',
			'icon'   => 'dashicons-welcome-learn-more',
			'desc'   => __( 'Get more info from documentation', 'jet-data-importer' ),
			'url'    => 'http://documentation.zemez.io/wordpress/index.php?project=crocoblock',
		),
		'knowledge-base' => array(
			'label'  => __( 'Knowledge Base', 'jet-data-importer' ),
			'type'   => 'primary',
			'target' => '_blank',
			'icon'   => 'dashicons-sos',
			'desc'   => __( 'Access the vast knowledge base', 'jet-data-importer' ),
			'url'    => 'https://zemez.io/wordpress/support/knowledge-base/',
		),
		'community' => array(
			'label'  => __( 'Community', 'jet-data-importer' ),
			'type'   => 'primary',
			'target' => '_blank',
			'icon'   => 'dashicons-facebook',
			'desc'   => __( 'Join community to stay tuned to the latest news', 'jet-data-importer' ),
			'url'    => 'https://www.facebook.com/groups/CrocoblockCommunity/',
		),
	),
	'slider' => array(
		'path' => 'https://raw.githubusercontent.com/ZemezLab/kava-slider/master/slides.json',
	),
	'advanced_import' => array(
		'from_path' => 'https://account.crocoblock.com/wp-content/uploads/static/wizard-skins.json'
	)
	/*
	'advanced_import' => array(
		'default' => array(
			'full'    => get_template_directory() . '/assets/demo-content/default/default-full.xml',
			'lite'    => get_template_directory() . '/assets/demo-content/default/default-min.xml',
			'thumb'   => get_template_directory_uri() . '/assets/demo-content/default/default-thumb.png',
			'plugins' => array(
				'booked-appointments' => 'Booked Appointments',
				'buddypress'          => 'BuddyPress',
				'cherry-projects'     => 'Cherry Projects'
			),
		),
	),
	or
	'advanced_import' => array(
		'from_path' => 'https://account.crocoblock.com/wp-content/uploads/static/wizard-skins.json'
	),
	 */
);
