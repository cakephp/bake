<?php // Dummy php code whatever ?>
<% foreach([1,2,3] as $number): %>
    <%= $number %>
<% endforeach; %>

<% foreach([1,2,3] as $number): %>
    number
<% endforeach; %>

This is all going to be squashed on one line:
<% foreach([1,2,3] as $number): -%>
 <%= $number -%>
<% endforeach; -%>
. This bit of text too

This will not be on one line though
<% foreach([1,2,3] as $number): -%>
    number
<% endforeach; -%>
And neither this text because "number" ends with a newline
