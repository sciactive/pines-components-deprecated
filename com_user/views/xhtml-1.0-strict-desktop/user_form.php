<?php
defined('D_RUN') or die('Direct access prohibited');
$page->head("<script type=\"text/javascript\" src=\"components/com_user/js/verify.js\"></script>\n");
?>
<form method="post" id="user_details" action="" onsubmit="return verify_form('user_details');">
<div class="stylized stdform">
<h2><?php echo $this->heading; ?></h2>
<p>Provide user details in this form.</p>
<?php if ( !is_null($this->id) ) { ?>
<input type="hidden" name="user_id" value="<?php echo $this->id; ?>" />
<?php } ?>
<label>Username<input type="text" name="username" value="<?php echo $this->username; ?>" /></label>
<label>Name<input type="text" name="name" value="<?php echo $this->name; ?>" /></label>
<label>Email<input type="text" name="email" value="<?php echo $this->email; ?>" /></label>
<?php if (is_null($this->id)) { ?>
<label>Password<span class="small"><?php echo ($config->com_user->empty_pw ? "May be blank." : "&nbsp;"); ?></span>
<?php } else { ?>
<label>Update Password<span class="small">Leave blank, if not changing.</span>
<?php } ?>
<input type="password" name="password" /></label>
<label>Repeat Password<input type="password" name="password2" /></label>
<?php if ( $this->display_abilities ) { ?>
    <input type="hidden" name="abilities" value="true" />
    <label>Abilities</label><br />
    <?php foreach ($this->sections as $cur_section) {
        $section_abilities = $config->ability_manager->get_abilities($cur_section);
        if ( count($section_abilities) ) { ?>
        <table width="100%">
            <thead><tr><th colspan="2"><?php echo $cur_section; ?></th></tr></thead>
            <tbody>
                <?php foreach ($section_abilities as $cur_ability) { ?>
                <tr><td><label><input type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>"
                    <?php if ( array_search($cur_section.'/'.$cur_ability['ability'], $this->user_abilities) !== false ) { ?>
                        checked
                    <?php } ?>
                     />&nbsp;<?php echo $cur_ability['title']; ?></label></td><td style="width: 80%;"><?php echo $cur_ability['description']; ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
        <?php }
    } ?>
<?php } ?>
<input type="hidden" name="action" value="<?php echo $this->new_action; ?>" />
<input type="submit" value="Submit" />
<input type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'manageusers'); ?>';" value="Cancel" />
<div class="spacer"></div>
</div>
</form>