<?php

	// run multiple tests
	class Test {
		public static function run($arg) {
			switch(gettype($arg)) {
				case "array":
					$numOfTests = count($arg);
					for($i=0; $i<$numOfTests; $i++) {
						$arg->run();
					}
				break;
				default:
					$arg->run();
				break;
			};
		}
	};
	
	// responsible for displaying the results
	class TestResult {
		public function TestResult() {
			echo '
				<style type="text/css">
					body {
						font-family: Tahoma;
						font-size: 14px;
					}
				</style>
			';
		}
		public function title($str) {
			echo '<h1 style="font-size:20px;">'.$str.'</h1>';
		}
		public function passed($str) {
			echo '<p style="color: #256D1B; margin: 0; padding: 0;">Passed &#187; '.$str.'</p>';
		}
		public function failed($str) {
			echo '<p style="color: #FF0000; font-weight: bold; margin: 0; padding: 0;">Failed &#187; '.$str.'</p>';
		}
		public function sectionStart($title) {
			echo '<fieldset><legend>'.$title.'</legend>';
		}
		public function sectionEnd() {
			echo '</fieldset>';
		}
		public function note($str) {
			echo '
				<p 
					style="
						border-left: solid 10px #42A5CA;
						border-top: solid 1px #49C2BF;
						margin: 2px 0 0 0;
						padding: 5px 0 5px 5px;
						font-size: 12px;
						font-weight: bold;
						color: #42A5CA;
					"
				>'.$str.'</p>';
		}
	};
	
	// custom exception which help us to investigate the result of the test
	class TestException extends Exception {
	
		public $status;
	
		function TestException($status) {
			$this->status = $status;
		}
	};

	// the actual test class
	class TestCase {
	
		protected $_result;
		private $_skipMethods = array(
			"run", 
			"TestCase", 
			"getTraceInformation", 
			"processResult", 
			"isTrue", 
			"isFalse", 
			"isNull", 
			"isNotNull", 
			"isA", 
			"isNotA",
			"isEqual",
			"isNotEqual",
			"isIdentical",
			"isNotIdentical",
			"isEmptyString",
			"isNotEmptyString",
			"isMoreThen",
			"isLessThen",
			"describe"
		);
	
		public function TestCase() {
			$this->_result = new TestResult();
		}
		public function run() {
		
			$this->_result->title("class ".get_class($this));
				
			$methods = get_class_methods(get_class($this));
			$numOfMethods = count($methods);
			for($i=0; $i<$numOfMethods; $i++) {
				$method = $methods[$i];
				if(!in_array($method, $this->_skipMethods)) {
					$this->_result->sectionStart("method : ".$method);
					$this->$method();
					$this->_result->sectionEnd();
				}
			}
			
		}
		public function describe($str) {
			$this->_result->note($str);
			return $this;
		}
		protected function isTrue($expression, $catch = true) {
			if($catch) {
				try {
					if($expression) {
						throw new TestException(true);
					} else {
						throw new TestException(false);
					}
				} catch(TestException $e) {
					$this->processResult($e);
				}
			} else {
				if(!$expression) {
					throw new TestException(false);
				}
			}
		}
		protected function isFalse($expression, $catch = true) {
			$this->isTrue(!$expression, $catch);
		}
		protected function isNull($expression, $catch = true) {
			$this->isTrue($expression === NULL, $catch);
		}
		protected function isNotNull($expression, $catch = true) {
			$this->isTrue($expression !== NULL, $catch);
		}
		protected function isA($ob, $className) {
			try {
				$this->isNotNull($ob, false);
				$this->isNotNull($className, false);
				$this->isTrue(get_class($ob) == $className);
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isNotA($ob, $className) {
			try {
				$this->isNotNull($ob, false);
				$this->isNotNull($className, false);
				$this->isTrue(get_class($ob) != $className);
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isEqual($a, $b) {
			try {
				$this->isNotNull($a, false);
				$this->isNotNull($b, false);
				$this->isTrue($a == $b);
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isNotEqual($a, $b) {
			try {
				$this->isNotNull($a, false);
				$this->isNotNull($b, false);
				$this->isTrue(!($a == $b));
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isIdentical($a, $b) {
			try {
				$this->isNotNull($a, false);
				$this->isNotNull($b, false);
				$this->isTrue($a === $b);
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isNotIdentical($a, $b) {
			try {
				$this->isNotNull($a, false);
				$this->isNotNull($b, false);
				$this->isTrue(!($a === $b));
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isEmptyString($str) {
			try {
				$this->isNotNull($str, false);
				$this->isIdentical($str, "");
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isNotEmptyString($str) {
			try {
				$this->isNotNull($str, false);
				$this->isNotIdentical($str, "");
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isMoreThen($a, $value) {
			try {
				$this->isNotNull($a, false);
				$this->isTrue($a > $value);
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		protected function isLessThen($a, $value) {
			try {
				$this->isNotNull($a, false);
				$this->isTrue($a < $value);
			} catch(TestException $e) {
				$this->processResult($e);
			}
		}
		private function getTraceInformation($trace) {
			$numOfTraces = count($trace);
			for($i=0; $i<$numOfTraces; $i++) {
				if($trace[$i]["class"] != 'TestCase' && $i > 0) {
					return $trace[$i-1];
				}
			}
			return $trace[0];
		}
		private function processResult($e) {
			$trace = $this->getTraceInformation($e->getTrace());
			if(isset($trace)) {
				$file = basename($trace["file"]);
				$resultMessage = '';
				$resultMessage .= '<strong>'.$file.'</strong>';
				$resultMessage .= ' (line: '.$trace["line"].', method: '.$trace["function"].')';
				if($e->status) {
					$this->_result->passed($resultMessage);
				} else {
					$this->_result->failed($resultMessage);
				}
			}
		}
	
	};
	
?>