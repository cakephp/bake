<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Template Task Comment'), ['action' => 'edit', $templateTaskComment->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Template Task Comment'), ['action' => 'delete', $templateTaskComment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $templateTaskComment->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Template Task Comments'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Template Task Comment'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Articles'), ['controller' => 'Articles', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Article'), ['controller' => 'Articles', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="templateTaskComments view large-9 medium-8 columns content">
    <h3><?= h($templateTaskComment->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Article') ?></th>
            <td><?= $templateTaskComment->has('article') ? $this->Html->link($templateTaskComment->article->title, ['controller' => 'Articles', 'action' => 'view', $templateTaskComment->article->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Published') ?></th>
            <td><?= h($templateTaskComment->published) ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($templateTaskComment->id) ?></td>
        </tr>
        <tr>
            <th><?= __('User Id') ?></th>
            <td><?= $this->Number->format($templateTaskComment->user_id) ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($templateTaskComment->created) ?></tr>
        </tr>
        <tr>
            <th><?= __('Updated') ?></th>
            <td><?= h($templateTaskComment->updated) ?></tr>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Comment') ?></h4>
        <?= $this->Text->autoParagraph(h($templateTaskComment->comment)); ?>
    </div>
</div>
