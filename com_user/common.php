<?php
defined('D_RUN') or die('Direct access prohibited');

class com_user {
	function authenticate($username, $password) {
		$entity = new user;
		$entity = $this->get_user_by_username($username);
		if ( $entity->password === md5($password.$entity->salt) ) {
			return $entity->guid;
		} else {
			return null;
		}
	}

	function delete_group($group_id) {
		// TODO: delete children
		$entity = new group;
		if ( $entity = $this->get_group($group_id) ) {
			$entity->delete();
			return true;
		} else {
			return false;
		}
	}

	function delete_user($user_id) {
		// TODO: delete children
		$entity = new user;
		if ( $entity = $this->get_user($user_id) ) {
			$entity->delete();
			return true;
		} else {
			return false;
		}
	}

	function gatekeeper($ability = NULL, $user = NULL) {
		if ( is_null($user) ) {
			if ( isset($_SESSION['user']) ) {
				$user = $_SESSION['user'];
			} else {
				unset($user);
			}
		}
		if ( isset($user) ) {
			if ( !is_null($ability) ) {
				if ( isset($user->abilities) ) {
					if ( in_array($ability, $user->abilities) || in_array('system/all', $user->abilities) )
						return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	function get_group($group_id) {
        /**
         * @todo Rewrite specifically for groups.
         */
		global $config;
		$group = new group;
		$group = $config->entity_manager->get_entity($group_id, group);
		if ( empty($group) )
			return null;

		if ( $group->has_tag('com_user', 'group') ) {
			return $group;
		} else {
			return null;
		}
	}

	function get_group_by_groupname($groupname) {
		global $config;
		$entities = array();
		$entity = new group;
		$entities = $config->entity_manager->get_entities_by_data(array('groupname' => $groupname), group);
		foreach ($entities as $entity) {
			if ( $entity->has_tag('com_user', 'group') )
				return $entity;
		}
		return null;
	}

	function get_groupname($group_id) {
		$entity = new group;
		$entity = $this->get_group($group_id);
		return $entity->groupname;
	}

	function get_user($user_id) {
		global $config;
		$user = new user;
		$user = $config->entity_manager->get_entity($user_id, user);
		if ( empty($user) )
			return null;

		if ( $user->has_tag('com_user', 'user') ) {
			return $user;
		} else {
			return null;
		}
	}

	function get_user_array($parent_id = NULL) {
		// TODO: check for orphans, they could cause users to be hidden
		global $config;
		$return = array();
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user', user);
			foreach ($entities as $entity) {
				if ( is_null($entity->parent) ) {
					$child_array = $this->get_user_array($entity->guid);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['username'] = $entity->username;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		} else {
			$entities = $config->entity_manager->get_entities_by_parent($parent_id, user);
			foreach ($entities as $entity) {
				if ( $entity->has_tag('com_user', 'user') ) {
					$child_array = $this->get_user_array($entity->guid);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['username'] = $entity->username;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		}
		return $return;
	}

	function get_user_by_username($username) {
		global $config;
		$entities = array();
		$entity = new user;
		$entities = $config->entity_manager->get_entities_by_data(array('username' => $username), user);
		foreach ($entities as $entity) {
			if ( $entity->has_tag('com_user', 'user') )
				return $entity;
		}
		return null;
	}

	function get_user_menu($parent_id = NULL, &$menu = NULL, $menu_parent = NULL, $top_level = TRUE) {
		global $config;
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user', user);
			foreach ($entities as $entity) {
				$menu->add($entity->name.' ['.$entity->username.']', $entity->guid, $entity->parent, $entity->guid);
			}
			$orphans = $menu->orphans();
			if ( !empty($orphans) )
				$orphan_menu_id = $menu->add('Orphans', NULL);
			foreach ($orphans as $orphan) {
				$menu->add($orphan['name'], $orphan['data'], $orphan_menu_id, $orphan['data']);
			}
		} else {
			$entities = $config->entity_manager->get_entities_by_parent($parent_id);
			foreach ($entities as $entity) {
				$new_menu_id = $menu->add($entity->name.' ['.$entity->username.']', $entity->guid, ($top_level ? NULL : $entity->parent), $entity->guid);
				$this->get_user_menu($entity->guid, $menu, $new_menu_id, FALSE);
			}
		}
	}

	function get_username($user_id) {
		$entity = new user;
		$entity = $this->get_user($user_id);
		return $entity->username;
	}

    function list_groups() {
		global $config;

        $module = new module('com_user', 'list_groups', 'content');
		$module->title = "Groups";

		$module->groups = $config->entity_manager->get_entities_by_tags('com_user', 'group', group);

		if ( empty($module->groups) ) {
            $module->detach();
            display_notice("There are no groups.");
        }
    }

	function list_users() {
		global $config;

		/* TODO: Remove after testing with left and right modules. */
		$module = new module('system', 'false', 'left');
		$module->title = "Left Users";
		$module->content("No users here ;)<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />");

		$module = new module('system', 'false', 'right');
		$module->title = "Right Users";
		$module->content("No users here ;)");
		/* End Remove. */

		$module = new module('com_user', 'list_users', 'content');
		$module->title = "Users";

		$module->users = $config->entity_manager->get_entities_by_tags('com_user', 'user', user);

		if ( empty($module->users) ) {
            $module->detach();
            display_notice("There are no users.");
        }

		/*
        $menu = new menu;
		$this->get_user_menu(NULL, $menu);
		$module->content($menu->render(array('<ul class="dropdown dropdown-vertical">', '</ul>'),
				array('<li>', '</li>'),
				array('<ul>', '</ul>'),
				array('<li>', '</li>'),
				"<strong>#NAME#</strong><br />".
					"<input type=\"button\" onclick=\"window.location='".$config->template->url('com_user', 'edituser', array('user_id' => '#DATA#'))."';\" value=\"Edit\" /> | ".
					"<input type=\"button\" onclick=\"if(confirm('Are you sure you want to delete \\'#NAME#\\'?')) {window.location='".$config->template->url('com_user', 'deleteuser', array('user_id' => '#DATA#'))."';}\" value=\"Delete\" />\n",
				'<hr style="visibility: hidden; clear: both;" />'));
         */
	}

	function login($id) {
		$entity = new user;

		$entity = $this->get_user($id);

		if ( isset($entity->username) ) {
			if ( $this->gatekeeper('com_user/login', $entity) ) {
				$_SESSION['user_id'] = $entity->guid;
				$_SESSION['user'] = $entity;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function logout() {
		unset($_SESSION['user']);
		session_unset();
		session_destroy();
	}

	function new_group() {
		$new_group = new user;
		$new_group->add_tag('com_user', 'group');
		$new_group->abilities = array();
		return $new_group;
	}

	function new_user() {
		$new_user = new user;
		$new_user->add_tag('com_user', 'user');
		$new_user->salt = md5(rand());
		$new_user->abilities = array();
		return $new_user;
	}

	function password(&$user, $password) {
		$user->password = md5($password.$user->salt);
	}

	function print_login($position = 'content') {
		$module = new module('com_user', 'login', $position);
	}

	function print_group_form($heading, $new_option, $new_action, $id = NULL) {
        /**
         * @todo Rewrite for groups.
         */
		global $config, $page;
		$module = new module('com_user', 'group_form', 'content');
		if ( is_null($id) ) {
			$module->groupname = $module->name = '';
			$module->group_abilities = array();
		} else {
			$group = $this->get_group($id);
			$module->groupname = $group->groupname;
			$module->name = $group->name;
			$module->email = $group->email;
			$module->parent = $group->parent;
			$module->group_abilities = $group->abilities;
		}
        $module->heading = $heading;
        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->id = $id;
        $module->display_abilities = gatekeeper("com_user/abilities");
        $module->sections = array('system');
        foreach ($config->components as $cur_component) {
            $module->sections[] = $cur_component;
        }
		//$module->content("<label>Parent<select name=\"parent\">\n");
		//$module->content("<option value=\"none\">--No Parent--</option>\n");
		//$module->content($this->print_user_tree('<option value="#guid#"#selected#>#mark# #name# [#username#]</option>', $this->get_user_array(), $parent));
		//$module->content("</select></label>\n");
	}

	function print_user_form($heading, $new_option, $new_action, $id = NULL) {
		global $config, $page;
		$module = new module('com_user', 'user_form', 'content');
		if ( is_null($id) ) {
			$module->username = $module->name = '';
			$module->user_abilities = array();
		} else {
			$user = $this->get_user($id);
			$module->username = $user->username;
			$module->name = $user->name;
			$module->email = $user->email;
			$module->parent = $user->parent;
			$module->user_abilities = $user->abilities;
		}
        $module->heading = $heading;
        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->id = $id;
        $module->display_abilities = gatekeeper("com_user/abilities");
        $module->sections = array('system');
        foreach ($config->components as $cur_component) {
            $module->sections[] = $cur_component;
        }
		//$module->content("<label>Parent<select name=\"parent\">\n");
		//$module->content("<option value=\"none\">--No Parent--</option>\n");
		//$module->content($this->print_user_tree('<option value="#guid#"#selected#>#mark# #name# [#username#]</option>', $this->get_user_array(), $parent));
		//$module->content("</select></label>\n");
	}

	function print_user_tree($mask, $user_array, $selected_id = NULL, $selected = ' selected="selected"', $mark = '') {
		$return = '';
		foreach ($user_array as $key => $user) {
			$parsed = str_replace('#guid#', $key, $mask);
			$parsed = str_replace('#name#', $user['name'], $parsed);
			$parsed = str_replace('#username#', $user['username'], $parsed);
			$parsed = str_replace('#mark#', $mark, $parsed);
			if ( $key == $selected_id ) {
				$parsed = str_replace('#selected#', $selected, $parsed);
			} else {
				$parsed = str_replace('#selected#', '', $parsed);
			}
			$return .= $parsed."\n";
			if ( !empty($user['children']) )
				$return .= $this->print_user_tree($mask, $user['children'], $selected_id, $selected, $mark.'->');
		}
		return $return;
	}

	function punt_user($message = NULL, $url = NULL) {
		global $config;
		header("Location: ".$config->template->url('com_user', 'exit', array('message' => urlencode($message), 'url' => urlencode($url)), false));
		exit;
	}
}

$config->user_manager = new com_user;
$config->ability_manager->add('com_user', 'login', 'Login', 'User can login to the system. (Useful for making user categories.)');
$config->ability_manager->add('com_user', 'new', 'Create Users', 'Let user create new users.');
$config->ability_manager->add('com_user', 'manage', 'Manage Users', 'Let user see and manage other users. Required to access the below abilities.');
$config->ability_manager->add('com_user', 'edit', 'Edit Users', 'Let user edit other users\' details.');
$config->ability_manager->add('com_user', 'delete', 'Delete Users', 'Let user delete other users.');
$config->ability_manager->add('com_user', 'abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.');

if ( isset($_SESSION['user_id']) )
	$_SESSION['user'] = $config->user_manager->get_user($_SESSION['user_id']);

/*
 * This is a shortcut for a very commonly used function. Any user management
 * component should provide a shortcut for gatekeeper.
 */
function gatekeeper($ability = NULL) {
	global $config;
	return $config->user_manager->gatekeeper($ability);
}

?>