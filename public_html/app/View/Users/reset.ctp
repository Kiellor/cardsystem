<div class="users form">
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Please enter your email address'); ?></legend>
        <?php 
        	echo $this->Form->input('username', array('label' => 'Email Address'));
    	?>
    </fieldset>
<?php echo $this->Form->end(__('Reset Password')); ?>
</div>