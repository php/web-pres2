<?php
if (!function_exists('file_get_contents')) {
    function file_get_contents ($filename)
    {
        return implode ("", file ($filename));
    }
}
?>
