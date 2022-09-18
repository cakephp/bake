<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var iterable<\Bake\Test\App\Model\Entity\HiddenField> $hiddenFields
 */
?>
<div class="hiddenFields index content">
    <?= $this->Html->link(__('New Hidden Field'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Hidden Fields') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hiddenFields as $hiddenField): ?>
                <tr>
                    <td><?= $this->Number->format($hiddenField->id) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $hiddenField->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $hiddenField->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $hiddenField->id], ['confirm' => __('Are you sure you want to delete # {0}?', $hiddenField->id)]) ?>
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
