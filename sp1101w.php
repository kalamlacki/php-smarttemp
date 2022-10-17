<HTML>
<BODY>
<PRE>
<?php
$fp=popen("/usr/bin/sudo /home/lukasz/SP1101w/detect 2>/dev/null","r");
while (!feof($fp)) {
    $buffer = fgets($fp, 1024);
    echo $buffer;
}
pclose($fp); 

?>
</PRE>
</BODY>
</HTML>