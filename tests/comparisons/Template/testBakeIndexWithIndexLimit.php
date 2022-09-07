<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var iterable<\Cake\Datasource\EntityInterface> $templateTaskComments
 */
?>
<div class="templateTaskComments index content">
    <?= $this->Html->link(__('New Template Task Comment'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Template Task Comments') ?></h3>
    <div class="table-responsive">
        <table>
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
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
