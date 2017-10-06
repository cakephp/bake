<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Bake\Test\App\Model\Entity\TestTemplateModel $testTemplateModel
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Test Template Models'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="testTemplateModels form large-9 medium-8 columns content">
    <?= $this->Form->create($testTemplateModel) ?>
    <fieldset>
        <legend><?= __('Add Test Template Model') ?></legend>
        <?php
            echo $this->Form->control('name');
            echo $this->Form->control('body');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
