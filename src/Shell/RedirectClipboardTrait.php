<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.1.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell;

use Cake\Filesystem\File;

/**
 * Redirects the file output of a Shell to the clipboard. All files are copied to the clipboard
 * in the order they are written.
 *
 * @see \Cake\Console\Shell This traits works only on Shell classes.
 */
trait RedirectClipboardTrait
{

    /**
     * @var null|string Name of the temp file used for clipboard output.
     */
    private static $_clipboardTemp = null;

    /**
     * @var null|File File handle used for clipboard output.
     */
    private static $_clipboardFile = null;

    /**
     * Opens the temporary file that receives clipboard contents.
     *
     * @return bool
     */
    private static function openClipboardFile()
    {
        if (self::$_clipboardFile !== null) {
            return true;
        }
        self::$_clipboardTemp = tempnam(sys_get_temp_dir(), "tmp");
        self::$_clipboardFile = new File(self::$_clipboardTemp, true);
        return self::$_clipboardFile->exists() || self::$_clipboardFile->writable();
    }

    /**
     * Tests if a command line tool can be found on Linux.
     *
     * @param string $cmd The command to locate.
     * @return bool
     */
    private static function which($cmd)
    {
        $code = 0;
        $output = null;
        exec("which {$cmd}", $output, $code);
        return $code == 0;
    }

    /**
     * Sends the temporary file to the clipboard.
     *
     * @return bool
     */
    private static function sendClipboardFile()
    {
        if (self::$_clipboardFile == null) {
            return false;
        }
        $fileName = self::$_clipboardTemp;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            return trim(shell_exec("clip < {$fileName}")) == '';
        }
        if (self::which('pbcopy')) {
            // OSX
            return trim(shell_exec("pbcopy < {$fileName}")) == '';
        }
        if (self::which('xclip')) {
            // Linux
            return trim(shell_exec("xclip -selection clipboard < {$fileName}")) == '';
        }
        return false;
    }

    /**
     * Closes the temporary file and deletes it.
     */
    private static function closeClipboardFile()
    {
        if (self::$_clipboardFile == null) {
            return;
        }
        self::$_clipboardFile->close();
        self::$_clipboardFile = null;
        if (file_exists(self::$_clipboardTemp)) {
            unlink(self::$_clipboardTemp);
        }
    }

    /**
     * Runs the Shell with the provided argv.
     *
     * @param array $argv Array of arguments.
     * @param bool $autoMethod Set to true to allow any public methods
     * @param array $extra Extra parameters.
     * @return mixed
     * @see \Cake\Console\Shell::runCommand
     */
    public function runCommand($argv, $autoMethod = false, $extra = [])
    {
        if (!self::openClipboardFile()) {
            $this->abort('Could not open temp file required for clipboard.');
        }

        try {
            $res = parent::runCommand($argv, $autoMethod = false, $extra = []);
            if (self::sendClipboardFile()) {
                $this->_io->out('<success>Copy to clipboard</success>');
            } else {
                $this->_io->out('<error>Failed to send to clipboard</error>');
                $this->_io->out('');
                $this->_io->out('Linux users need to install xclip.');
                $this->_io->out('   sudo apt-get install xclip');
            }
        } finally {
            self::closeClipboardFile();
        }

        return $res;
    }

    /**
     * Optionally redirects file output to the clipboard.
     *
     * @param string $path Where to put the file.
     * @param string $contents Content to put in the file.
     * @return bool Success
     */
    public function createFile($path, $contents)
    {
        if (empty($this->params['clipboard'])) {
            return parent::createFile($path, $contents);
        }
        if (self::$_clipboardFile == null) {
            $this->abort('Handle to clipboard is missing.');
        }

        self::$_clipboardFile->write("// {$path}" . PHP_EOL);
        self::$_clipboardFile->write($contents);
        self::$_clipboardFile->write(PHP_EOL);
        return true;
    }
}
