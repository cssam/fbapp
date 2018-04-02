<?php

    require_once("Test.php");
    
    class MyCustomTestingCase extends TestCase {
        
        public function customMethod() {
            $this->describe("is 2 + 2 = 4")->isTrue(2 + 2 == 4);
            $this->describe("is 1 + 1 = 5")->isTrue(1 + 1 == 5);
        }
        
    }
    
    Test::run(new MyCustomTestingCase());

?>