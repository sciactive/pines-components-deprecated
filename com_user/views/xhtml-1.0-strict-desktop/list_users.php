<?php
defined('D_RUN') or die('Direct access prohibited');
?>
<?php foreach($this->users as $user) { ?>
<?php echo $this->line_header; ?><strong><?php echo $user->username; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'edituser', array('user_id' => urlencode($user->guid))); ?>';" value="Edit" />
<input type="button" onclick="if(confirm('Are you sure you want to delete \'<?php echo $user->username; ?>\'?')) {window.location='<?php echo $config->template->url('com_user', 'deleteuser', array('user_id' => urlencode($user->guid))); ?>';}" value="Delete" />
<?php echo $this->line_footer; ?><br /><br />
<?php } ?>