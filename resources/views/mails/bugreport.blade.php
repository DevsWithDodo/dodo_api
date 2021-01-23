<b>Reporter:</b> {{ ($reporter == null ? 'Unathenticated. ' : $reporter->id . ' - ' . $reporter->username) }}
<br>
<b>Bug:</b><br> 
{{ $bug_description }}