<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Connection;

use AssoConnect\SmtpToolbox\Exception\SmtpConnectionLogicException;
use AssoConnect\SmtpToolbox\Exception\SmtpConnectionRuntimeException;
use Psr\Log\LoggerInterface;

class SmtpConnection
{
    /**
     * SMTP line break constant.
     *
     * @var string
     */
    private const CRLF = "\r\n";

    /**
     * The SMTP port to use if one is not specified.
     *
     * @var int
     */
    public const DEFAULT_PORT = 25;
    /**
     * Debug level for no output.
     */
    public const DEBUG_OFF = 0;
    /**
     * Debug level to show client -> server messages.
     */
    public const DEBUG_CLIENT = 1;
    /**
     * Debug level to show client -> server and server -> client messages.
     */
    public const DEBUG_SERVER = 2;
    /**
     * Debug level to show connection status, client -> server and server -> client messages.
     */
    public const DEBUG_CONNECTION = 3;
    /**
     * Debug level to show all messages.
     */
    public const DEBUG_LOWLEVEL = 4;
    private static ?bool $hasStreamApi = null;

    public LoggerInterface $debugOutput;

    /**
     * @var array{command: string, response: string, smtp_code: int, success: int|bool}[]
     */
    public array $transferLogs = [];

    /**
     * The socket for the server connection.
     *
     * @var ?resource
     */
    protected $socket;
    /**
     * The most recent reply received from the server.
     *
     * @var string
     */
    protected string $lastReply = '';
    /**
     * Debug output level.
     * Options:
     * * self::DEBUG_OFF (`0`) No debug output, default
     * * self::DEBUG_CLIENT (`1`) Client commands
     * * self::DEBUG_SERVER (`2`) Client commands and server responses
     * * self::DEBUG_CONNECTION (`3`) As DEBUG_SERVER plus connection status
     * * self::DEBUG_LOWLEVEL (`4`) Low-level data output, all messages.
     */
    private int $debugLevel = self::DEBUG_OFF;
    /**
     * The timeout value for connection, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
     *
     * @see https://tools.ietf.org/html/rfc2821#section-4.5.3.2
     */
    private int $timeout = 300;
    /**
     * How long to wait for commands to complete, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     */
    private int $timeLimit = 10;

    /**
     * The set of SMTP extensions sent in reply to EHLO command.
     * Indexes of the array are extension names.
     * Value at index 'HELO' or 'EHLO' (according to command that was sent)
     * represents the server name. In case of HELO it is the only element of the array.
     * Other values can be boolean TRUE or an array containing extension options.
     * If null, no HELO/EHLO string has yet been received.
     * @var mixed[]
     */
    private ?array $serverCapabilities = null;

    public function __construct(LoggerInterface $logger)
    {
        $this->debugOutput = $logger;
    }

    /**
     * Connect to an SMTP server.
     *
     * @param string $host SMTP server IP or host name
     * @param int $port The port number to connect to
     * @param int $timeout How long to wait for the connection to open
     * @param mixed[] $options An array of options for stream_context_create()
     */
    public function connect(string $host, int $port = self::DEFAULT_PORT, int $timeout = 5, array $options = []): void
    {
        // make sure we are __not__ connected
        if ($this->connected()) {
            $this->quit();
        }

        $this->transferLogs = [];

        $this->log(
            sprintf(
                'Connection: opening to %s:%s, timeout=%s, options=%s',
                $host,
                $port,
                $timeout,
                count($options) > 0 ? var_export($options, true) : '[]'
            ),
            self::DEBUG_CONNECTION
        );

        $errno = 0;
        $errstr = '';

        try {
            set_error_handler([$this, 'errorHandler']);
            if (self::hasStreamApi()) {
                $socket_context = stream_context_create($options);
                $socket = stream_socket_client(
                    sprintf('%s:%d', $host, $port),
                    $errno,
                    $errstr,
                    $timeout,
                    STREAM_CLIENT_CONNECT,
                    $socket_context
                );
            } else {
                //Fall back to fsockopen which should work in more places, but is missing some features
                $this->log(
                    'Connection: stream_socket_client not available, falling back to fsockopen',
                    self::DEBUG_CONNECTION
                );
                $socket = fsockopen($host, $port, $errno, $errstr, $timeout);
            }
        } finally {
            restore_error_handler();
        }

        // Verify we connected properly
        if (false === $socket) {
            throw new SmtpConnectionRuntimeException(sprintf('Failed to connect to server: %s', $errstr), $errno);
        }
        $this->socket = $socket;

        $this->log('Connection: opened', self::DEBUG_CONNECTION);

        // SMTP server can take longer to respond, give longer timeout for first read
        // Windows does not have support for this timeout function
        if (stripos(PHP_OS, 'WIN') !== 0) {
            $max = (int)ini_get('max_execution_time');
            // Don't bother if unlimited
            if (0 !== $max && $timeout > $max) {
                @set_time_limit($timeout);
            }
            stream_set_timeout($this->getSocket(), $timeout, 0);
        }

        // get any announcement
        $announce = $this->fetchLinesFromServer();
        $this->log('SERVER -> CLIENT: ' . $announce, self::DEBUG_SERVER);

        $code = $code_ex = $detail = null;
        self::parseResponseCode($announce, $code, $code_ex, $detail);
        $this->transferLogs[] = [
            'command' => '<CONNECT>',
            'response' => $announce,
            'smtp_code' => (int)$code,
            'success' => (int)$code === 220,
        ];
    }

    /**
     * Check connection state.
     *
     * @return bool True if connected
     */
    public function connected(): bool
    {
        if (is_resource($this->socket)) {
            if (true === $this->getStreamStatus('eof')) {
                // The socket is valid but we are not connected
                $this->log('SMTP NOTICE: EOF caught while checking if connected', self::DEBUG_CLIENT);
                $this->close();

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    private function getStreamStatus(string $key)
    {
        $info = stream_get_meta_data($this->getSocket());

        return $info[$key];
    }

    /**
     * Output debugging info via a user-selected method.
     *
     * @param string $str Debug string to output
     * @param int $level The debug level of this message; see DEBUG_* constants
     *
     * @see SMTP::$Debugoutput
     * @see SMTP::$do_debug
     */
    protected function log(string $str, int $level = 0): void
    {
        if ($level > $this->debugLevel) {
            return;
        }

        $this->debugOutput->debug($str);
    }

    /**
     * Close the socket and clean up the state of the class.
     */
    public function close(): void
    {
        $this->serverCapabilities = null;
        if (is_resource($this->socket)) {
            // close the connection and cleanup
            @fclose($this->socket);
            $this->socket = null; //Makes for cleaner serialization
            $this->log('Connection: closed', self::DEBUG_CONNECTION);
        }
    }

    /**
     * Checks if PHP stream_* function exists
     */
    private static function hasStreamApi(): bool
    {
        if (null === self::$hasStreamApi) {
            // check this once and cache the result
            self::$hasStreamApi = function_exists('stream_socket_client');
        }

        return self::$hasStreamApi;
    }

    /**
     * Read the SMTP server's response.
     * Either before eof or socket timeout occurs on the operation.
     * With SMTP we can tell if we have more lines to read if the
     * 4th character is '-' symbol. If it is a space then we don't
     * need to read anything else.
     */
    protected function fetchLinesFromServer(): string
    {
        // If the connection is bad, give up straight away
        if (!is_resource($this->socket)) {
            return '';
        }

        $data = '';
        $timeout = 0;
        stream_set_timeout($this->socket, $this->timeout);
        if ($this->timeLimit > 0) {
            $timeout = time() + $this->timeLimit;
        }
        $selR = [$this->socket];
        $selW = null;
        while (is_resource($this->socket) && !feof($this->socket)) {
            //Must pass vars in here as params are by reference
            if (stream_select($selR, $selW, $selW, $this->timeLimit) <= 0) {
                $this->log(
                    sprintf('SMTP::fetchLinesFromServer(): timed-out (%d sec)', $this->timeout),
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
            //Deliberate noise suppression - errors are handled afterwards
            $str = @fgets($this->socket, 515);
            if (false === $str) {
                return '';
            }

            $this->log(sprintf('SMTP INBOUND: "%s"', trim($str)), self::DEBUG_LOWLEVEL);
            $data .= $str;

            // If response is only 3 chars (not valid, but RFC5321 S4.2 says it must be handled),
            // or 4th character is a space, we are done reading, break the loop,
            // string array access is a micro-optimisation over strlen

            if (strlen($str) <= 3 || substr($str, 3, 1) === ' ') {
                break;
            }

            // Timed-out? Log and break
            if ((bool)$this->getStreamStatus('timed_out')) {
                $this->log(
                    sprintf('SMTP::fetchLinesFromServer(): timed-out (%d sec)', $this->timeout),
                    self::DEBUG_LOWLEVEL
                );
                break;
            }

            // Now check if reads took too long
            if (0 !== $timeout && time() > $timeout) {
                $this->log(
                    sprintf('SMTP::fetchLinesFromServer(): timelimit reached (%d sec)', $this->timeLimit),
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
        }

        return $data;
    }

    /**
     * Parse SMTP server reply and extract response codes and other details.
     */
    private static function parseResponseCode(
        string $response,
        ?string &$code,
        ?string &$code_ex,
        ?string &$detail
    ): void {
        if (1 === preg_match('/^(\d{3})[ -](?:(\d\\.\d\\.\d{1,2}) )?/', $response, $matches)) {
            $code = $matches[1];
            $code_ex = (count($matches) > 2 ? $matches[2] : null);
            // cut off error code from each response line
            $detail = preg_replace(
                "/{$code}[ -]"
                . (null !== $code_ex ? str_replace('.', '\\.', $code_ex) . ' ' : '')
                . '/m',
                '',
                $response
            );
            return;
        }

        if ('' !== $response) {
            // fall back to simple parsing if regex fails
            $code = substr($response, 0, 3);
            $code_ex = null;
            $detail = substr($response, 4);
            return;
        }

        $code = '500';
        $code_ex = null;
        $detail = 'empty response';
    }

    /**
     * Get debug output level.
     */
    public function getDebugLevel(): int
    {
        return $this->debugLevel;
    }

    /**
     * Set debug output level.
     */
    public function setDebugLevel(int $level = 0): void
    {
        $this->debugLevel = $level;
    }

    /**
     * Get debug output method.
     */
    public function getDebugOutput(): LoggerInterface
    {
        return $this->debugOutput;
    }

    /**
     * Get SMTP timeout.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set SMTP timeout.
     *
     * @param int $timeout The timeout duration in seconds
     */
    public function setTimeout(int $timeout = 0): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Get the last reply from the server.
     */
    public function getLastReply(): string
    {
        return $this->lastReply;
    }

    /**
     * Send an SMTP VRFY command.
     *
     * @param string $name The name to verify
     */
    public function verify(string $name): void
    {
        $this->sendCommand('VRFY', "VRFY $name", [250, 251]);
    }

    /**
     * Send a command to an SMTP server and check its return code.
     *
     * @param string $command The command name - not sent to the server
     * @param string $commandRaw The actual command to send
     * @param int|int[] $expect One or more expected integer success codes
     */
    public function sendCommand(string $command, string $commandRaw, $expect): void
    {
        if (!$this->connected()) {
            throw new SmtpConnectionLogicException("Called $command without being connected");
        }

        //Reject line breaks in all commands
        if (strpos($commandRaw, "\n") !== false || strpos($commandRaw, "\r") !== false) {
            throw new SmtpConnectionLogicException("Command $command contained line breaks");
        }

        $this->sendRaw($commandRaw . self::CRLF, $command);
        $this->lastReply = $this->fetchLinesFromServer();

        // fetch SMTP code and possible error code explanation
        $code = $code_ex = $detail = null;
        self::parseResponseCode($this->lastReply, $code, $code_ex, $detail);
        $success = in_array((int)$code, (array)$expect, true);

        $this->transferLogs[] = [
            'command' => $commandRaw,
            'response' => $this->lastReply,
            'smtp_code' => (int)$code,
            'success' => $success,
        ];

        $this->log('SERVER -> CLIENT: ' . $this->lastReply, self::DEBUG_SERVER);

        if (!$success) {
            throw new SmtpConnectionRuntimeException(
                sprintf('%s command failed: %s', $command, $this->lastReply),
                (int)$code
            );
        }
    }

    /**
     * Send raw data to the server.
     *
     * @param string $data The data to send
     * @param string $command Optionally, the command this is part of, used only for controlling debug output
     *
     * @return int|bool The number of bytes sent to the server or false on error
     */
    public function sendRaw(string $data, string $command = '')
    {
        //If SMTP transcripts are left enabled, or debug output is posted online
        //it can leak credentials, so hide credentials in all but lowest level
        if (
            self::DEBUG_LOWLEVEL > $this->debugLevel &&
            in_array($command, ['User & Password', 'Username', 'Password'], true)
        ) {
            $this->log('CLIENT -> SERVER: <credentials hidden>', self::DEBUG_CLIENT);
        } else {
            $this->log('CLIENT -> SERVER: ' . $data, self::DEBUG_CLIENT);
        }

        return $this->send($data);
    }

    /**
     * Send data.
     *
     * @param string $message Data.
     * @return  int|bool
     */
    public function send(string $message)
    {
        set_error_handler([$this, 'errorHandler']);
        $result = fwrite($this->getSocket(), $message);
        restore_error_handler();

        return $result;
    }

    /**
     * Send an SMTP NOOP command.
     * Used to keep keep-alives alive, doesn't actually do anything.
     */
    public function noop(): void
    {
        $this->sendCommand('NOOP', 'NOOP', 250);
    }

    /**
     * Send an SMTP HELO or EHLO command.
     * Used to identify the sending server to the receiving server.
     * This makes sure that client and server are in a known state.
     * Implements RFC 821: HELO <SP> <domain> <CRLF>
     * and RFC 2821 EHLO.
     *
     * @param string $host The host name or IP to connect to
     */
    public function hello(string $host = ''): void
    {
        // try extended hello first (RFC 2821)
        try {
            $this->sendHello('EHLO', $host);
            return;
        } catch (SmtpConnectionRuntimeException $exception) {
            $this->sendHello('HELO', $host);
        }
    }

    /**
     * Send an SMTP HELO or EHLO command.
     * Low-level implementation used by hello().
     *
     * @param string $hello The HELO string
     * @param string $host The hostname to say we are
     *
     *
     * @see hello()
     */
    protected function sendHello(string $hello, string $host): void
    {
        $this->sendCommand($hello, $hello . ' ' . $host, 250);
        $this->parseHelloFields($hello, $this->lastReply);
    }

    /**
     * Parse a reply to HELO/EHLO command to discover server extensions.
     * In case of HELO, the only parameter that can be discovered is a server name.
     *
     * @param string $type `HELO` or `EHLO`
     */
    protected function parseHelloFields(string $type, string $heloReply): void
    {
        $this->serverCapabilities = [];
        $lines = explode("\n", $heloReply);

        foreach ($lines as $n => $s) {
            if ("" === $s) {
                continue;
            }
            //First 4 chars contain response code followed by - or space
            $s = trim(substr($s, 4));
            $fields = explode(' ', $s);
            if (count($fields) > 1) {
                if (0 === $n) {
                    $name = $type;
                    $fields = $fields[0];
                } else {
                    $name = array_shift($fields);
                    switch ($name) {
                        case 'SIZE':
                            $fields = (count($fields) > 0 ? $fields[0] : 0);
                            break;
                        case 'AUTH':
                            break;
                        default:
                            $fields = true;
                    }
                }
                $this->serverCapabilities[$name] = $fields;
            }
        }
    }

    /**
     * Send an SMTP MAIL command.
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more recipient
     * commands may be called followed by a data command.
     * Implements RFC 821: MAIL <SP> FROM:<reverse-path> <CRLF>.
     *
     * @param string $from Source address of this message
     */
    public function mail(string $from): void
    {
        $this->sendCommand('MAIL FROM', sprintf('MAIL FROM:<%s>', $from), 250);
    }

    /**
     * Send an SMTP QUIT command.
     * Closes the socket if there is no error or the $closeConnection argument is true.
     * Implements from RFC 821: QUIT <CRLF>.
     */
    public function quit(): void
    {
        $this->sendCommand('QUIT', 'QUIT', 221);
        $this->close();
    }

    /**
     * Send an SMTP RCPT command.
     * Sets the TO argument to $toaddr.
     * Returns true if the recipient was accepted false if it was rejected.
     * Implements from RFC 821: RCPT <SP> TO:<forward-path> <CRLF>.
     *
     * @param string $address The address the message is being sent to
     */
    public function recipient(string $address): void
    {
        $this->sendCommand('RCPT TO', 'RCPT TO:<' . $address . '>', [250, 251]);
    }

    /**
     * Send an SMTP RSET command.
     * Abort any transaction that is currently in progress.
     * Implements RFC 821: RSET <CRLF>.
     */
    public function reset(): void
    {
        $this->sendCommand('RSET', 'RSET', 250);
    }

    /**
     * Initiate a TLS (encrypted) session.
     */
    public function startTLS(): bool
    {
        $this->sendCommand('STARTTLS', 'STARTTLS', 220);

        //Allow the best TLS version(s) we can
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

        // PHP 5.6.7 dropped inclusion of TLS 1.1 and 1.2 in STREAM_CRYPTO_METHOD_TLS_CLIENT
        // so add them back in manually if we can
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }

        // Begin encrypted connection
        set_error_handler([$this, 'errorHandler']);
        $crypto_ok = stream_socket_enable_crypto($this->getSocket(), true, $crypto_method);
        restore_error_handler();

        return (bool)$crypto_ok;
    }

    /**
     * Get SMTP extensions available on the server.
     * @return mixed[]|null
     */
    public function getServerCapabilities(): ?array
    {
        return $this->serverCapabilities;
    }

    /**
     * Get metadata about the SMTP server from its HELO/EHLO response.
     * The method works in three ways, dependent on argument value and current state:
     *   1. HELO/EHLO has not been sent - returns null and populates $this->error.
     *   2. HELO has been sent -
     *     $name == 'HELO': returns server name
     *     $name == 'EHLO': returns boolean false
     *     $name == any other string: returns null and populates $this->error
     *   3. EHLO has been sent -
     *     $name == 'HELO'|'EHLO': returns the server name
     *     $name == any other string: if extension $name exists, returns True
     *       or its options (e.g. AUTH mechanisms supported). Otherwise returns False.
     *
     * @param string $name Name of SMTP extension or 'HELO'|'EHLO'
     *
     * @return mixed
     */
    public function getServerCapability(string $name)
    {
        if (null === $this->serverCapabilities || count($this->serverCapabilities) === 0) {
            throw new SmtpConnectionLogicException('No HELO/EHLO was sent');
        }

        if (!array_key_exists($name, $this->serverCapabilities)) {
            if ('HELO' === $name) {
                return $this->getServerCapability('EHLO');
            }

            if ('EHLO' === $name || array_key_exists('EHLO', $this->serverCapabilities)) {
                return false;
            }

            throw new SmtpConnectionRuntimeException(
                'HELO handshake was used; No information about server extensions available'
            );
        }

        return $this->serverCapabilities[$name];
    }

    /**
     * Reports an error number and string.
     *
     * @param int $errno The error number returned by PHP
     * @param string $errmsg The error message returned by PHP
     * @param string $errfile The file the error occurred in
     * @param int $errline The line number the error occurred on
     */
    protected function errorHandler(int $errno, string $errmsg, string $errfile = '', int $errline = 0): void
    {
        throw new SmtpConnectionRuntimeException($errmsg, $errno);
    }

    /**
     * @return resource
     */
    protected function getSocket()
    {
        if (!is_resource($this->socket)) {
            throw new SmtpConnectionRuntimeException('Please connect first');
        }

        return $this->socket;
    }

    public function __destruct()
    {
        if ($this->connected()) {
            $this->close();
        }
    }
}
