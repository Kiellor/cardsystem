<?php
    header('Content-Disposition: attachment; filename="'.$filename.'.pdf"');
    echo $content_for_layout;
?>