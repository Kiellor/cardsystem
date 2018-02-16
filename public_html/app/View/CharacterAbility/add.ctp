<!-- app/View/CharacterAbility/add.ctp -->
<?php echo $this->Form->create('CharacterAbility'); ?>
    <?php echo $this->Form->input('Character.id', array('type' => 'select', 'options'=>$characters, 'label' => 'Character ID', 'default' => 1)); ?>
    <?php echo $this->Form->input('Ability.id', array('type' => 'select', 'options'=>$abilities, 'label' => 'Ability ID', 'default' => 1)); ?>
    <?php echo $this->Form->input('CharacterAbility.build_spent', array('type' => 'text')); ?>
    <button type="submit">Save</button>
<?php echo $this->Form->end(); ?>