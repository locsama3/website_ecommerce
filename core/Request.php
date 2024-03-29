<?php 
	
	class Request
	{
		private $__rules = [], $__messages = [], $__errors = [];
		public $db;

	    /*
			1. Method
			2. Body
	    */
		public function __construct()
		{
			$this->db = new Database();
		}

		public function getMethod()
		{
			return strtolower($_SERVER['REQUEST_METHOD']);
		}

		public function isPost()
		{
			if($this->getMethod() == 'post'){
				return true;
			}

			return false;
		}

		public function isGet()
		{
			if($this->getMethod() == 'get'){
				return true;
			}

			return false;
		}

		public function getFields()
		{
			$dataFields = [];

			if($this->isGet()){
				// Xử lý lấy dữ liệu với phương thức get
				if(!empty($_GET)){
					foreach ($_GET as $key => $value) {
						if(is_array($value)){
							$dataFields[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
						}else{
							$dataFields[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
						}
						
					}
				}
			}

			if($this->isPost()){
				// Xử lý lấy dữ liệu với phương thức post
				if(!empty($_POST)){
					foreach ($_POST as $key => $value) {
						if(is_array($value)){
							$dataFields[$key] = filter_input(INPUT_POST, $key, FILTER_REQUIRE_ARRAY);
						}else{
							$dataFields[$key] = filter_input(INPUT_POST, $key/*, FILTER_SANITIZE_SPECIAL_CHARS*/);
						}	
					}
				}
			}

			return $dataFields;
		}

		public function getFiles()
		{
			$dataFiles = [];

			if($this->isPost()){
				// Xử lý lấy file với phương thức post
				if(!empty($_FILES)){
					foreach ($_FILES as $key => $value) {
						$dataFiles[$key] = $value;
					}
				}
			}

			return $dataFiles;
		}

		// Xử lý validate form
		// set rules
		public function rules($rules = [])
		{
			$this->__rules = $rules;
		}

		// set message
		public function message($messages = [])
		{
			$this->__messages = $messages;
		}

		// run validate
		public function validate()
		{
			$this->__rules = array_filter($this->__rules);

			$checkValidate = true;

			if(!empty($this->__rules)){

				$dataFields = $this->getFields();

				foreach ($this->__rules as $fieldName => $ruleItem) {
					$ruleItemArr = explode('|', $ruleItem);

					foreach ($ruleItemArr as $rule) {

						$ruleName = null;
						$ruleValue = null;

					    $ruleArr = explode(':', $rule);

					    $ruleName = reset($ruleArr);

					    if(count($ruleArr) > 1){
					    	$ruleValue = end($ruleArr);
					    }

					    if($ruleName == 'required'){
					    	if(empty(trim($dataFields[$fieldName]))){
					    		$this->setErrors($fieldName, $ruleName);
					    		$checkValidate = false;
					    	}
					    }

					    if($ruleName == 'min'){
					    	if(strlen(trim($dataFields[$fieldName])) < $ruleValue){
					    		$this->setErrors($fieldName, $ruleName);
					    		$checkValidate = false;
					    	}
					    }

					    if($ruleName == 'max'){
					    	if(strlen(trim($dataFields[$fieldName])) > $ruleValue){
					    		$this->setErrors($fieldName, $ruleName);
					    		$checkValidate = false;
					    	}
					    }

					    if($ruleName == 'email'){
					    	if(!filter_var($dataFields[$fieldName], FILTER_VALIDATE_EMAIL)){
					    		$this->setErrors($fieldName, $ruleName);
					    		$checkValidate = false;
					    	}
					    }

					    if($ruleName == 'match'){
					    	if(trim($dataFields[$fieldName]) != trim($dataFields[$ruleValue])){
					    		$this->setErrors($fieldName, $ruleName);
					    		$checkValidate = false;
					    	}
					    }

					    if($ruleName == 'unique'){
					    	$tableName = null;
					    	$fieldCheck = null;

					    	if(!empty($ruleArr[1])){
					    		$tableName = $ruleArr[1];
					    	}

					    	if(!empty($ruleArr[2])){
					    		$fieldCheck = $ruleArr[2];
					    	}

					    	if(!empty($tableName) && !empty($fieldCheck)){
					    		if(count($ruleArr) == 3){
					    			$checkExist = $this->db->query("SELECT count(*) FROM $tableName 
					    						  WHERE $fieldCheck ='trim($dataFields[$fieldName])'")->rowCount();
					    		}elseif (count($ruleArr) == 4) {
					    			if(!empty($ruleArr[3]) && preg_match('~.+?=.+?~is', $ruleArr[3])){
					    				$conditionWhere = $ruleArr[3];
					    				$conditionWhere = str_replace('=', '<>', $conditionWhere);
					    				$checkExist = $this->db->query("SELECT count(*) FROM $tableName 
					    						  WHERE $fieldCheck ='trim($dataFields[$fieldName])' 
					    						  		AND $conditionWhere")->rowCount();
					    			}
					    		}
					    		
					    		if(!empty($checkExist)){
					    			$this->setErrors($fieldName, $ruleName);
					    			$checkValidate = false;
					    		}
					    	}
					    }

					    // Callback validate
					    if(preg_match('~^callback_(.+)~is', $ruleName, $callbackArr)){
					    	if(!empty($callbackArr[1])){
					    		$callbackName = $callbackArr[1];
					    		$controller = App::$app->getCurrentController();

					    		if(method_exists($controller, $callbackName)){
				    				$checkCallback = call_user_func_array([$controller, $callbackName], [trim($dataFields[$fieldName])]);

				    				if(!$checkCallback){
				    					$this->setErrors($fieldName, $ruleName);
					    				$checkValidate = false;
				    				}
					    		}
					    	}
					    }
					}
				}
			}

			$sessionKey = Session::isInvalid();
			Session::flash($sessionKey.'_errors', $this->errors());
			Session::flash($sessionKey.'_old', $this->getFields());

			return $checkValidate;
		}

		// get errors
		public function errors($fieldName = '')
		{
			if(!empty($this->__errors)){
				if(empty($fieldName)){
					$errorsArr = [];
					foreach ($this->__errors as $key => $error) {
						$errorsArr[$key] = reset($error);
					}

					return $errorsArr;
				}
				return reset($this->__errors[$fieldName]);
			}

			return false;
		}

		// set errors
		public function setErrors($fieldName, $ruleName)
		{
    		$this->__errors[$fieldName][$ruleName] = $this->__messages[$fieldName. '.' .$ruleName];
		}

	}
	
 ?>