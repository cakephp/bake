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
            echo $this->Form->input('name');
            echo $this->Form->input('body');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
