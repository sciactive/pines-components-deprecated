<?php
/**
 * Shows the results from a file uploader test.
 *
 * @package Components\elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'elFinder Uploader Results';
?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h4>File: <?php echo htmlspecialchars($this->file); ?></h4>
	</div>
	<div class="pf-element">
		<span class="pf-label">Check Passed</span>
		<span class="pf-note">If the check does not pass, the user is probably trying to hack the system.</span>
		<span class="pf-field"><?php echo ($pines->uploader->check($this->file)) ? 'Yes' : 'No'; ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Real Path</span>
		<span class="pf-note">This path can be used in code to manipulate the file.</span>
		<span class="pf-field"><?php $real = $pines->uploader->real($this->file); echo htmlspecialchars($real); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Relative URL</span>
		<span class="pf-note">This path can be used for browser access to the file.</span>
		<span class="pf-field"><?php $url = $pines->uploader->url($real); echo htmlspecialchars($url); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Full URL</span>
		<span class="pf-note">This path can be used for access to the file in email, another server, etc.</span>
		<span class="pf-field"><?php $furl = $pines->uploader->url($real, true); echo htmlspecialchars($furl); ?></span>
	</div>
	<div class="pf-element pf-heading">
		<h4>Temp File: <?php echo htmlspecialchars($this->tmpfile); ?></h4>
	</div>
	<div class="pf-element">
		<span class="pf-label">Check Passed</span>
		<span class="pf-note">The check for temp files is a little different.</span>
		<span class="pf-field"><?php echo ($pines->uploader->temp($this->tmpfile)) ? 'Yes' : 'No'; ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Real Path</span>
		<span class="pf-note">This path can be used in code to manipulate the file.</span>
		<span class="pf-field"><?php $real = $pines->uploader->temp($this->tmpfile); echo htmlspecialchars($real); ?></span>
	</div>
	<div class="pf-element pf-heading">
		<h4>Folder: <?php echo htmlspecialchars($this->folder); ?></h4>
	</div>
	<div class="pf-element">
		<span class="pf-label">Check Passed</span>
		<span class="pf-field"><?php echo ($pines->uploader->check($this->folder)) ? 'Yes' : 'No'; ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Real Path</span>
		<span class="pf-field"><?php $real = $pines->uploader->real($this->folder); echo htmlspecialchars($real); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Relative URL</span>
		<span class="pf-field"><?php $url = $pines->uploader->url($real); echo htmlspecialchars($url); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Full URL</span>
		<span class="pf-field"><?php $furl = $pines->uploader->url($real, true); echo htmlspecialchars($furl); ?></span>
	</div>
	<fieldset class="pf-group">
		<legend>Multi-File Uploading Result</legend>
		<?php foreach ((array) $this->files as $file) { ?>
		<div class="pf-element pf-heading">
			<h4>File: <?php echo htmlspecialchars($file); ?></h4>
		</div>
		<div class="pf-element">
			<span class="pf-label">Check Passed</span>
			<span class="pf-field"><?php echo ($pines->uploader->check($file)) ? 'Yes' : 'No'; ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Real Path</span>
			<span class="pf-field"><?php $real = $pines->uploader->real($file); echo htmlspecialchars($real); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Relative URL</span>
			<span class="pf-field"><?php $url = $pines->uploader->url($real); echo htmlspecialchars($url); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Full URL</span>
			<span class="pf-field"><?php $furl = $pines->uploader->url($real, true); echo htmlspecialchars($furl); ?></span>
		</div>
		<?php } ?>
	</fieldset>
</div>