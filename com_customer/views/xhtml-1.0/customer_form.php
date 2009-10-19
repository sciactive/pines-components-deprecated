<?php
/**
 * Provides a form for the user to edit a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<form class="pform" method="post" id="customer_details" action="<?php echo $config->template->url(); ?>">
<fieldset>
    <legend><?php echo $this->heading; ?></legend>
    <div class="element heading">
        <p>Provide customer details in this form.</p>
    </div>
    <div class="element">
        <label><span class="label">Username</span>
        <input class="field" type="text" name="username" size="20" value="<?php echo $this->username; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Name</span>
        <input class="field" type="text" name="name" size="20" value="<?php echo $this->name; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Email</span>
        <input class="field" type="text" name="email" size="20" value="<?php echo $this->email; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label"><?php if (!is_null($this->id)) echo 'Update '; ?>Password</span>
        <?php if (is_null($this->id)) {
            echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
        } else {
            echo '<span class="note">Leave blank, if not changing.</span>';
        } ?>
        <input class="field" type="password" name="password" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Repeat Password</span>
        <input class="field" type="password" name="password2" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Company</span>
        <input class="field" type="text" name="company" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Job Title</span>
        <input class="field" type="text" name="job_title" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Address 1</span>
        <input class="field" type="text" name="address_1" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Address 2</span>
        <input class="field" type="text" name="address_2" size="20" /></label>
    </div>
    <div class="element">
        <span class="label">City, State</span>
        <input class="field" type="text" name="city" size="15" />
        <input class="field" type="text" name="state" size="2" />
    </div>
    <div class="element">
        <label><span class="label">Zip</span>
        <input class="field" type="text" name="zip" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Home Phone</span>
        <input class="field" type="text" name="phone_home" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Work Phone</span>
        <input class="field" type="text" name="phone_work" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Cell Phone</span>
        <input class="field" type="text" name="phone_cell" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Fax</span>
        <input class="field" type="text" name="fax" size="20" /></label>
    </div>
	<div class="element buttons">
        <?php if ( !is_null($this->id) ) { ?>
        <input type="hidden" name="user_id" value="<?php echo $this->id; ?>" />
        <?php } ?>
        <input type="hidden" name="option" value="<?php echo $this->new_option; ?>" />
        <input type="hidden" name="action" value="<?php echo $this->new_action; ?>" />
        <input class="button" type="submit" value="Submit" />
        <input class="button" type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'manageusers'); ?>';" value="Cancel" />
    </div>
</fieldset>
</form>