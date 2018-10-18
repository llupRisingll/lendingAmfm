<?php

class Params{
    private static $params = array();
    private static $required = array();

    public static function getAll(){
        if (empty(self::$params)){
            self::$params = Route::getURIParams();
        }
        return self::$params;
    }

    private static function array_keys_exist( array $array, array $keys ) {
        $count = 0;

        foreach ( $keys as $key )
            if ( isset( $array[$key] ) || array_key_exists( $key, $array ) )
                $count ++;
     
        return count( $keys ) === $count;
    }

    // Pulls a single key from the hash and raises an error if its not there.
    public static function require(){
        if ( func_num_args() < 1 ){
            return false; 
        }

        // Get HTTP Verb data
        // Check if all Function arguments represents as key in the URI params.
        if (!self::array_keys_exist( self::getAll(), func_get_args())){
            Route::returnCode(401);
        }else{
            self::$required = func_get_args();
        }
    }

    
    // Returns the keys which are allowed and marks the hash as safe for mass assignment.
    public static function permit(){
        if ( func_num_args() < 1 ){
            return false; //not enough args
        }

        // Convert all arguments with required param + permitted parameters
        $keys = func_get_args();
        $array = self::getAll();

        // Permit Algorithm..
        $newArray = array();

        // Include Args
        foreach ( $keys as $key )
            if ( isset( $array[$key] ) || array_key_exists( $key, $array ) )
                $newArray[$key] = $array[$key];
        
        // Include requires
        foreach ( self::$required as $key )
            if ( isset( $array[$key] ) || array_key_exists( $key, $array ) )
                $newArray[$key] = $array[$key];
            
        self::$params = $newArray;
    }

    // Return a parameter value
    public static function get(String $key){
        if (isset(self::$params[$key])){
            return self::$params[$key];
        }

        return false;
    }
}