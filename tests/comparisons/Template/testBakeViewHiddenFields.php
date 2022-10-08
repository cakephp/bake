<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Bake\Test\App\Model\Entity\HiddenField $hiddenField
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Hidden Field'), ['action' => 'edit', $hiddenField->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Hidden Field'), ['action' => 'delete', $hiddenField->id], ['confirm' => __('Are you sure you want to delete # {0}?', $hiddenField->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Hidden Fields'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Hidden Field'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="hiddenFields view content">
            <h3><?= h($hiddenField->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($hiddenField->id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
