<?php

class Helper_KWP {
    
    static function add_post_route($post_id, $route, $placement) {
    }
    
    /**
    * Adds or updates post meta.
    * 
    * @param mixed $post_id
    * @param mixed $key
    * @param mixed $value
    */
    static function add_update_post_meta($post_id, $key, $value) {
        add_post_meta($post_id, $key, $value, true) or update_post_meta($post_id, $key, $value);
    }

    
    /**
    * Create a new directory, and the whole path.
    *
    * If  the  parent  directory  does  not exists, we will create it,
    * etc.
    * @todo
    *     - PHP5 mkdir functoin supports recursive, it should be used
    * @author baldurien at club-internet dot fr 
    * @param string the directory to create
    * @param int the mode to apply on the directory
    * @return bool return true on success, false else
    * @previousNames mkdirs
    */
    static function mkdir_p($dir, $mode = 0777, $recursive = true) {
        if (is_null($dir) || $dir === "" ) {
            return FALSE;
        }
        if (is_dir($dir) || $dir === "/" ) {
            return TRUE;
        }
        if (self::mkdir_p(dirname($dir), $mode, $recursive) ) {
            return mkdir($dir, $mode);
        }
        return FALSE;
    }

    /**
     * Copies file or folder from source to destination, it can also do
     * recursive copy by recursively creating the dest file or directory path if it wasn't exist
     * Use cases:
     * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
     * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
     * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest      
     * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
     * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
     * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
     * @todo
     *  - Should have rollback so it can undo the copy when it wasn't completely successful
     *  - It should be possible to turn off auto path creation feature f
     *  - Supporting callback function
     *  - May prevent some issues on shared enviroments : <a href="http://us3.php.net/umask" title="http://us3.php.net/umask">http://us3.php.net/umask</a>
     * @param $source //file or folder
     * @param $dest ///file or folder
     * @param $options //folderPermission,filePermission
     * @return boolean
     */
    static function cp_r($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
    {
        $result=false;
        
        //For Cross Platform Compatibility
        if (!isset($options['noTheFirstRun'])) {
            $source=str_replace('\\','/',$source);
            $dest=str_replace('\\','/',$dest);
            $options['noTheFirstRun']=true;
        }
        
        if (is_file($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if (!file_exists($dest)) {
                    makeAll($dest,$options['folderPermission'],true);
                }
                $__dest=$dest."/".basename($source);
            } else {
                $__dest=$dest;
            }
            $result=copy($source, $__dest);
            chmod($__dest,$options['filePermission']);
            
        } elseif(is_dir($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if ($source[strlen($source)-1]=='/') {
                    //Copy only contents
                } else {
                    //Change parent itself and its contents
                    $dest=$dest.basename($source);
                    @mkdir($dest);
                    chmod($dest,$options['filePermission']);
                }
            } else {
                if ($source[strlen($source)-1]=='/') {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                }
            }

            $dirHandle=opendir($source);
            while($file=readdir($dirHandle))
            {
                if($file!="." && $file!="..")
                {
                    $__dest=$dest."/".$file;
                    $__source=$source."/".$file;
                    //echo "$__source ||| $__dest<br />";
                    if ($__source!=$dest) {
                        $result=self::cp_r($__source, $__dest, $options);
                    }
                }
            }
            closedir($dirHandle);
            
        } else {
            $result=false;
        }
        return $result;
    }    
    
    /**
    * Join paths.
    * 
    * @example
    * 
    *   join_paths('one', 'two', 'three') == 'one/two/three'
    */
    static function join_paths() {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg) {
            $paths = array_merge($paths, (array)$arg);
        }
        foreach ($paths as &$path) {
            $path = trim($path, '/');
        }
        return join('/', $paths);
    }
    
	
	/**
	 * Converts a literal array, into an object array.
	 *
	 * @example
	 * 		$arr = array('apple, 'orange');
	 * 		$items = objectify($arr);
	 * 		foreach ($item in $items) {
	 * 			echo $item.i . ' => ' . $item.value . '\n';
	 * 		}
	 *
	 * 	prints
	 * 		0 => apple
	 * 		1 => orange
	 *
	 * @static
	 * @param array $literal_array
	 * @param string $index_key The key name for index.
	 * @param string $value_key The key name for value.
	 * @param int $index_base Defaults to 0-based array. To make 1-based, pass in 1.
	 * @param bool $skip_empty Skip emty values. Default is true.
	 * @return void
	 */
	static function objectify($literal_array, $index_key = 'i', $value_key = 'value', $index_base = 0, $skip_empty = true) {
		if (!($literal_array)) {
			return $literal_array;
		}
		$result = array();
		$i = 0;
		foreach ($literal_array as $item) {
			if ($skip_empty && empty($item))
				continue;

			$o = new stdClass();
			$o->$index_key = $i + $index_base;
			$o->$value_key = $item;
			$result[] = $o;
			$i++;
		}

 		return $result;
	}

	static function slash($path) {
		$ch = substr($path, -1);
		if ($ch != '\\' &&  $ch != '/')
			return $path . DIRECTORY_SEPARATOR;
		else
			return $path;
	}
}
