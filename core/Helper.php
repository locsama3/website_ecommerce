<?php 
	$sessionKey = Session::isInvalid();
	$errors = Session::flash($sessionKey.'_errors');
	$old = Session::flash($sessionKey.'_old');

	// thông báo lỗi validate form
	if(!function_exists('form_error')){
		function form_error($fieldName, $before = '', $after = '')
		{
			global $errors;
			if (!empty($errors) && array_key_exists($fieldName, $errors)) {
				return $before.$errors[$fieldName].$after;
			}

			return false;
		}
	}

	// trả về dữ liệu cũ khi update form
	if(!function_exists('old')){
		function old($fieldName, $default='')
		{
			global $old;
			if (!empty($old[$fieldName])) {
				return $old[$fieldName];
			}

			return $default;
		}
	}
 ?>