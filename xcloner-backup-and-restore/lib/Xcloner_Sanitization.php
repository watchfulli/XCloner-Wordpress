<?php

namespace Watchfulli\XClonerCore;

use Exception;
use League\Flysystem\Util;

class Xcloner_Sanitization
{
    /**
     * @param mixed $option
     * @return int | false
     */
    public function sanitize_input_as_int($option)
    {
        return filter_var($option, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @param mixed $option
     * @return int | false
     */
    public function sanitize_input_as_float($option)
    {
        return filter_var($option, FILTER_VALIDATE_FLOAT);
    }

    /**
     * @param mixed $option
     * @return string | false
     */
    public function sanitize_input_as_string($option)
    {
        return filter_var($option, FILTER_SANITIZE_STRING);
    }

    /**
     * @param mixed $option
     * @return string | false
     */
    public function sanitize_input_as_absolute_path($option)
    {
        $path = filter_var($option, FILTER_SANITIZE_URL);

        try {
            $option = Util::normalizePath($path);
        } catch (Exception $e) {
            add_settings_error('xcloner_error_message', '', __($e->getMessage()), 'error');
        }

        if ($path && !is_dir($path)) {
            add_settings_error('xcloner_error_message', '', __(sprintf('Invalid Server Path %s', $option)), 'error');

            return false;
        }

        return $path;
    }

    /**
     * @param mixed $option
     * @return string | false
     */
    public function sanitize_input_as_path($option)
    {
        return filter_var($option, FILTER_SANITIZE_URL);
    }

    /**
     * @param mixed $option
     * @return string | false
     */
    public function sanitize_input_as_relative_path($option)
    {
        $option = filter_var($option, FILTER_SANITIZE_URL);
        if (!$option) {
            add_settings_error('xcloner_error_message', '', __(sprintf('Invalid Server Path %s', $option)));
            return false;
        }
        return str_replace("..", "", $option);
    }

    /**
     * @param mixed $option
     * @return string | false
     */
    public function sanitize_input_as_email($option)
    {
        return filter_var($option, FILTER_SANITIZE_EMAIL);
    }

    /**
     * @param mixed $option
     * @return string | false
     */
    public function sanitize_input_as_raw($option)
    {
        return filter_var($option, FILTER_UNSAFE_RAW);
    }
}
