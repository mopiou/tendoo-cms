<?php
class tim_tepas_class
{
	function __construct(){
		// Saving Theme Settings
		$active_theme	=	get_core_vars( 'active_theme' );
		$settings		=	get_meta( $active_theme[ 'NAMESPACE' ] . '_theme_settings' );
		if( $settings ){
			push_core_vars( 'active_theme' , 'theme_settings' , $settings );
		}
	}
}