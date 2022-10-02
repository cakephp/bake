<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Bake\Test\App\Model\Entity\TemplateTaskComment $templateTaskComment
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Template Task Comment'), ['action' => 'edit', $templateTaskComment->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Template Task Comment'), ['action' => 'delete', $templateTaskComment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $templateTaskComment->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Template Task Comments'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Template Task Comment'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="templateTaskComments view content">
            <h3><?= h($templateTaskComment->name) ?></h3>
            <table>
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
    </div>
</div>
