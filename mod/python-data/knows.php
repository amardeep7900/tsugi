<?php

require_once('data_util.php');

use \Tsugi\Core\LTIX;
use \Tsugi\Util\LTI;
use \Tsugi\Util\Mersenne_Twister;

$sanity = array(
  'urllib' => 'You should use urllib to retrieve the HTML Pages',
  'BeautifulSoup' => 'You should use BeautifulSoup to parse the HTML'
);

// Compute the stuff for the output
$sample_pages = 4;
$sample_pos = 2;
$actual_pages = 7;
$actual_pos = 17;

$code = 12345;
$sample_names = array();
$names = getShuffledNames($code);
$name = $names[$sample_pos];
$sample_names[] = $name;
for($p=0;$p<$sample_pages;$p++) {
    $code = array_search($name, $NAMES);
    $names = getShuffledNames($code);
    $name = $names[$sample_pos];
    $sample_last = $name;
    $sample_names[] = $name;
}

$code = $USER->id+$LINK->id+$CONTEXT->id;
$actual_names = array();
$names = getShuffledNames($code);
$name = $names[$actual_pos];
$actual_names[] = $name;
for($p=0;$p<$actual_pages;$p++) {
    $code = array_search($name, $NAMES);
    $names = getShuffledNames($code);
    $name = $names[$actual_pos];
    $actual_last = $name;
    $actual_names[] = $name;
}

$oldgrade = $RESULT->grade;
if ( isset($_POST['name']) && isset($_POST['code']) ) {
    $RESULT->setJsonKey('code', $_POST['code']);

    if ( $_POST['name'] != $actual_last ) {
        $_SESSION['error'] = "Your name did not match";
        header('Location: '.addSession('index.php'));
        return;
    }

    $val = validate($sanity, $_POST['code']);
    if ( is_string($val) ) {
        $_SESSION['error'] = $val;
        header('Location: '.addSession('index.php'));
        return;
    }

    LTIX::gradeSendDueDate(1.0, $oldgrade, $dueDate);
    // Redirect to ourself
    header('Location: '.addSession('index.php'));
    return;
}

if ( $LINK->grade > 0 ) {
    echo('<p class="alert alert-info">Your current grade on this assignment is: '.($LINK->grade*100.0).'%</p>'."\n");
}

if ( $dueDate->message ) {
    echo('<p style="color:red;">'.$dueDate->message.'</p>'."\n");
}
$url = curPageUrl();
$sample_url = str_replace('index.php','data/known_by_'.$sample_names[0].'.html',$url);
$actual_url = str_replace('index.php','data/known_by_'.$actual_names[0].'.html',$url);
?>
<p>
<b>Following Links in Python</b>
<p>
In this assignment you will write a Python program that expands on
<a href="http://www.pythonlearn.com/code/urllinks.py" target="_blank">http://www.pythonlearn.com/code/urllinks.py</a>.
The program will use <b>urllib</b> to read the HTML from the data files below, 
extract the href= vaues from the anchor tags, scan for a tag that is in 
a particular position from the top and follow that link and repeat the process
a number of times and report the last name you find.
</p>
<p>
We provide two files for this assignment.  One is a sample file where we give
you the name for your testing and the other is the actual data you need 
to process for the assignment
<ul>
<li> Sample problem: Start at 
<a href="<?= deHttps($sample_url) ?>" target="_blank"><?= deHttps($sample_url) ?></a> <br/>
Find the link at position <b><?= $sample_pos+1 ?></b> (the first name is 1).
Follow that link.  Repeat this process <b><?= $sample_pages ?></b> times.  The 
answer is the last name that you retrieve.<br/>
Sequence of names: 
<?php
    foreach($sample_names as $name) {
        echo($name.' ');
    }
    echo("<br/>\n");
?>
Last name in sequence: <?= $sample_last ?><br/>
</li>
<li> Actual problem: Start at: <a href="<?= deHttps($actual_url) ?>" target="_blank"><?= deHttps($actual_url) ?></a> <br/>
Find the link at position <b><?= $actual_pos+1 ?></b> (the first name is 1).
Follow that link.  Repeat this process <b><?= $actual_pages ?></b> times.  The 
answer is the last name that you retrieve.<br/>
<!--
Sequence of names: 
<?php
    foreach($actual_names as $name) {
        // echo($name.' ');
    }
    echo("<br/>\n");
?>
-->
First character of last name in sequence: <?= substr($actual_last,0,1) ?><br/>
</li>
</ul>
<b>Strategy</b>
<p>
The web pages tweak the height between the links and hide the page after a few seconds
to make it difficult for you to do the assignment without writing a Python program.  
But frankly with a little effort and patience you can overcome these attempts to make it 
a little harder to complete the assignment without writing a Python program.
But that is not the point.   The point is to write a clever Python program to solve the
program.
</p>
<p><b>Sample execution</b>
<p>
Here is a sample execution of a solution:
<pre>
$ python solution.py 
Enter URL: http://pr4e.dr-chuck.com/ ... /known_by_Iria.html
Enter count: 4
Enter position: 3
Retrieving: http://pr4e.dr-chuck.com/ ... /known_by_Iria.html
Retrieving: http://pr4e.dr-chuck.com/ ... /known_by_Sonniva.html
Retrieving: http://pr4e.dr-chuck.com/ ... /known_by_Roman.html
Retrieving: http://pr4e.dr-chuck.com/ ... /known_by_Miranne.html
Last Url: http://pr4e.dr-chuck.com/ ..../known_by_Victoria.html
</pre>
The answer to the assignment for this execution is "Victoria".
</p>
<p><b>Turning in the Assignment</b>
<form method="post">
Enter the last name retrieved and your Python code below:<br/>
Name: <input type="text" size="20" name="name">
(name stats with <?= substr($actual_last,0,1) ?>)
<input type="submit" value="Submit Assignment"><br/>
Python code:<br/>
<textarea rows="20" style="width: 90%" name="code"></textarea><br/>
</form>