<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Template Task Comment'), ['action' => 'edit', $templateTaskComment->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Template Task Comment'), ['action' => 'delete', $templateTaskComment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $templateTaskComment->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Template Task Comments'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Template Task Comment'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Authors'), ['controller' => 'TemplateTaskAuthors', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Author'), ['controller' => 'TemplateTaskAuthors', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="templateTaskComments view large-9 medium-8 columns content">
    <h3><?= h($templateTaskComment->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Name') ?></th>
            <td><?= h($templateTaskComment->name) ?></td>
        </tr>
        <tr>
            <th><?= __('Body') ?></th>
            <td><?= h($templateTaskComment->body) ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($templateTaskComment->id) ?></td>
        </tr>
    </table>
</div>
