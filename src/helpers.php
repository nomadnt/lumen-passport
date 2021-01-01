<?php

if (! function_exists('config_path')) {
    /**
     * Get the path to the configuration folder.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->getConfigurationPath().($path ? '/'.$path : $path);
    }
}
