<?php
/**
 * Plugin Name: Break Site for Testing
 * Description: Intentionally broken plugin for testing error handling
 * Version: 1.0.0
 */

// Syntax error - missing semicolon
//$broken_variable = "This will cause an error"

// Fatal error - calling undefined function
//undefined_function_call();

// Parse error - unclosed string
//$another_error = "This string is never closed

/* Class with syntax errors
class BrokenClass {
    public function brokenMethod() {
        return $undefined_var->nonExistentMethod();
    }
}*/

// Trigger errors
/*trigger_error("Intentional warning for testing", E_USER_WARNING);
throw new Exception("Intentional exception for testing");*/