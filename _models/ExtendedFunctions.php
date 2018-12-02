<?php

class ExtendedFunctions {

	/**
	 * Get the parent level on a level classified array
	 * @param $treeArray
	 * @param $parentId
	 * @return bool|int|string
	 */
	public static function get_parent_level($treeArray,$parentId){
		foreach ($treeArray as $key => $value){
			foreach ($value as $val){
				if ($parentId == $val){
					return $key;
				}
			}
		}
		return false;
	}
}