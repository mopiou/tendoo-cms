<?php echo $lmenu;?>
<section id="content">
    <section class="bigwrapper"><?php echo get_core_vars( 'inner_head' );?>
        
        <footer class="footer bg-white b-t">
            <div class="row m-t-sm text-center-xs">
                <div class="col-sm-4">
                    
                </div>
                <div class="col-sm-4 text-center">  </div>
                <div class="col-sm-4 text-right text-center-xs">
                    <ul class="pagination pagination-sm m-t-none m-b-none">
                        <?php 
					if(is_array($paginate[4]))
					{
						foreach($paginate[4] as $p)
						{
							?>
                        <li class="<?php echo $p['state'];?>"><a href="<?php echo $p['link'];?>"><?php echo $p['text'];?></a></li>
                        <?php
						}
					}
				?>
                    </ul>
                </div>
            </div>
        </footer>
        <section class="scrollable" id="pjax-container">
            <header>
                <div class="row b-b m-l-none m-r-none">
                    <div class="col-sm-4">
                        <h4 class="m-t m-b-none"><?php echo get_page('title');?></h4>
                        <p class="block text-muted"><?php echo get_page('description');?></p>
                    </div>
                    <div class="col-sm-8">
                        <a href="#" class="btn btn-lg <?php echo theme_button_class();?>" style="float:right;margin:10px;"><i style="font-size:20px;" class="fa fa-question-circle"></i>
                        </a>
                    </div>
                </div>
            </header>
            <section class="bigwrapper">
                <section class="wrapper"> <?php echo output('notice');?>
                    <div class="row">
                        <div class="col-lg-8">
                            <section class="panel">
                                <div class="panel-heading"> <?php _e( 'Installed Modules' );?> </div>
                                <table class="table table-striped">
                                    <tbody>
                                        <?php
                                            if($mod_nbr > 0)
                                            {
												if( is_array( $modules_list )  && $modules_list ){
													foreach($modules_list as $mod)
													{
														$appIcon	=	module_icon( $mod[ 'namespace' ] );
														?>
											<tr>
												<td>
												<a class="view" href="<?php echo $this->instance->url->site_url(array('admin','open','modules',$mod['namespace']));?>">
												<?php
														if($appIcon)
														{
														?>
													<img src="<?php echo $appIcon;?>" style="height:70px;width:70px;">
													<?php
														}
														else
														{
															?>
													<i class="fa fa-puzzle-piece" style="font-size:70px;float:left;height:70px;width:70px;"></i>
													<?php
														}
														?>
														</a></td>
												<td class="action"><strong> <a class="view" href="<?php echo $this->instance->url->site_url(array('admin','open','modules',$mod['namespace']));?>"><?php echo $mod['human_name'];?></a> </strong> <br>
													<?php echo $mod['description'];?> <br>
													<br>
													<small><?php _e( 'Author' );?> : <?php echo $mod['author'];?></small> 
													|
													<small><?php _e( 'Attributes' );?> : <?php echo (in_array($mod['handle'],array('BLOG','INDEX','FORUM','CONTACT','STATIC','MEDIA','PORTFOLIO','APP','WIDGETS'))) ? $mod['handle'] : 'Inconnu';?></small>
													<strong><small style="float:right;font-size:10px;"><?php echo ($mod['version'] == '') ? 'Version Inconnue' : 'v.'.$mod['version'];?></small></strong>
													<?php
													if( TRUE !== ( $active_theme	=	does_active_theme_support( $mod['handle'] ) ) && $mod[ 'handle' ] != 'APP' )
													{
													?>
													<hr class="line-dashed" style="margin:5px 0;">
												<div style="color:#FF6464">
                                                	<i class="fa fa-warning" style="font-size:20px;"></i> 
													<?php echo sprintf( __( 'Active theme %s doesn\'t support this module.' ) , '<strong>' . $active_theme['human_name'] . '</strong>' );?>
                                                </div>
													<?php
													}
													?>
													</td>
												<td class="action"><a class="delete" href="<?php echo $this->instance->url->site_url(array('admin','uninstall','module',$mod['namespace']));?>"><i style="font-size:25px;" class="fa fa-trash-o" title="<?php _e( 'Uninstall' );?>"></i></a></td>
												<td><?php
														if($mod['active'] === false )
														{
															?>
													<a class="delete" href="<?php echo $this->instance->url->site_url(array('admin','active','module',$mod['namespace']));?>"><i style="font-size:25px;" class="fa fa-times-circle" title="<?php _e( 'Click here to enable' );?>"></i></a>
													<?php
														}
														else
														{
															?>
													<a class="delete" href="<?php echo $this->instance->url->site_url(array('admin','unactive','module',$mod['namespace']));?>"><i style="font-size:25px;" class="fa fa-check" title="<?php _e( 'Click here to uninstall' );?>"></i></a>
													<?php
														}
														?></td>
											</tr>
											<?php
													}
												} else {
													?>
                                                    <tr>
                                                        <td colspan="6"><?php echo tendoo_info( translate( 'no_module_installed' ) );?></p></td>
                                                    </tr>
                                                    <?php
												}
                                            }
                                            else
                                            {
                            ?>
                                        <tr>
                                            <td colspan="6"><?php _e( 'No module installed' );?></td>
                                        </tr>
                                        <?php
                        }
                                            ?>
                                    </tbody>
                                </table>
                            </section>
                        </div>
                    </div>
                </section>
            </section>
        </section>
        </section>
        <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a> </section>