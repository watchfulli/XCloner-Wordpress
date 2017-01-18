<?php

class Xcloner_Sanitization {
	
	public function sanitize_input_as_int($option)
	{
		return filter_var($option, FILTER_SANITIZE_NUMBER_INT);
	}
	
	public function sanitize_input_as_string($option)
	{
		return filter_var($option, FILTER_SANITIZE_STRING);
	}
	
	public function sanitize_input_as_path($option)
	{
		return filter_var($option, FILTER_SANITIZE_URL);
	}
	
	public function sanitize_input_as_email($option)
	{
		return filter_var($option, FILTER_SANITIZE_EMAIL);
	}

	public function sanitize_input_as_raw($option)
	{
		return filter_var($option, FILTER_UNSAFE_RAW);
	}
	
}
