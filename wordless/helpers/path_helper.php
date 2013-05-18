<?php
/**
 * This module provides methods for accesing wordpress paths.
 *
 * @ingroup helperclass
 */

class PathHelper {

  /**
   * Returns the physical path for the specified file in the themes directory.
   * 
   * @param string $path
   *   The relative path inside the @e themes/ folder.
   * @return string
   *   The complete path for the specified file/folder.
   * 
   * @ingroup helperfunc
   */
  function template_path($path='') {
    return Wordless::join_paths(WP_CONTENT, 'themes', $path);
  }

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
  function plugin_path($path='') {
    return Wordless::join_paths(WP_CONTENT, 'plugins', $path);
  }

}

Wordless::register_helper("PathHelper");