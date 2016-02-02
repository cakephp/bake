<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Author'), ['action' => 'edit', $author->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Author'), ['action' => 'delete', $author->id], ['confirm' => __('Are you sure you want to delete # {0}?', $author->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Authors'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Author'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Roles'), ['controller' => 'Roles', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Role'), ['controller' => 'Roles', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Profiles'), ['controller' => 'Profiles', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Profile'), ['controller' => 'Profiles', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Articles'), ['controller' => 'Articles', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Article'), ['controller' => 'Articles', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="authors view large-9 medium-8 columns content">
    <h3><?= h($author->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Role') ?></th>
            <td><?= $author->has('role') ? $this->Html->link($author->role->name, ['controller' => 'Roles', 'action' => 'view', $author->role->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Name') ?></th>
            <td><?= h($author->name) ?></td>
        </tr>
        <tr>
            <th><?= __('Profile') ?></th>
            <td><?= $author->has('profile') ? $this->Html->link($author->profile->id, ['controller' => 'Profiles', 'action' => 'view', $author->profile->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($author->id) ?></td>
        </tr>
        <tr>
            <th><?= __('Member Number') ?></th>
            <td><?= $this->Number->format($author->member_number) ?></td>
        </tr>
        <tr>
            <th><?= __('Account Balance') ?></th>
            <td><?= $this->Number->format($author->account_balance) ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($author->created) ?></td>
        </tr>
        <tr>
            <th><?= __('Modified') ?></th>
            <td><?= h($author->modified) ?></td>
        </tr>
        <tr>
            <th><?= __('Member') ?></th>
            <td><?= $author->member ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Description') ?></h4>
        <?= $this->Text->autoParagraph(h($author->description)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Articles') ?></h4>
        <?php if (!empty($author->articles)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Author Id') ?></th>
                <th><?= __('Title') ?></th>
                <th><?= __('Body') ?></th>
                <th><?= __('Published') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($author->articles as $articles): ?>
            <tr>
                <td><?= h($articles->id) ?></td>
                <td><?= h($articles->author_id) ?></td>
                <td><?= h($articles->title) ?></td>
                <td><?= h($articles->body) ?></td>
                <td><?= h($articles->published) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Articles', 'action' => 'view', $articles->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $articles->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Articles', 'action' => 'delete', $articles->id], ['confirm' => __('Are you sure you want to delete # {0}?', $articles->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
