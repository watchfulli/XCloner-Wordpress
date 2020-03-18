<?php

use League\Flysystem\Util;

class Xcloner_Sanitization
{

    /**
     * Construct method
     */
    public function __construct()
    {
    }

    /**
     * Sanitize input as INT
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_int($option)
    {
        return filter_var($option, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize input as FLOAT
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_float($option)
    {
        return filter_var($option, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Sanitize input as STRING
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_string($option)
    {
        return filter_var($option, FILTER_SANITIZE_STRING);
    }

    /**
     * Sanitize input as ABSOLUTE PATH
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_absolute_path($option)
    {
        $path = filter_var($option, FILTER_SANITIZE_URL);

        try {
            $option = Util::normalizePath($path);
        } catch (Exception $e) {
            add_settings_error('xcloner_error_message', '', __($e->getMessage()), 'error');
        }

        if ($path and !is_dir($path)) {
            add_settings_error('xcloner_error_message', '', __(sprintf('Invalid Server Path %s', $option)), 'error');

            return false;
        }

        return $path;
    }

    /**
     * Sanitize input as PATH
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_path($option)
    {
        return filter_var($option, FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize input as RELATIVE PATH
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_relative_path($option)
    {
        $option = filter_var($option, FILTER_SANITIZE_URL);
        $option = str_replace("..", "", $option);

        return $option;
    }

    /**
     * Sanitize input as EMAIL
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_email($option)
    {
        return filter_var($option, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Undocumented function as RAW
     *
     * @param [type] $option
     * @return void
     */
    public function sanitize_input_as_raw($option)
    {
        return filter_var($option, FILTER_UNSAFE_RAW);
    }
}
