<?php
class Tendoo_Controller extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		
		// Include default library class
		include_once( LIBPATH .'/Html.php' );
		include_once( LIBPATH .'/Enqueue.php' );
		
		// get system lang
		$this->lang->load( 'system' );	
		
		// if is installed, setup is always loaded
		if( $this->setup->is_installed() )
		{
			// Load internal modules here
			$this->load->model( 'internal_modules' );
			// Should load modules and themes heres
			// triggers actions before session init
			$this->events->do_action( 'before_session_starts' );			
			
			$this->load->model( 'options' );			
			$this->load->model( 'users_model' , 'users' ); // run after flexi_auth
			
			// Triggers actions after session starts
			$this->events->do_action( 'after_session_starts' );
			
			// If there is no master user , redirect to master user creation if current controller isn't tendoo-setup
			if( ! $this->users->master_exists() && $this->uri->segment(1) !== 'tendoo-setup' )
			{
				redirect( array( 'tendoo-setup' , 'site' ) );
			}
		}
		
		// if is reserved controllers only
		if( in_array( $this->uri->segment(1) , $this->config->item( 'reserved_controllers' ) ) )
		{
			$this->load->helper( 'ui' );
			$this->load->library( 'notice' );
		}
				
		// Checks system status
		if( in_array( $this->uri->segment(1) , $this->config->item( 'reserved_controllers' ) ) || $this->uri->segment(1) === null ) // null for index page
		{			
			// there are some section which need tendoo to be installed. Before getting there, tendoo controller checks if for those
			// section tendoo is installed. If segment(1) returns null, it means the current section is index. Even for index,
			// installation is required
			if( ( in_array( $this->uri->segment(1) , $this->config->item( 'controllers_requiring_installation' ) ) || $this->uri->segment(1) === null ) && ! $this->setup->is_installed() )
			{
				redirect( array( 'tendoo-setup' ) );
			}
			// force user to be connected for certain controller
			if( in_array( $this->uri->segment(1) , $this->config->item( 'controllers_requiring_login' ) ) && $this->setup->is_installed() )
			{
				if( ! $this->users->is_connected() )
				{
					redirect( array( $this->config->item( 'default_login_route' ) ) );
				}
			}

			// force user to be connected for certain controller
			if( in_array( $this->uri->segment(1) , $this->config->item( 'controllers_requiring_logout' ) ) && $this->setup->is_installed() )
			{
				if( $this->users->is_connected() )
				{
					redirect( array( $this->config->item( 'default_logout_route' ) ) );
				}
			}
			
			// loading assets for reserved controller
			Enqueue::enqueue_css( 'bootstrap.min' );
			Enqueue::enqueue_css( 'AdminLTE.min' );
			Enqueue::enqueue_css( 'skins/_all-skins.min' );			
			Enqueue::enqueue_css( 'font-awesome-4.3.0' );
			Enqueue::enqueue_css( '../plugins/iCheck/square/blue' );
			/**
			 * 	Enqueueing Js
			**/
			
			Enqueue::enqueue_js( '../plugins/jQuery/jQuery-2.1.4.min' );
			Enqueue::enqueue_js( 'bootstrap.min' );
			Enqueue::enqueue_js( '../plugins/iCheck/icheck.min' );		
			Enqueue::enqueue_js( 'app.min' );
		}
	}
}