# PHP-Shell Generator

Minimalistic [web shell](https://en.wikipedia.org/wiki/Web_shell) written in PHP and [jQuery.Terminal](https://github.com/jcubic/jquery.terminal). The purpose of this tool is to serve as an alternative to popular web shells like [r57](https://www.google.com/search?q=r57+shell) and [c99](https://www.google.com/search?q=c99+shell) but with a minimalistic user interface with focus on the essential tools like a command line interface and access to system programs as well as alternatives when such programs are unavailable.

<div style="text-align:center">
<img src="http://cixtor.com/uploads/phpshell-generator-4.png">
</div>

## Options

| Parameters | Name | Description |
|------------|------|-------------|
| `-h` | Help | Prints this message with a list of available options |
| `-i` | Input | Specifies the file to serve as a template to generate the web shell (default "template.txt") |
| `-o` | Output | Specifies the filename for the generated web shell (default `<random>.php`) |
| `-s` | Shell | Specifies the PHP function to execute Unix commands (default "passthru") |
| `-u` | Username | Specifies the username to restrict access to a limit set of users |
| `-p` | Password | Specifies the password to restrict access to a limit set of users |
| `-l` | Lint | Double-checks the consistency of the PHP code in generated file |

## Methods

| Method | Description |
|--------|-------------|
| `set_interpreter` | Sets the PHP function to execute commands, e.g. `set_interpreter shell_exec` |
| `get_interpreter` | Prints the current PHP function acting as the interpreter |
| `interpreter` | Alias for `get_interpreter` |
| `get_disabled_functions` | Prints a list of disabled PHP functions to identify which shell functions are available |
| `disabled_functions` | Alias for `get_disabled_functions` |
| `get_php_version` | Prints the PHP version |
| `php_version` | Alias for `get_php_version` |
| `cd` | Changes directories using `chdir`, e.g. `cd /var/log/` |
| `logout` | Terminates the user session and reloads the page |
| `exit` | Alias for `logout` |
| `status` | Prints the web shell configuration |
| `clear` | Resets the screen |

## Generator

The process consists basically in replace some lines of the template with the customizable data provided by the tool like the password or the shell interpreter, adding dependencies to implement the web terminal interface, reducing the tabular characters and new lines to finally get a shell of more or less `130KiB`.

**Note:** The shell generator was rewritten from Ruby to PHP for simplicity.

![compilation](http://cixtor.com/uploads/phpshell-generator-1.png)

## 404 Not Found

To attempt to hide the web shell in the web server a little bit, the generated PHP file returns a "404 Not Found" status code on GET and HEAD requests when the user session is not set yet. When you access the PHP file in a web browser, press the `<Tab>` key to jump to the first form field and type the username, press `<Tab>` one more time to type the password, and then hit the `<Return>` key to submit the form and log in. If the credentials are correct, the web shell will create a session that will remain until you type `logout` or `exit`.

![not_found](http://cixtor.com/uploads/phpshell-generator-2.png)
