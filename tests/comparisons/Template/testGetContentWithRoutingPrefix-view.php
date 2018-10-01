<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Bake\Test\App\Model\Entity\TestTemplateModel $testTemplateModel
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Test Template Model'), ['action' => 'edit', $testTemplateModel->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Test Template Model'), ['action' => 'delete', $testTemplateModel->id], ['confirm' => __('Are you sure you want to delete # {0}?', $testTemplateModel->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Test Template Models'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Test Template Model'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="testTemplateModels view large-9 medium-8 columns content">
    <h3><?= h($testTemplateModel->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($testTemplateModel->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Body') ?></th>
            <td><?= h($testTemplateModel->body) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($testTemplateModel->id) ?></td>
        </tr>
    </table>
</div>
