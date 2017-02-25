<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\Ess;

use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Logger.
 *
 * Logs messages to specified file or to PHP's system logger if no file
 * specified.
 *
 * Usage:
 * $log = new Si\Ess\Logger('/var/log/my.log', Psr\Log\LogLevel::INFO);
 * $log->info('Returned a million search results'); //Prints to the log file
 * $log->error('Oh dear.'); //Prints to the log file
 * $log->debug('x = 5'); //Prints nothing due to current severity threshhold
 *
 * Based on https://github.com/katzgrau/KLogger by Kenny Katzgrau <katzgrau@gmail.com>
 */
class Logger extends AbstractLogger
{
    /**
     *  Default options.
     */
    protected $options = array (
        'dateFormat'     => 'Y-m-d G:i:s.u',    // entries date format
        'fileDate'       => true,               // if logger logs to custom file add today date to filename
        'flushFrequency' => false,
        'entryFormat'    => false,              // entries format
        'appendContext'  => true,
    );

    private $logFilePath;   /// <string> path to log file
    protected $logLevelThreshold = \Psr\Log\LogLevel::DEBUG;     /// <int> minimum logging threshold
    private $logLineCount = 0;      /// <int> The number of lines logged in this instance's lifetime

    /**
     * Log Levels
     */
    protected $logLevels = [
        \Psr\Log\LogLevel::EMERGENCY => 0,
        \Psr\Log\LogLevel::ALERT     => 1,
        \Psr\Log\LogLevel::CRITICAL  => 2,
        \Psr\Log\LogLevel::ERROR     => 3,
        \Psr\Log\LogLevel::WARNING   => 4,
        \Psr\Log\LogLevel::NOTICE    => 5,
        \Psr\Log\LogLevel::INFO      => 6,
        \Psr\Log\LogLevel::DEBUG     => 7
    ];

    private $fileHandle;    /// <resource> handle to log file
    private $defaultPermissions = 0777; /// <int> default log file permissions

    public function __construct(
        array $logFileParts = [],                   /// parts of filename, last is always extension (if starts with '.')
        string $logLevelThreshold = \Psr\Log\LogLevel::DEBUG,
        array $options = []
    ) {
        $this->logLevelThreshold = $logLevelThreshold;
        $this->options = array_merge($this->options, $options);

        if (empty($logFileParts)) {                                 // log to PHP's system logger
            $this->logFilePath = "PHP's system logger";
            $this->fileHandle = null;
        } else if (strpos($logFileParts[0], 'php://') === 0) {      // log to php stream
            $this->logFilePath = $logFileParts[0];
            $this->fileHandle = fopen($this->logFilePath, 'w+');
        } else {                                                    // log to file
            $this->logFilePath = $this->composeLogFilePath($logFileParts);
            $this->fileHandle = fopen($this->logFilePath, 'a');
            if ( ! is_writable($this->logFilePath)) {
                throw new \Exception("Can't write to '{$this->logFilePath}'. Check permissions.");
            }
        }

        if (false === $this->fileHandle) {
            throw new \Exception("Can't open '{$this->logFilePath}' file.");
        }
    }

    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    private function composeLogFilePath(array $logFileParts): string
    {
        if ($this->options['fileDate']) {
            $extension = array_pop($logFileParts);
            if ($extension[0] === '.') {
                $filename = implode($logFileParts).".".date('Y-m-d').$extension;
            } else {
                $filename = implode($logFileParts).$extension.".".date('Y-m-d');
            }
            return $filename;
        } else {
            return implode($logFileParts);
        }
    }

    public function setLogLevelThreshold(string $logLevelThreshold): void
    {
        $this->logLevelThreshold = $logLevelThreshold;
    }

    public function setEntryFormat(string $entryFormat): void
    {
        $this->options['entryFormat'] = $entryFormat;
    }

    /**
     * Logs with an arbitrary level.
     */
    //public function log($level, $message, array $context = array());
    public function log($level, $message, array $context = [])
    {
        if ($this->logLevels[$this->logLevelThreshold] < $this->logLevels[$level]) {
            return;
        }
        $message = $this->formatMessage($level, $message, $context);
        $this->write($message);
    }

    /**
     * Writes a line to the log without prepending a status or timestamp
     */
    private function write(string $message): void
    {
        if (null !== $this->fileHandle) {
            if (fwrite($this->fileHandle, $message) === false) {
                throw new \Exception("Can't write to '{$this->logFilePath}'. Check permissions.");
            }
            $this->logLineCount++;
            if ($this->options['flushFrequency'] && $this->logLineCount % $this->options['flushFrequency'] === 0) {
                fflush($this->fileHandle);
            }
        } else {
            $this->logLineCount++;
            error_log($message);
        }
    }

    /**
     * Formats the message for logging.
     */
    private function formatMessage(string $level, string $message, array $context = []): string
    {
        if ($this->options['entryFormat']) {
            $parts = array(
                'date'          => $this->getTimestamp(),
                'level'         => strtoupper($level),
                'level-padding' => str_repeat(' ', 9 - strlen($level)),
                'priority'      => $this->logLevels[$level],
                'message'       => $message,
                'context'       => json_encode($context),
            );
            $message = $this->options['entryFormat'];
            foreach ($parts as $part => $value) {
                $message = str_replace('{'.$part.'}', $value, $message);
            }

        } else {
            $message = "[{$this->getTimestamp()}] [{$level}] {$message}";
        }

        if ($this->options['appendContext'] && ! empty($context)) {
            $message .= PHP_EOL.$this->indent($this->contextToString($context));
        }

        return $message;
        return $message.PHP_EOL;

    }

    /**
     * Gets the correctly formatted Date/Time for the log entry.
     *
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     */
    private function getTimestamp(): string
    {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.'.$micro, $originalTime));

        return $date->format($this->options['dateFormat']);
    }

    /**
     * Takes the given context and coverts it to a string.
     */
    private function contextToString(array $context): string
    {
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace(array(
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m'
            ), array(
                '=> $1',
                'array()',
                '    '
            ), str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * Indents the given string with the given indent.
     */
    private function indent(string $string, string $indent = '    '): string
    {
        return $indent.str_replace("\n", "\n".$indent, $string);
    }
}
