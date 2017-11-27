<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $templateTaskComments
 */
?>
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
                <th scope="col"><?= $this->Paginator->sort('id', ['label' => __('Id')]) ?></th>
                <th scope="col"><?= $this->Paginator->sort('article_id', ['label' => __('Article Id')]) ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id', ['label' => __('User Id')]) ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
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
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
