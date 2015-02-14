<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Edit Test Template Model'), ['action' => 'edit', $testTemplateModel->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Test Template Model'), ['action' => 'delete', $testTemplateModel->id], ['confirm' => __('Are you sure you want to delete # {0}?', $testTemplateModel->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Test Template Models'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Test Template Model'), ['action' => 'add']) ?> </li>
    </ul>
</div>
<div class="testTemplateModels view large-10 medium-9 columns">
    <h2><?= h($testTemplateModel->name) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __('Name') ?></h6>
            <p><?= h($testTemplateModel->name) ?></p>
            <h6 class="subheader"><?= __('Body') ?></h6>
            <p><?= h($testTemplateModel->body) ?></p>
        </div>
        <div class="large-2 columns numbers end">
            <h6 class="subheader"><?= __('Id') ?></h6>
            <p><?= $this->Number->format($testTemplateModel->id) ?></p>
        </div>
    </div>
</div>
