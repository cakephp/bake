<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Template Task Comment'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Articles'), ['controller' => 'Articles', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Article'), ['controller' => 'Articles', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="templateTaskComments index large-9 medium-8 columns content">
    <h3><?= __('Template Task Comments') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?= $this->Paginator->sort('id') ?></th>
                <th><?= $this->Paginator->sort('article_id') ?></th>
                <th><?= $this->Paginator->sort('user_id') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templateTaskComments as $templateTaskComment): ?>
            <tr>
                <td><?= $this->Number->format($templateTaskComment->id) ?></td>
                <td><?= $templateTaskComment->has('article') ? $this->Html->link($templateTaskComment->article->title, ['controller' => 'Articles', 'action' => 'view', $templateTaskComment->article->id]) : '' ?></td>
                <td><?= $this->Number->format($templateTaskComment->user_id) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $templateTaskComment->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $templateTaskComment->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $templateTaskComment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $templateTaskComment->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
