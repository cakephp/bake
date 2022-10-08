<?php
/**
 * @var \Bake\Test\App\View\AppView $this
 * @var \Bake\Test\App\Model\Entity\BakeArticle $testTemplateModel
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Test Template Models'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="testTemplateModels form content">
            <?= $this->Form->create($testTemplateModel) ?>
            <fieldset>
                <legend><?= __('Add Test Template Model') ?></legend>
                <?php
                    echo $this->Form->control('title');
                    echo $this->Form->control('body');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
