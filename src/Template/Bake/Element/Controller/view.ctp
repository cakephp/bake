<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$allAssociations = array_merge(
    $this->Bake->aliasExtractor($modelObj, 'BelongsTo'),
    $this->Bake->aliasExtractor($modelObj, 'BelongsToMany'),
    $this->Bake->aliasExtractor($modelObj, 'HasOne'),
    $this->Bake->aliasExtractor($modelObj, 'HasMany')
);

%>

    /**
     * View method
     *
<%
$primaryKeys = (array)$modelObj->primaryKey();
foreach ($primaryKeys as $primaryKeyComponent) { %>
     * @param string|null <%= $primaryKeyComponent %> <%= $singularHumanName %> primaryKey.
<% } %>
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
<%
$actionArguments = [];
foreach ($primaryKeys as $primaryKeyComponent) {
    $actionArguments[] = '$' . $primaryKeyComponent . ' = null';
}
%>
    public function view(<%= join($actionArguments, ', ') %>)
    {
        <%
        $params = [];
        foreach ($primaryKeys as $primaryKeyComponent) {
            $params[] = '$' . $primaryKeyComponent;
        }
        %>
        $<%= $singularName%> = $this-><%= $currentModelName %>->get([<%= join($params, ', ') %>], [
            'contain' => [<%= $this->Bake->stringifyList($allAssociations, ['indent' => false]) %>]
        ]);
        $this->set('<%= $singularName %>', $<%= $singularName %>);
        $this->set('_serialize', ['<%= $singularName %>']);
    }
