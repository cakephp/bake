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
 
use Cake\Utility\Inflector;
%>

    /**
     * Delete method
     *
<%
$primaryKeys = (array)$modelObj->primaryKey();
foreach ($primaryKeys as $primaryKeyComponent) { %>
     * @param string|null <%= Inflector::variable($primaryKeyComponent) %> <%= $singularHumanName %> primaryKey.
<% } %>
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
<%
$actionArguments = [];
foreach ($primaryKeys as $primaryKeyComponent) {
    $actionArguments[] = '$' . Inflector::variable($primaryKeyComponent) . ' = null';
}
%>
    public function delete(<%= join($actionArguments, ', ') %>)
    {
        $this->request->allowMethod(['post', 'delete']);
        <%- 
        $primaryKeyArguments = [];
        foreach ($primaryKeys as $primaryKeyComponent) {
            $primaryKeyArguments[] = '$' . Inflector::variable($primaryKeyComponent);
        }
        %>
        $<%= $singularName %> = $this-><%= $currentModelName %>->get([<%= join($primaryKeyArguments, ', ') %>]);
        if ($this-><%= $currentModelName %>->delete($<%= $singularName %>)) {
            $this->Flash->success(__('The <%= strtolower($singularHumanName) %> has been deleted.'));
        } else {
            $this->Flash->error(__('The <%= strtolower($singularHumanName) %> could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
