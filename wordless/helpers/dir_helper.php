<?php
/**
 * This module provides methods for accesing wordpress paths.
 *
 * @ingroup helperclass
 */

class DirHelper {

  /**
   * Returns the physical path for the specified file in the plugins directory.
   * 
   * @param string $path
   *   The relative path inside the @e plugins/ folder.
   * @return string
   *   The complete path for the specified file/folder.
   * 
   * @ingroup helperfunc
   */
  function plugin_dir($path) {
    return Wordless::join_paths(Wordless::plugin_path() , $path);
  }

}

Wordless::register_helper("DirHelper");