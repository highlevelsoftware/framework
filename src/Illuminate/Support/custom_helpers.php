<?php

if ( ! function_exists('poly_each'))
{
	/**
	 * Poly fill the each method for php 7
	 *
	 * @param  array $input
	 * @return array
	 */
	function poly_each(array $input)
	{
	    $key   = key($input);
	    $value = current($input);
	    return [
	        0       => $key,
            1       => $value,
            'key'   => $key,
            'value' => $value
        ];
	}
}