<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('List Test Template Models'), ['action' => 'index']) ?></li>
    </ul>
</div>
<div class="testTemplateModels form large-10 medium-9 columns">
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
