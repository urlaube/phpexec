<?php

  /**
    This is the PhpExec plugin.

    This file contains the PhpExec plugin. It provides a PHP execution feature
    and two shortcuts to trigger the execution. One shortcut directly writes the
    PHP output into the content while the other shortcut escapes the output
    before it writes the PHP output into the content.

    @package urlaube\phpexec
    @version 0.1a1
    @author  Yahe <hello@yahe.sh>
    @since   0.1a0
  */

  // ===== DO NOT EDIT HERE =====

  // prevent script from getting called directly
  if (!defined("URLAUBE")) { die(""); }

  class PhpExec extends BaseSingleton implements Plugin {

    // CONSTANTS

    const CONSTANT  = "constant";
    const EXTENSION = ".php";
    const FILENAME  = "filename";

    const PHP    = "~\[php (?P<filename>[^\]]+)\]~";
    const PHPRAW = "~\[php\:raw (?P<filename>[^\]]+)\]~";

    const PLACEHOLDER = "~\{\%(?P<constant>[^\}]+)\}~";

    // HELPER FUNCTIONS

    protected static function getConstant($matches) {
      $result = null;

      // check if the constant name is set
      if (isset($matches[static::CONSTANT])) {
        $constant = $matches[static::CONSTANT];

        if (defined($constant)) {
          $result = constant($constant);
        }
      }

      return $result;
    }

    protected static function getContent($matches, $raw = false) {
      $result = null;

      // check if the filename is set
      if (isset($matches[static::FILENAME])) {
        $filename = $matches[static::FILENAME];

        // replace placeholder with PHP constant
        $filename = preg_replace_callback(static::PLACEHOLDER,
                                          function ($matches) { return static::getConstant($matches, false); },
                                          $filename);

        // fix $filename
        $filename = realpath($filename);

        // check if the file name ends with .php and is referencing an existing file
        if (istrail($filename, static::EXTENSION) && is_file($filename)) {
          // start output buffering
          $level = _startBuffer();
          if (null !== $level) {
            try {
              // include the file to execute the PHP source
              include($filename);
            } finally {
              // finish output buffering and retrieve the content
              $result = _stopBuffer($level);

              // escape the result if it's not supposed to be used raw
              if (!$raw) {
                $result = html($result);
              }
            }
          }
        }
      }

      return $result;
    }

    protected static function handleShortcodes($content) {
      $result = $content;

      if (is_string($result)) {
        // replace shortcode with PHP output
        $result = preg_replace_callback(static::PHP,
                                        function ($matches) { return static::getContent($matches, false); },
                                        $result);

        // replace shortcode with raw PHP output
        $result = preg_replace_callback(static::PHPRAW,
                                        function ($matches) { return static::getContent($matches, true); },
                                        $result);
      }

      return $result;
    }

    // RUNTIME FUNCTIONS

    public static function run($content) {
      $result = $content;

      if ($result instanceof Content) {
        if ($result->isset(CONTENT)) {
          $result->set(CONTENT, static::handleShortcodes(value($result, CONTENT)));
        }
      } else {
        if (is_array($result)) {
          // iterate through all content items
          foreach ($result as $result_item) {
            if ($result_item instanceof Content) {
              if ($result_item->isset(CONTENT)) {
                $result_item->set(CONTENT, static::handleShortcodes(value($result_item, CONTENT)));
              }
            }
          }
        }
      }

      return $result;
    }

  }

  // register plugin
  Plugins::register(PhpExec::class, "run", FILTER_CONTENT);
