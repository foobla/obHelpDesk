<?php
/**
 * @package        $Id: script.obhelpdesk.php 71 2013-09-12 01:51:36Z phonglq $
 * @author         foobla.com
 * @copyright      2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL.
 */

// no direct access
defined( '_JEXEC' ) or die;

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
class com_obHelpDeskInstallerScript {
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance $adapter The object responsible for running this script
	 */
	public function __constructor( JAdapterInstance $adapter ) {
		return;
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install( JAdapterInstance $adapter ) {

	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall( JAdapterInstance $adapter ) {
		# Uninstall modules

		$db = JFactory::getDbo();
// 		$sql = "SELECT
// 					`id`,`module`,`title`
// 				FROM
// 					`#__modules`
// 				WHERE
// 						`module`='mod_obhelpdesk_departmentsstats'
// 						OR `module`='mod_obhelpdesk_newesttickets'
// 						OR `module`='mod_obhelpdesk_overduetickets'
// 						OR `module`='mod_obhelpdesk_ticketsstats'";
		$sql = "SELECT 
				    *
				FROM
				    `#__extensions`
				WHERE
				    `element` LIKE 'mod_obhelpdesk_%'
					AND `type`='module';";

		$db->setQuery( $sql );
		$rows = $db->loadAssocList();

		$status = array();
		$count  = count( $rows );
		if ( $count ) {
			?>
			<h2><?php echo JText::_( 'obHelpDesk Uninstall Status' ); ?></h2>
			<table class="adminlist">
				<thead>
				<tr>
					<th class="title"><?php echo JText::_( 'Extension' ); ?></th>
					<th><?php echo JText::_( 'Client' ); ?></th>
					<th width="30%"><?php echo JText::_( 'Status' ); ?></th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				</tfoot>
				<tbody>
				<?php
				for ( $i = 0; $i < $count; $i ++ ) {
					$installer = new JInstaller();
					$res       = $installer->uninstall( 'module', $rows[$i]['extension_id'] );
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="key">Module: <?php echo $rows[$i]['name'] ?></td>
						<td class="key"><?php echo ( $rows[$i]['client_id'] ) ? JText::_( 'Admin' ) : JText::_( 'Site' ); ?></td>
						<td>
							<strong><?php echo ( $res ) ? JText::_( 'Uninstalled' ) : JText::_( 'Not uninstalled' ); ?></strong>
						</td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
		<?php
		}

		// $parent is the class calling this method
		return;
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update( JAdapterInstance $adapter ) {
		// $parent is the class calling this method

		return;
	}

	/**
	 * Called before any type of action
	 *
	 * @param   string           $route   Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance $adapter The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight( $route, JAdapterInstance $adapter ) {
		return;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string           $route   Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance $adapter The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight( $route, JAdapterInstance $adapter ) {
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		if ( $route == 'install' || $route == 'update' ) {

			# INIT DATABASE
			$sqlfile = dirname( __FILE__ ) . DS . 'admin' . DS . 'install' . DS . 'install.mysql.sql';
			if ( ! JFile::exists( $sqlfile ) ) {
				$sqlfile = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'install.mysql.sql';
			}
			if ( JFile::exists( $sqlfile ) ) {
				$this->executeSqlFile( $sqlfile );
			}

			# UPDATE DATABASE
			// update replytemplates with level, from 3.1o
			$query_check = "SHOW FIELDS FROM `#__obhelpdesk3_replytemplates` LIKE 'default'";
			$db->setQuery( $query_check );
			if ( $res = $db->loadObject() ) {
				$query = '
					ALTER TABLE `#__obhelpdesk3_replytemplates`
						DROP COLUMN `default`,
						ADD COLUMN `level` SMALLINT(3) NULL DEFAULT 0 AFTER `published`
				';
				$db->setQuery( $query );
				$db->query();
			}

			// update department with external_link feature, from 3.1n
			$query_check = "SHOW FIELDS FROM `#__obhelpdesk3_departments` LIKE 'external_link'";
			$db->setQuery( $query_check );
			if ( ! $res = $db->loadObject() ) {
				$query = '
					ALTER TABLE `#__obhelpdesk3_departments`
					ADD COLUMN `external_link` VARCHAR(255) NULL AFTER `prefix`;
				';
				$db->setQuery( $query );
				$db->query();
			}

			// update struct of table tickets
			$sql = "SHOW FIELDS FROM `#__obhelpdesk3_tickets` LIKE 'replies'";
			$db->setQuery( $sql );
			$res = $db->loadObject();
			if ( ! $res ) {
				# add replies field
				$sql = "ALTER TABLE `#__obhelpdesk3_tickets` ADD COLUMN `replies` SMALLINT UNSIGNED NOT NULL DEFAULT 0";
				$db->setQuery( $sql );
				$db->query();

				# update value for replies field
				$sql = "update `#__obhelpdesk3_tickets` AS t
						set t.replies = (
							select count(*) from `#__obhelpdesk3_messages` AS m
							where m.tid = t.id
						)
						where t.replies = 0";
				$db->setQuery( $sql );
				$db->query();

			}
			//2 update emailtemplates
			$sql = "SELECT COUNT(*) FROM `#__obhelpdesk3_emailtemplates`";
			$db->setQuery( $sql );
			$count = $db->loadResult();
			if ( $count < 10 ) {
				$sql = "update #__obhelpdesk3_emailtemplates set `type` = CONCAT('bak_', `type`), `edit`=0 WHERE id<100";
				$db->setQuery( $sql );
				$db->query();
				// update email tem
				$sqlfile = dirname( __FILE__ ) . DS . 'admin' . DS . 'install' . DS . 'email_templates.sql';
				if ( ! JFile::exists( $sqlfile ) ) {
					$sqlfile = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'email_templates.sql';
				}
				if ( JFile::exists( $sqlfile ) ) {
					$this->executeSqlFile( $sqlfile );
				}
			}

			# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			# Check is update or new install
			# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			$sql = "SELECT
						`id`,`module`
					FROM
						`#__modules`
					WHERE
							`module`='mod_obhelpdesk_customer'
							OR `module`='mod_obhelpdesk_overduetickets'
							OR `module`='mod_obhelpdesk_newesttickets'
							OR `module`='mod_obhelpdesk_departmentsstats'
							OR `module`='mod_obhelpdesk_ticketsstats'";
			$db->setQuery( $sql );
			$rows = $db->loadAssocList( 'module' );

			$isUpdate = ( count( $rows ) ); //P


			# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			# Install modules
			# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			$status                                  = new stdClass();
			$status->mod_obhelpdesk_customer         = 0;
			$status->mod_obhelpdesk_departmentsstats = 0;
			$status->mod_obhelpdesk_newesttickets    = 0;
			$status->mod_obhelpdesk_overduetickets   = 0;
			$status->mod_obhelpdesk_ticketsstats     = 0;
			$status->plg_obhelpdesk_kb_content       = 0;
			$status->plg_obhelpdesk_kb_k2            = 0;

			defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );

			$installer                       = new JInstaller();
			$path                            = str_replace( array( '/', '\\' ), DS, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'AIO' . DS . 'mod_obhelpdesk_customer' );
			$status->mod_obhelpdesk_customer = $installer->install( $path );

			$installer                               = new JInstaller();
			$path                                    = str_replace( array( '/', '\\' ), DS, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'AIO' . DS . 'mod_obhelpdesk_departmentsstats' );
			$status->mod_obhelpdesk_departmentsstats = $installer->install( $path );

			$installer                            = new JInstaller();
			$path                                 = str_replace( array( '/', '\\' ), DS, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'AIO' . DS . 'mod_obhelpdesk_newesttickets' );
			$status->mod_obhelpdesk_newesttickets = $installer->install( $path );

			$installer                             = new JInstaller();
			$path                                  = str_replace( array( '/', '\\' ), DS, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'AIO' . DS . 'mod_obhelpdesk_overduetickets' );
			$status->mod_obhelpdesk_overduetickets = $installer->install( $path );

			$installer                           = new JInstaller();
			$path                                = str_replace( array( '/', '\\' ), DS, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'AIO' . DS . 'mod_obhelpdesk_ticketsstats' );
			$status->mod_obhelpdesk_ticketsstats = $installer->install( $path );

			$installer                         = new JInstaller();
			$path                              = str_replace( array( '/', '\\' ), DS, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'AIO' . DS . 'obhelpdesk_kb' . DS . 'content' );
			$status->plg_obhelpdesk_kb_content = $installer->install( $path );

			$installer                    = new JInstaller();
			$path                         = str_replace( array( '/', '\\' ), DS, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obhelpdesk' . DS . 'install' . DS . 'AIO' . DS . 'obhelpdesk_kb' . DS . 'k2' );
			$status->plg_obhelpdesk_kb_k2 = $installer->install( $path );


			# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			# Set Status, Position and Module Assignment for modules - see details page of module 
			# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			if ( ! $isUpdate ) {
				// Set status for obhelpdesk_kb plugin
				if ( $status->plg_obhelpdesk_kb_content ) {
					$sql = "UPDATE 
								`#__extensions`
							SET
								`enabled` = 1
							WHERE 
								`type`='plugin' 
								AND `folder`='obhelpdesk_kb'";
					$db->setQuery( $sql );
					$db->query();
				}

				// Set Status and Position for modules
				if ( $status->mod_obhelpdesk_customer ) {
					$sql = "UPDATE 
								`#__modules`
							SET
								`published` = 1,
								`showtitle` = 0,
								`position` 	= 'obhelpdesk_customer',
								`ordering`	= 1,
								`params`	= '{\"show_kb\":\"1\",\"kb_link\":\"\",\"show_orders\":\"1\",\"orders_link\":\"\",\"moduleclass_sfx\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}'
							WHERE 
								`module`='mod_obhelpdesk_customer';";
					$db->setQuery( $sql );
					$db->query();
					if ( $db->getErrorNum() ) {
						$app->enqueueMessage( $db->getErrorMsg() );
					}
				}

				if ( $status->mod_obhelpdesk_overduetickets ) {
					$sql = "UPDATE
								`#__modules`
							SET
								`published` = 1,
								`showtitle` = 1,
								`position` = 'obhelpdesk_staff',
								`ordering`	= 1,
								`params`	= '{\"moduleclass_sfx\":\" span6\",\"userAvatar\":\"1\",\"who\":\"0\",\"cache\":\"0\",\"cache_time\":\"900\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}'
							WHERE
								`module`='mod_obhelpdesk_overduetickets';";
					$db->setQuery( $sql );
					$db->query();
					if ( $db->getErrorNum() ) {
						$app->enqueueMessage( $db->getErrorMsg() );
					}
				}

				if ( $status->mod_obhelpdesk_newesttickets ) {
					$sql = "UPDATE 
								`#__modules`
							SET
								`published` = 1,
								`showtitle` = 1,
								`position` = 'obhelpdesk_staff',
								`ordering`	= 2,
								`params`	= '{\"moduleclass_sfx\":\" span6\",\"userAvatar\":\"1\",\"who\":\"0\",\"itemCount\":\"10\",\"cache\":\"0\",\"cache_time\":\"900\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}'
							WHERE 
								`module`='mod_obhelpdesk_newesttickets';";
					$db->setQuery( $sql );
					$db->query();
					if ( $db->getErrorNum() ) {
						$app->enqueueMessage( $db->getErrorMsg() );
					}
				}

				// Set Status and Position for modules
				if ( $status->mod_obhelpdesk_departmentsstats ) {
					$sql = "UPDATE
								`#__modules`
							SET
								`published` = 1,
								`showtitle` = 1,
								`position` = 'obhelpdesk_staff',
								`ordering`	= 3,
								`params`	= '{\"moduleclass_sfx\":\" span12 nomargin\",\"width\":\"300\",\"height\":\"300\",\"backgroundColor\":\"#FFFFFF\",\"textColor\":\"#000000\",\"itemCount\":\"10\",\"cache\":\"1\",\"cache_time\":\"900\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}'
							WHERE
								`module`='mod_obhelpdesk_departmentsstats';";
					$db->setQuery( $sql );
					$db->query();
					if ( $db->getErrorNum() ) {
						$app->enqueueMessage( $db->getErrorMsg() );
					}
				}

				if ( $status->mod_obhelpdesk_ticketsstats ) {
					$sql = "UPDATE
								`#__modules`
							SET
								`published` = 1,
								`showtitle` = 1,
								`position` = 'obhelpdesk_staff',
								`ordering`	= 4,
								`params`	= '{\"moduleclass_sfx\":\" span12 nomargin\",\"width\":\"200\",\"height\":\"300\",\"backgroundColor\":\"#FFFFFF\",\"textColor\":\"#000000\",\"months\":\"12\",\"cache\":\"1\",\"cache_time\":\"900\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}'
							WHERE
								`module`='mod_obhelpdesk_ticketsstats';";
					$db->setQuery( $sql );
					$db->query();
					if ( $db->getErrorNum() ) {
						$app->enqueueMessage( $db->getErrorMsg() );
					}
				}

				// Set Module Assignment for modules
				$sql = "SELECT
							`id`
						FROM
							`#__modules`
						WHERE
							`module` = 'mod_obhelpdesk_customer'
							OR `module` = 'mod_obhelpdesk_overduetickets'
							OR `module` = 'mod_obhelpdesk_newesttickets'
							OR `module` = 'mod_obhelpdesk_departmentsstats'
							OR `module` = 'mod_obhelpdesk_ticketsstats'";
				$db->setQuery( $sql );
				$ids = $db->loadColumn();
				if ( $db->getErrorNum() ) {
					$app->enqueueMessage( $db->getErrorMsg() );
				}
				if ( count( $ids ) ) {
					$values = '(' . implode( ',0),(', $ids ) . ',0 )';
					$sql    = "INSERT IGNORE INTO `#__modules_menu`
								(`moduleid`, `menuid`) 
							VALUES
								{$values};";
					$db->setQuery( $sql );
					$db->query();
					if ( $db->getErrorNum() ) {
						$app->enqueueMessage( $db->getErrorMsg() );
					}
				}
			}
			?>
			<div id="foobla">
				<h2><?php echo JText::_( 'obHelpDesk Installation Status' ); ?></h2>
				<table class="table table-striped">
					<thead>
					<tr>
						<th class="title"><?php echo JText::_( 'Extension' ); ?></th>
						<th><?php echo JText::_( 'Client' ); ?></th>
						<th width="30%"><?php echo JText::_( 'Status' ); ?></th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					</tfoot>
					<tbody>
					<tr class="row0">
						<td class="key" colspan="2"><?php echo 'obHelpDesk ' . JText::_( 'Component' ); ?></td>
						<td><strong><?php echo JText::_( 'Installed' ); ?></strong></td>
					</tr>
					<tr class="row1">
						<td class="key">Module: mod_obhelpdesk_departmentsstats</td>
						<td class="key">Site</td>
						<td>
							<strong><?php echo ( $status->mod_obhelpdesk_departmentsstats ) ? JText::_( 'Installed' ) : JText::_( 'Not installed' ); ?></strong>
						</td>
					</tr>
					<tr class="row0">
						<td class="key">Module: mod_obhelpdesk_newesttickets</td>
						<td class="key">Site</td>
						<td>
							<strong><?php echo ( $status->mod_obhelpdesk_newesttickets ) ? JText::_( 'Installed' ) : JText::_( 'Not installed' ); ?></strong>
						</td>
					</tr>
					<tr class="row1">
						<td class="key">Module: mod_obhelpdesk_overduetickets</td>
						<td class="key">Site</td>
						<td>
							<strong><?php echo ( $status->mod_obhelpdesk_overduetickets ) ? JText::_( 'Installed' ) : JText::_( 'Not installed' ); ?></strong>
						</td>
					</tr>
					<tr class="row0">
						<td class="key">Module: mod_obhelpdesk_ticketsstats</td>
						<td class="key">Site</td>
						<td>
							<strong><?php echo ( $status->mod_obhelpdesk_ticketsstats ) ? JText::_( 'Installed' ) : JText::_( 'Not installed' ); ?></strong>
						</td>
					</tr>
					<tr class="row1">
						<td class="key">Module: mod_obhelpdesk_customer</td>
						<td class="key">Site</td>
						<td>
							<strong><?php echo ( $status->mod_obhelpdesk_customer ) ? JText::_( 'Installed' ) : JText::_( 'Not installed' ); ?></strong>
						</td>
					</tr>
					<tr class="row0">
						<td class="key">Plugin: plg_obhelpdesk_kb_content</td>
						<td class="key">&nbsp;</td>
						<td>
							<strong><?php echo ( $status->plg_obhelpdesk_kb_content ) ? JText::_( 'Installed' ) : JText::_( 'Not installed' ); ?></strong>
						</td>
					</tr>
					<tr class="row1">
						<td class="key">Plugin: plg_obhelpdesk_kb_k2</td>
						<td class="key">&nbsp;</td>
						<td>
							<strong><?php echo ( $status->plg_obhelpdesk_kb_k2 ) ? JText::_( 'Installed' ) : JText::_( 'Not installed' ); ?></strong>
						</td>
					</tr>
					</tbody>
				</table>
				<div style="margin: 50px; padding: 20px 20px 20px 180px; text-align: left; font-size: medium; border: 4px solid #eb722e; background: url('components/com_obhelpdesk/assets/images/obhelpdesk_110.png') no-repeat 2% center #f0d5a6;">
					<p style="font-weight: bold;"><?php echo JText::_( 'OBHELPDESK_INSTALLED_WHAT_NEXT' ); ?></p>
					<ol>
						<li><?php echo JText::_( 'OBHELPDESK_INSTALLED_ADD_DEPARTMENT' ); ?></li>
						<li><?php echo JText::_( 'OBHELPDESK_INSTALLED_ADD_GROUP' ); ?></li>
						<li><?php echo JText::_( 'OBHELPDESK_INSTALLED_ADD_STAFF' ); ?></li>
						<li><?php echo JText::_( 'OBHELPDESK_INSTALLED_CUSTOM_FIELD' ); ?></li>
						<li><?php echo JText::_( 'OBHELPDESK_INSTALLED_REPLY' ); ?></li>
					</ol>
				</div>
			</div>
		<?php
		}

		return;
	}

	private function executeSqlFile( $sqlfile ) {
		$app     = JFactory::getApplication();
		$db      = JFactory::getDbo();
		$sql     = JFile::read( $sqlfile );
		$queries = $db->splitSql( $sql );
		if ( $queries && count( $queries ) ) {
			foreach ( $queries AS $query ) {
				$db->setQuery( $query );
				$db->query();
				if ( $db->getErrorNum() ) {
					$app->enqueueMessage( $db->getErrorMsg(), 'error' );
				}
			}
		}
	}
}

?>