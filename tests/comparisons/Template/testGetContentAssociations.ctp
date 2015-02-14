<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Edit Template Task Comment'), ['action' => 'edit', $templateTaskComment->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Template Task Comment'), ['action' => 'delete', $templateTaskComment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $templateTaskComment->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Template Task Comments'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Template Task Comment'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Authors'), ['controller' => 'TemplateTaskAuthors', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Author'), ['controller' => 'TemplateTaskAuthors', 'action' => 'add']) ?> </li>
    </ul>
</div>
<div class="templateTaskComments view large-10 medium-9 columns">
    <h2><?= h($templateTaskComment->name) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __('Name') ?></h6>
            <p><?= h($templateTaskComment->name) ?></p>
            <h6 class="subheader"><?= __('Body') ?></h6>
            <p><?= h($templateTaskComment->body) ?></p>
        </div>
        <div class="large-2 columns numbers end">
            <h6 class="subheader"><?= __('Id') ?></h6>
            <p><?= $this->Number->format($templateTaskComment->id) ?></p>
        </div>
    </div>
</div>
