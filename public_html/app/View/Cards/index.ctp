<!-- File: /app/View/Cards/index.ctp -->

<h1>Card Marshall Input Center</h1>

<?php echo $this->Form->create('Character'); ?>
    <?php echo $this->Form->input('Character.cardnumber', array('type' => 'text', 'label' => 'Card Number')); ?>
    <button type="submit">Search</button>
<?php echo $this->Form->end(); ?>