<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Bake\Test\App\Model\Entity\TestTemplateModel $testTemplateModel
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Test Template Model'), ['action' => 'edit', $testTemplateModel->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Test Template Model'), ['action' => 'delete', $testTemplateModel->id], ['confirm' => __('Are you sure you want to delete # {0}?', $testTemplateModel->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Test Template Models'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Test Template Model'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="testTemplateModels view content">
            <h3><?= h($testTemplateModel->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($testTemplateModel->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Body') ?></th>
                    <td><?= h($testTemplateModel->body) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($testTemplateModel->id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
