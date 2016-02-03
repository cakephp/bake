<?php // Dummy php code whatever ?>
<% foreach([1,2,3] as $number): %>
    <%= $number %>
<% endforeach; %>

<% foreach([1,2,3] as $number): %>
    number
<% endforeach; %>

But this should make a difference:
<% foreach([1,2,3] as $number): -%>
    <%= $number -%>
<% endforeach; -%>
This will not be on a new line

<% foreach([1,2,3] as $number): -%>
    number
<% endforeach; -%>
This will though, as "number" ends with a newline
