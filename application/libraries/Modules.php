<?php
class Modules
{
	private static	$modules;
	static function load( $module_path )
	{
		$dir	=	opendir( $module_path );
		$config	=	array();
		
		// Looping currend folder
		while( FALSE !== ( $file = readdir( $dir ) ) )
		{
			if( substr( $file , -10 ) === 'config.xml' )
			{
				$config		=	get_instance()->xml2array->createArray( file_get_contents( $module_path . '/' . $file ) );
			}
			else if( is_dir( $module_path . '/' . $file ) && ! in_array( $file , array( '.' , '..' ) ) )
			{
				self::load( $module_path . '/' .$file );
			}
		}
		// Adding Valid init file to module array
		if( isset( $config[ 'application' ][ 'details' ][ 'namespace' ] , $config[ 'application' ][ 'details' ][ 'main' ] ) )
		{
			$namespace = strtolower( $config[ 'application' ][ 'details' ][ 'namespace' ] );
			// Saving details
			self::$modules[ $namespace ]					=	$config;
			// Edit main file path
			self::$modules[ $namespace ][ 'application' ][ 'details' ][ 'main' ]	=	$module_path . '/' .  self::$modules[ $namespace ][ 'application' ][ 'details' ][ 'main' ];
			self::$modules[ $namespace ][ 'application' ][ 'details' ][ 'namespace' ]	=	strtolower( self::$modules[ $namespace ][ 'application' ][ 'details' ][ 'namespace' ] );
		}
	}
	static function get( $namespace = null )
	{
		if( $namespace == NULL )
		{
			return self::$modules;
		}
		return isset( self::$modules[ $namespace ] ) ? self::$modules[ $namespace ] : false; // if module exists
	}
	
	/**
	 * Include modules init main file defined on config.ini
	**/
	
	static function init( $filter )
	{
		$modules		=	self::get();
		$modules_array	=	array();
		foreach( force_array( $modules ) as $module )
		{
			// print_array( $modules );
			// Load every module when on install mode
			if( is_file( $init_file = $module[ 'application' ][ 'details' ][ 'main' ] ) && $filter === 'all' )
			{
				include_once( $init_file );
			}
			else if( is_file( $init_file = $module[ 'application' ][ 'details' ][ 'main' ] ) && $filter === 'actives' )
			{
				$actives_modules		=	force_array( get_instance()->options->get( 'actives_modules' ) );
				if( in_array( strtolower( $module[ 'application' ][ 'details' ][ 'namespace' ] ) , $actives_modules ) )
				{
					// Check compatibility and other stuffs
					include_once( $init_file );
				}
			}
		}
		// get_instance()->options->set( 'active_modules' , $modules_array );	
	}
	
	/**
	 * 	enable module using namespace
	 *		is most used by tendoo core
	**/
	
	static function enable( $module_namespace )
	{
		$activated_modules			=	get_instance()->options->get( 'actives_modules' );
		if( ! in_array( $module_namespace , $activated_modules ) )
		{
			$activated_modules[]		=	$module_namespace;
			get_instance()->options->set( 'actives_modules' , $activated_modules );
		}
		return;
	}
	
	/**
	 * checks if a module is active
	 *
	 *	@access public
	 * @param string module namespace
	 * @returns bool
	**/
	
	static function is_active( $module_namespace )
	{
		$activated_modules			=	get_instance()->options->get( 'actives_modules' );
		if( ! in_array( $module_namespace , $activated_modules ) )
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Disable module
	 *
	 * @access public
	 * @param string module namespace
	 * @return void
	**/
	
	static function disable( $module_namespace )
	{
		$activated_modules			=	get_instance()->options->get( 'actives_modules' );
		if( in_array( $module_namespace , $activated_modules ) )
		{
			$key	=	array_search( $module_namespace , $activated_modules );
			unset( $activated_modules[ $key ] );
			get_instance()->options->set( 'actives_modules' , $activated_modules );
		}
	}
	
	/**
	 * Install module
	**/
	
	static function install( $file_name )
	{
		 $config[ 'upload_path' ]        =  APPPATH . '/temp/';
		 $config[ 'allowed_types' ]		=	'zip';
		 
		 get_instance()->load->library( 'upload' , $config );
		 
		 if ( ! get_instance()->upload->do_upload( $file_name ) )
		 { 
				get_instance()->notice->push_notice( get_instance()->lang->line( 'fetch-from-upload' ) );
		 }
		 else
		 {
				$data = array( 'upload_data' => get_instance()->upload->data());
				$extraction_temp_path		=	self::__unzip( $data );
				// Look for config.xml file to read config
				if( file_exists( $extraction_temp_path . '/config.xml' ) )
				{
					$module_array	=	get_instance()->xml2array->createArray( file_get_contents( $extraction_temp_path . '/config.xml' ) );					
					if( isset( $module_array[ 'application' ][ 'details' ][ 'namespace' ] ) )
					{
						$module_namespace	= $module_array[ 'application' ][ 'details' ][ 'namespace' ];
						$old_module = self::get( $module_namespace );
						// if module with a same namespace already exists
						if( $old_module && true == false ) // disabling update
						{
							if( isset( $old_module[ 'application' ][ 'details' ][ 'version' ] ) )
							{
								
							}
						}
						// if module does'nt exists
						else
						{
							$module_global_manifest	=	self::__parse_path( $extraction_temp_path );	
							if( is_array( $module_global_manifest ) )
							{
								self::__move_to_module_dir( $module_array , $module_global_manifest[0] , $module_global_manifest[1] );
							}
							// If it's not an array, return the error code.
							return $module_global_manifest;
						}
					}
					return 'incorrect-config-file';
				}
				return 'config-file-not-found';
		 }
	}
	
	static function __unzip( $upload_details )
	{
		$extraction_path		=	$upload_details[ 'upload_data' ][ 'file_path' ] . $upload_details[ 'upload_data' ][ 'raw_name' ];
		get_instance()->load->library( 'unzip' );	
		get_instance()->unzip->extract( 
			$upload_details[ 'upload_data' ][ 'full_path' ] , 
			$extraction_path
		);
		return $extraction_path;
	}
	static function __parse_path( $path )
	{
		$manifest	=	array();
		$module_manifest	=	array();
		if( $dir	=	opendir( $path ) )
		{
			while( false !== ( $file	=	readdir( $dir ) ) )
			{
				if( ! in_array( $file , array( '.' , '..' ) , true ) )
				{
					// Set sub dir path
					$sub_dir_path	=	$path;
					// If a correct folder is found
					if( in_array( $file , array( 'libraries' , 'models' , 'config' , 'helpers' ) ) && is_dir( $path . '/' . $file ) )
					{
						/**
						 * Reading folder is disabled since it doesn't 
						 * really seem to be a good pratice
						**/						
						
						// adding module file to manifest
						/**
						 * Return error if a conflic occur
						**/						
						$sub_dir	=	opendir( $path . '/' . $file );
						while( false !== ( $_file = readdir( $sub_dir ) ) )
						{
							if( ! in_array( $_file , array( '.' , '..' ) ) )
							{
								// remove it later
								$manifest[]	=	$path . '/' . $file . '/' . $_file ;
								
								if( ! file_exists( APPPATH . $file . '/' . $_file ) )
								{
									// 	var_dump( $sub_dir_path . '/' . $_file );
									$manifest[]	=	$path . '/' . $file . '/' . $_file ;
								}
								else
								{
									// return 'file-conflict';
								}
							}
						}						
					}
					// for other file and folder, they are included in module dir
					else
					{
						// var_dump( $sub_dir_path );
						$module_manifest[]	=	$sub_dir_path . '/' . $file;
					}
				}
			}
			// When everything seems to be alright
			return array( $module_manifest , $manifest );
		}
		return 'extraction-path-not-found';
	}

	/**
	 * Move module temp fil to valid module folder
	**/
	
	static function __move_to_module_dir( $module_array , $global_manifest , $manifest )
	{
		get_instance()->load->helper( 'file' );
		$module_dir_path		=	APPPATH . '/modules/' . $module_array[ 'application' ][ 'details' ][ 'namespace' ];
		if( ! is_dir( APPPATH . '/modules/' . $module_array[ 'application' ][ 'details' ][ 'namespace' ] ) )
		{
			mkdir( APPPATH . '/modules/' . $module_array[ 'application' ][ 'details' ][ 'namespace' ] , 0777 , true );
		}
		// moving global manifest
		var_dump( $global_manifest );
		var_dump( $manifest );
		foreach( $global_manifest as $_manifest )
		{
			// creating folder if it does'nt exists
			if( ! is_file( $_manifest ) )
			{
				// filter app folder and tendoo folder
				$dir	=	strtolower( basename( $_manifest ) );
				
				if( ! in_array( $dir , array( 'config' , 'models' , 'libraries' , 'helpers' ) ) )
				{
					if( ! is_dir( $module_dir_path . '/' . $dir ) ) : mkdir( $module_dir_path . '/' . $dir , 0777 , true ); endif;
				}
			}
			else
			{
				var_dump( $_manifest );
				// write_file( $module_dir_path , file_get_contents( $_manifest ) );
			}
			
			// var_dump( $module_dir_path );
			// var_dump( $module_dir_path );
			// var_dump( $_manifest );
			
			// write file on the new folder
		}
		// moving manifest to system folder
		foreach( $manifest as $_manifest )
		{
			// creating folder if it does'nt exists
			$dir	=	dirname( APPPATH . '/' . $_manifest );
			if( ! is_dir( $dir ) ) : mkdir( $dir , 0777 , true ); endif;
			
			// write file on the new folder
			write_file( APPPATH . '/' . $_manifest , file_get_contents( $_manifest ) );
		}
	}
}