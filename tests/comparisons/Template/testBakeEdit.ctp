<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $templateTaskComment->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $templateTaskComment->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Template Task Comments'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Articles'), ['controller' => 'Articles', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Article'), ['controller' => 'Articles', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="templateTaskComments form large-9 medium-8 columns content">
    <?= $this->Form->create($templateTaskComment) ?>
    <fieldset>
        <legend><?= __('Edit Template Task Comment') ?></legend>
        <?php
            echo $this->Form->input('article_id', ['options' => $articles]);
            echo $this->Form->input('user_id');
            echo $this->Form->input('comment');
            echo $this->Form->input('published');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
