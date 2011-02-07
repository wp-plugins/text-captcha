<h1>Here is your admin form</h1>
<?php
extract($config); // config passed in from text-captcha.php

// set up the radio buttons for lazy programming

// print_r($settings);


?>

<form method="post" action="plugins.php?page=text-captcha" class="text_captcha_admin" id="text_captcha_admin">
<h3>Settings you can toggle</h3>
<ul id="maths">
<?php
foreach($settings as $var => $val) {
    echo '<li class="settingsToggle">'.$var.':
        <input class="toggle '.$val.'" type="button" id="'.$var.'" name="'.$var.'" value="'.$val.'"> 
        <input type="hidden" id="settings_'.$var.'" name="settings_'.$var.'" value="'.$val.'"> 
        </li>';
}
?>        
</ul>
<h3>Custom questions and answers</h3>
<table style="width: 65%;" id="customQuestions">
<tbody>
<tr>
    <td>Question</td>
    <td>Answer</td>
    <td></td>
</tr>
<?php
// takes an array like:
// $questions[1]=array('question'=>'what color is the sky on a clear day?', 'answer'=> 'blue');

// set length of text boxes to something sensible
$max_len=48;
foreach($questions as $id => $q_and_a) {
    extract($q_and_a);
    $len=strlen($question);
    if($len > $max_len) {
        $max_len=$len;
    }
}

$width=$max_len*6;

foreach($questions as $id => $q_and_a) {
    extract($q_and_a);
    echo '<tr id="container'.$id.'">';
    echo '<td><input id="answer'.$id.'" type="text" style="width: '.$width.'px;" name="questions['.$id.'][question]" value="'.$question.'"></td>';
    echo '<td><input id="question'.$id.'" type="text" name="questions['.$id.'][answer]" value="'.$answer.'"></td>';
    echo '<td><input type="button" class="delRow" id="'.$id.'" value="delete this question"></td>';
    echo '</tr>';
}
?>
</tbody>
</table>
<input type="hidden" id="max_id" name="max_id" value="<?php echo $id; ?>">
<input type="button" id="addRow" name="addRow" value="Add a question">
<input type="submit" id="updateForm" name="updateForm" value="Update Settings">
</form>
