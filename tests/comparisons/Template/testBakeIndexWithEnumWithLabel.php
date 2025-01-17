<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var iterable<\Cake\Datasource\EntityInterface> $bakeUsers
 */
?>
<div class="bakeUsers index content">
    <?= $this->Html->link(__('New Bake User'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Bake Users') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('username') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('updated') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bakeUsers as $bakeUser): ?>
                <tr>
                    <td><?= $this->Number->format($bakeUser->id) ?></td>
                    <td><?= h($bakeUser->username) ?></td>
                    <td><?= $bakeUser->status === null ? '' : h($bakeUser->status->label()) ?></td>
                    <td><?= h($bakeUser->created) ?></td>
                    <td><?= h($bakeUser->updated) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $bakeUser->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $bakeUser->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $bakeUser->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $bakeUser->id),
                            ]
                        ) ?>
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