<?php

class ExtendedFunctions {

	/**
	 * Get the parent level on a level classified array
	 * @param $parentId
	 * @return bool|int|string
	 */
	public static function get_parent_level($parentId){
		foreach (self::$treeArray as $key => $value){
			foreach ($value as $val){
				if ($parentId == $val){
					return $key;
				}
			}
		}
		return false;
	}
}