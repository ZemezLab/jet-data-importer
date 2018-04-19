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
	'advanced_import' => false,
	'import' => array(
		'chunk_size'            => $this->chunk_size,
		'regenerate_chunk_size' => 3,
	),
	'remap' => array(
		'post_meta' => array(),
		'term_meta' => array(),
		'options'   => array(),
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
			'url'    => home_url( '/' ),
		),
		'customize' => array(
			'label'  => __( 'Customize your theme', 'jet-data-importer' ),
			'type'   => 'default',
			'target' => '_self',
			'url'    => admin_url( 'customize.php' ),
		),
	),
	'slider' => array(
		'path' => 'https://raw.githubusercontent.com/templatemonster/tm-wizard-slider/master/slides.json',
	),
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
	 */
);
